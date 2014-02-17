<?php require_once './includes/fonctions.php'; ?>
<?php require_once './includes/config.php'; ?>

<!-- TODO rechercher voir fonctions.php -->

<!DOCTYPE html>


<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <p>La capitale de <?php echo $paysSaisi; ?> est <?php echo $capitale; ?></p>
        <h2>Formulaire recherche</h2>
        <form method="get">
            <p>
                <label>
                    Pays : <input type="text" name="pays" value="<?php echo $_GET["pays"]; ?>">
                </label>
                <input type="submit" value="Rechercher">
            </p>
        </form>
    </body>
</html>