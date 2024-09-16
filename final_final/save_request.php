<?php
require 'connection.php';
include 'session.php';

if (!$_SESSION['login_user']) {
    header("location:login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['login_user'];
    $item_id = $_POST['item'];
    $num_of_plp = $_POST['num_of_plp'];

    // Εύρεση του ονόματος του προϊόντος
    $item_query = "SELECT name FROM items WHERE id = '$item_id'";
    $item_result = mysqli_query($conn, $item_query);

    if ($item_result && mysqli_num_rows($item_result) > 0) {
        $item_row = mysqli_fetch_assoc($item_result);
        $item_name = $item_row['name'];

        $current_date_time = date('Y-m-d H:i:s');

        // Καταχώρηση στον πίνακα requests
        $query = "INSERT INTO requests (username, item, num_of_plp, added) 
                  VALUES ('$username', '$item_name', '$num_of_plp', '$current_date_time')";

        if (mysqli_query($conn, $query)) {
            // Επιστροφή JSON δεδομένων για το νέο αίτημα
            $response = array(
                "item" => $item_name,
                "num_of_plp" => $num_of_plp,
                "added" => $current_date_time
            );
            echo json_encode($response);
        } else {
            echo json_encode(array("error" => "Σφάλμα κατά την καταχώρηση."));
        }
    } else {
        echo json_encode(array("error" => "Το προϊόν δεν βρέθηκε."));
    }
}
?>
