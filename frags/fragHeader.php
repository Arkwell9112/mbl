<?php
if (!isset($toadd)) {
    $toadd = "";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,height=device-height,initial-scale=1">
    <link href="../imgs/bread.png" rel="icon">
    <link href="../scripts/select2.min.css" rel="stylesheet">
    <link href="../styles/header.css" rel="stylesheet">
    <link href="../styles/main.css" rel="stylesheet">
    <link href="../styles/footer.css" rel="stylesheet">
    <script src="../scripts/jquery-3.5.1.min.js"></script>
    <script src="../scripts/select2.min.js"></script>
    <script src="../scripts/footresp.js"></script>
    <?php echo $toadd ?>
    <title><?php echo $title ?></title>
</head>
<body>
<header>
    <div>
        <h1>Mon boulanger livreur</h1>
        <div>
            <h5>Il me livre du pain chaud tous les jours !</h5>
        </div>
    </div>
    <img src="../imgs/leftback.svg" id="leftback">
    <img src="../imgs/rightback.svg" id="rightback">
    <?php if ($isnav) include("fragNav.php"); ?>
</header>