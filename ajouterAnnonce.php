<?php require_once './includes/fonctions.php'; ?>
<?php require_once './includes/config.php'; ?>
<?php
// On teste si on est connecté
// on redirige vers login.php sinon
if (!isConnected()) {
    saveSecuredPage();
    redirigerEtQuitter("login.php");
}

$titrePage = "Ajouter une annonce";

if (postContient("titre", "descriptif") && isset($_FILES["cv"])) {
    $data = array_map("trim", $_POST);
//    $data["date_pub"] = date(DATE_ISO8601);

    // Protection contre les attaques XSS
    $data["titre"] = strip_tags($data["titre"]);
    $data["membre_id"] = $_SESSION["idUserConnected"];
    $data["descriptif"] = htmlspecialchars($data["descriptif"]);

    // Validation des champs
    $erreurs = [];

    if (strlen($data["titre"]) < 5 || strlen($data["titre"]) > 100) {
        $erreurs["titre"] = "Le titre doit être compris entre 5 et 100 caractères";
    }

    if (strlen($data["descriptif"]) < 25) {
        $erreurs["descriptif"] = "L'annonce doit contenir au moins 25 caractères";
    }

    // Validation et upload de la photo
    // error 4 veut dire pas de fichier uploadé
    if ($_FILES["cv"]["error"] !== 4) {
        if ($_FILES["cv"]["error"] !== 0 || $_FILES["cv"]["size"] > 2 * 1024 * 1024 || !in_array($_FILES["cv"]["type"], ["cv/doc", "cv/docx", "cv/odt", "cv/pdf"])) {
            $erreurs["cv"] = "Il faut uploader un cv au format DOC, DOCX, ODT ou PDF";
        } else {
            $source = $_FILES["cv"]["tmp_name"];
            $destination = strip_tags(uniqid() . "_" . $_FILES["cv"]["name"]); // images/jhdsjhg_maphoto.png

            $estUploadee = move_uploaded_file($source, "cv/" . $destination);

            if (!$estUploadee) {
                $erreurs["cv"] = "Une erreur s'est produite pendant l'upload du fichier";
            }
        }
    }

    if (empty($erreurs)) {

        $dsn = "mysql:host=".MYSQL_SERVER.";dbname=".MYSQL_BASE.";charset=UTF8";
        $pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        if (isset($destination)) {
            $sql = "INSERT INTO annonce (date_debut, date_fin, date_pub, descriptif, duree, id, lieu, titre, type) 
                    VALUES (date_debut, :date_fin, :date_pub, :descriptif, :duree, :id, :lieu, :titre, :type)";
        }
        else {
            $sql = "INSERT INTO article (titre, descriptif, id) 
                    VALUES (:titre, :descriptif, :id)";
        }
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindValue("titre", $data["titre"]);
        $stmt->bindValue("descriptif", $data["descriptif"]);
        $stmt->bindValue("id", $data["id"]);
        if (isset($destination)) { 
            $stmt->bindValue("cv", $data["cv"]);
        }
        
        $succes = $stmt->execute();
        
        if ($succes) {
            flashMessageEcrire("L'annonce a bien été publié");
            redirigerEtQuitter("index.php");
        } else {
            $erreurs["mysql"] = "Une erreur s'est produite à l'insertion";
        }
    }
}
?>
<?php include_once './includes/header.php'; ?>
<h1>Ajouter un article</h1>
<?php if (isset($messageSucces)) : ?>
    <div class="alert alert-success"><?php echo $messageSucces; ?></div>
<?php endif; ?>
<?php if (!empty($erreurs)) : ?>
    <div class="alert alert-danger">
        Voici la liste des erreurs :
        <ul>
    <?php foreach ($erreurs as $err) : ?>
                <li><?php echo $err; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
        <?php endif; ?>
<form method="post" enctype="multipart/form-data" role="form">
    <div class="form-group<?php if (isset($erreurs["titre"])) echo " has-error" ?>">
        <label for="titre">Titre</label>
        <input type="text" class="form-control" id="titre" placeholder="Saisir un titre" name="titre" <?php if (isset($data["titre"])) echo "value=\"$data[titre]\"" ?>>
<?php if (isset($erreurs["titre"])) : ?>
            <span class="help-block"><?php echo $erreurs["titre"]; ?></span>
        <?php endif; ?>
    </div>
    <div class="form-group<?php if (isset($erreurs["cv"])) echo " has-error" ?>">
        <label for="cv">Cv</label>
        <input type="file" id="cv" name="cv">
<?php if (isset($erreurs["cv"])) : ?>
            <span class="help-block"><?php echo $erreurs["cv"]; ?></span>
        <?php endif; ?>
    </div>
    <div class="form-group<?php if (isset($erreurs["descriptif"])) echo " has-error" ?>">
        <label for="descriptif">Contenu de l'article</label>
        <textarea class="form-control" rows="10" id="descriptif" placeholder="Saisir votre annonce" name="descriptif"><?php if (isset($data["descriptif"])) echo $data["descriptif"] ?></textarea>
<?php if (isset($erreurs["descriptif"])) : ?>
            <span class="help-block"><?php echo $erreurs["descriptif"]; ?></span>
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Ajouter</button>
</form>
<?php include_once './includes/footer.php'; ?>