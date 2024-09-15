<?php
require 'connection.php';
include 'session.php';


if(!$_SESSION['login_user']){
  header("location:login.html");
}

?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Αρχική Σελίδα</title>
    <link rel="stylesheet" type="text/css" href="menu.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://kit.fontawesome.com/e267099c50.js" crossorigin="anonymous"></script>

</head>

<body>
    <div class="bg"></div>
    <nav>
    <a href="#" class="active" onclick="location.reload();"><ion-icon name="home"></ion-icon></a>
        <br>
        <a href="mapadmin.php"><i class="fa-solid fa-map"></i></a>
        <br>
        <a href="charts.php"><i class="fa-solid fa-line-chart"></i></a>
        <br>
        <a href="add_announcement.php" style="font-size: 1.4rem"><i class="fa-solid fa-bullhorn"></i></a>
        <br>
        <a href="adminstorage.php"><i class="fa-solid fa-warehouse"></i></a>
        <br>
        <a href="adminadds.php"><i class="fa-solid fa-user-plus"></i></a>
        <div class="navspacer"></div>
        <a href="logout.php"><i class="fa-solid fa-sign-out"></i></a>
    </nav>

    <div class=body>
    <!-- Your existing content -->
    <h1 style="text-align: center;">Καλώς Ήρθες Admin!</h1>
    <h3 style="text-align: center;">Τι θα ήθελες να κάνεις σήμερα; </h3> 
    <p></p>
    </div>
</body>

</html>
