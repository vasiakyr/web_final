<?php
    include 'connection.php';
    session_start();

    if (isset($_POST['submit'])) {
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $pword = $_POST['pass'];
        $phone = $_POST['phone'];
        $username = $_POST['username'];
        $usertype = $_POST['usertype'];
        $location = $_POST['loc'];

        // Χρήση hashed password για ασφάλεια
        //$hashed_password = password_hash($pword, PASSWORD_DEFAULT);

        // Προετοιμασία του query για εισαγωγή δεδομένων
        $query = "INSERT INTO `user` (`Name`, `Surname`, `Username`, `Phone`, `Password`, `Type`, `Location`) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        // Δέσιμο παραμέτρων με το query
        $stmt->bind_param("sssssss", $fname, $lname, $username, $phone, $pword, $usertype, $location);

        // Εκτέλεση του query και έλεγχος αν έγινε επιτυχής εισαγωγή
        if ($stmt->execute()) {
            if (!isset($_SESSION['login_user'])) {
                // Αν δεν είναι συνδεδεμένος ο χρήστης, ανακατεύθυνση στο login page
                header("Location: login.html");
                exit();
            }
        } else {
            echo "Σφάλμα κατά την εισαγωγή δεδομένων: " . $stmt->error;
        }

        // Κλείσιμο του statement και της σύνδεσης
        $stmt->close();
        $conn->close();
    }
?>
