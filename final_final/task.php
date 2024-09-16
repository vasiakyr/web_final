<?php
require 'connection.php';
include 'session.php';

if (!isset($_SESSION['login_user'])) {
    header("location:login.html");
    exit;
}

$username = $_SESSION['login_user'];

// Διαχείριση της ενέργειας αποδοχή αιτήματος
if (isset($_GET['action']) && $_GET['action'] == 'accept' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Πρώτα, ενημέρωσε την κατάσταση του αιτήματος σε 'accepted'
    $updateQuery = "UPDATE requests SET status = 'accepted' WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    if (!$stmt) {
        echo "Σφάλμα προετοιμασίας: " . $conn->error;
        exit;
    }
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        echo "Σφάλμα εκτέλεσης: " . $stmt->error;
        exit;
    }
    $stmt->close();

    // Ανάκτησε τις πληροφορίες του αιτήματος για να τις αποθηκεύσεις στον πίνακα 'car'
    $selectQuery = "SELECT item, num_of_plp FROM requests WHERE id = ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($item, $num_of_plp);
    $stmt->fetch();
    $stmt->close();

    // Εισαγωγή στον πίνακα 'car'
    $insertQuery = "INSERT INTO car (item, username, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param('ssi', $item, $username, $num_of_plp);
    $stmt->execute();
    $stmt->close();

    // Επιστροφή για AJAX
    echo "success";
    exit;
}

// Ανάκτηση των αιτημάτων του χρήστη από τη βάση δεδομένων
$query = "SELECT id, num_of_plp, item, added, status FROM requests WHERE status = 'waiting'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Διαχείριση Αιτημάτων</title>
    
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://kit.fontawesome.com/e267099c50.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="menu.css">
    <link rel="stylesheet" type="text/css" href="form.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .table-container {
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #B8860B;
        }
        .action-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="bg"></div>
<nav>
    <a href="resquer.php" class="active"><ion-icon name="home"></ion-icon></a>
    <a href="car.php"><i class="fa-solid fa-car"></i></a>
    <a href="map.html"><i class="fa-solid fa-map"></i></a>
    <a href="task.php" style="font-size: 1.4rem"><i class="fa-solid fa-t"></i></a>
    <div class="navspacer"></div>
    <a href="login.html"><i class="fa-solid fa-sign-out"></i></a>
</nav>

<div class="table-container">
    <h3>Διαθέσιμα αιτήματα:</h3>
    <table>
        <thead>
            <tr>
                <th>Κωδικός Αιτήματος</th>
                <th>Προϊόν</th>
                <th>Άτομα</th>
                <th>Ημερομηνία Δημιουργίας</th>
                <th>Κατάσταση Αιτήματος</th>
                <th>Ενέργεια</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['item']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['num_of_plp']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['added']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td><button onclick='acceptRequest(" . urlencode($row['id']) . ")' class='action-button'>Ανάλαβε</button></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Δεν υπάρχουν αιτήματα.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    function acceptRequest(id) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", `task.php?action=accept&id=${id}`, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert('Αίτημα αναλήφθηκε επιτυχώς');
                location.reload();
            }
        };
        xhr.send();
    }
</script>

</body>
</html>
