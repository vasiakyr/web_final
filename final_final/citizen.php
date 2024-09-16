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
    <style>
        #quantity-container {
            margin-top: 20px;
        }

        #quantity-container label {
            display: block;
            margin-bottom: 5px;
        }

        #quantity-container input {
            margin-bottom: 10px;
            width: 100px;
            padding: 5px;
        }

        #current-quantity {
            margin-bottom: 10px;
            font-weight: bold;
        }

        #check-button {
            background-color: #32a64d;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
        }
        
        /* Style adjustments for the new icon */
        nav a {
            display: block;
            text-align: center;
            margin: 10px;
        }
        
        .navspacer {
            flex: 1;
        }
    </style>
</head>

<body>
    <div class="bg"></div>
    <nav>
    <a href="citizen.php" class="active""><ion-icon name="home"></ion-icon></a>
        <br>
        <a href="user_announcements.php"><i class="fa-solid fa-bullhorn"></i></a>
        <br>
        <a href="new_request.php" style="font-size: 1.4rem"><i class="fa-solid fa-hands-helping"></i></i></a>
        <br>
        <div class="navspacer"></div>
        <a href="logout.php"><i class="fa-solid fa-sign-out"></i></a>
    </nav>

    <h1>Καλώς Ήρθες</h1>
    <h3>Τι θα ήθελες να κάνεις;</h3>

</body>

</html>
