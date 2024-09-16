<?php
require 'connection.php';
include 'session.php';

// Ελέγχουμε αν ο χρήστης είναι συνδεδεμένος, αν όχι, ανακατευθύνει στη σελίδα σύνδεσης
if (!isset($_SESSION['login_user'])) {
    header("location:login.html");
    exit;
}

$username = $_SESSION['login_user'];

// Ανάκτηση δεδομένων από τον πίνακα 'car' για αυτόν τον χρήστη, ουσιαστικά εμφανίζουμε τι αγαθά έχει διαθέσιμα στο φορτηγό του
$query2 = "SELECT item, quantity FROM car WHERE username = ?";
$stmt = $conn->prepare($query2);
$stmt->bind_param('s', $username);
$stmt->execute();
$result2 = $stmt->get_result();

// Στα δύο dropdowns που έχουμε κάτω απο τον πίνακα με το φορτίο χρειάζεται να ανάκτησουμε τα προϊόντα απο τον πίνακα items για την ανανέωση της λίστας
//για όταν γίνεται φόρτωση
if (isset($_GET['fetch_items'])) {
    // Ανάκτηση δεδομένων από τον πίνακα 'items'
    $query = "SELECT DISTINCT name FROM items";
    $result = mysqli_query($conn, $query);
    
    $items = array();
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }
    echo json_encode($items);
    exit;
}
// Ανάκτηση προϊόντων από τον πίνακα 'car' 
//για όταν γίνεται ξεφόρτωση 
if (isset($_GET['fetch_car_items'])) {
    $query = "SELECT DISTINCT item FROM car WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $carItems = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $carItems[] = $row;
        }
    }
    echo json_encode($carItems);
    exit;
}

//~~~~~ ΦΟΡΤΩΣΗ ~~~~~~~
// Όταν επιλέγει φόρτωση προϊόντος
if (isset($_POST['action']) && $_POST['action'] == 'load') {
    $itemName = $_POST['item'];
    $quantity = intval($_POST['quantity']);

    // Έλεγχος αν το προϊόν υπάρχει ήδη για αυτόν τον χρήστη
    //ώστε να έχουμε δύο περιπτώσεις: είτε ενημέωση υπάρχοντος αγαθού είτε προσθήκη εντελώς καινούριου
    $checkQuery = "SELECT quantity FROM car WHERE username = ? AND item = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('ss', $username, $itemName);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Το προϊόν υπάρχει, ενημέρωση της ποσότητας
        $updateQuery = "UPDATE car SET quantity = quantity + ? WHERE username = ? AND item = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('iss', $quantity, $username, $itemName);
        $updateStmt->execute();
    } else {
        // Το προϊόν δεν υπάρχει, προσθήκη νέας εγγραφής
        $insertQuery = "INSERT INTO car (username, item, quantity) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param('ssi', $username, $itemName, $quantity);
        $insertStmt->execute();
    }

    echo json_encode(['status' => 'success']);
    exit;
}

//~~~~~ ΕΚΦΟΡΤΩΣΗ ~~~~~~~
if (isset($_POST['action']) && $_POST['action'] == 'unload') {
    $itemName = $_POST['item'];
    $quantity = intval($_POST['quantity']);

    // Έλεγχος αν το προϊόν υπάρχει και έχει αρκετή ποσότητα
    $checkQuery = "SELECT quantity FROM car WHERE username = ? AND item = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('ss', $username, $itemName);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $currentQuantity = 0;

    if ($checkResult->num_rows > 0) {
        $row = $checkResult->fetch_assoc();
        $currentQuantity = $row['quantity'];
        
        if ($currentQuantity >= $quantity) {
            // Το προϊόν υπάρχει και έχει αρκετή ποσότητα, ενημέρωση της ποσότητας
            $updateQuery = "UPDATE car SET quantity = quantity - ? WHERE username = ? AND item = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('iss', $quantity, $username, $itemName);
            $updateStmt->execute();

            // Αφαίρεση του προϊόντος αν η ποσότητα είναι μηδέν
            if ($currentQuantity - $quantity == 0) {
                $deleteQuery = "DELETE FROM car WHERE username = ? AND item = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                $deleteStmt->bind_param('ss', $username, $itemName);
                $deleteStmt->execute();
            }

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Δεν υπάρχει αρκετή ποσότητα']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Το προϊόν δεν βρέθηκε']);
    }

    exit;
}

//Προσθήκη/ενημέρωση πίνακα φορτίου του διασώστη χωρίς να γίνει refresh
// Αν ζητηθεί η ανάκτηση δεδομένων για τον πίνακα 'car' για ανανέωση μέσω AJAX
if (isset($_GET['fetch_car_table'])) {
    $query2 = "SELECT item, quantity FROM car WHERE username = ?";
    $stmt = $conn->prepare($query2);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result2 = $stmt->get_result();

    $carItems = array();
    if ($result2) {
        while ($row2 = $result2->fetch_assoc()) {
            $carItems[] = $row2;
        }
    }
    echo json_encode($carItems);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Φορτίο Αυτοκινήτου</title>
    <link rel="stylesheet" type="text/css" href="menu.css">
    <link rel="stylesheet" type="text/css" href="form.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://kit.fontawesome.com/e267099c50.js" crossorigin="anonymous"></script>
    <style>

        /* πεδίο φορτίου */
        .inventory-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .form-section {
            width: 100%;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        /* td is an HTML tag used to define a single cell within a table */
        td {
            border: 1px solid #fff;
            padding: 10px;
            text-align: center;
            color: #fff; 
        }


        input[type="number"], select {
        width: 80%;  /* Μειώνουμε το πλάτος στο 80% */
        padding: 8px;  /* Μειώνουμε το padding */
        margin-bottom: 20px;
        border-radius: 5px;
        border: 1px solid #d3d3d3;  /* Απαλό γκρι χρώμα για το περίγραμμα */
        background-color: #f0f0f0;  /* Απαλό γκρι για το φόντο */
        color: #333;  /* Σκούρο γκρι για το κείμενο */
        font-size: 0.9rem;  /* Μικρότερη γραμματοσειρά */
    }


        button, input[type="submit"] {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1rem;
        }

        h2, h3 {
            color: #fff;
            text-align: center;
        }

        h3 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="bg"></div>
<nav>
    <link rel="stylesheet" type="text/css" href="menu.css">
    <a href="resquer.php" class="active"><ion-icon name="home"></ion-icon></a>
    <a href="car.php"><i class="fa-solid fa-car"></i></ion-icon></a>
    <a href="map_rescuer.php"><i class="fa-solid fa-map"></i></a>
    <a href="task.php" style="font-size: 1.4rem"><i class="fa-solid fa-t"></i></a>
    <div class="navspacer"></div>
    <a href="login.html"><i class="fa-solid fa-sign-out"></i></ion-icon></a>
</nav>

<div class="container">

    <!-- Πίνακας που δείχνει το φορτίο του διασώστη -->
    <div class="inventory-section">
        <h1>Φορτίο Αυτοκινήτου</h1>
        <table>
            <thead>
                <tr>
                    <th>Προϊόν</th>
                    <th>Ποσότητα</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result2->num_rows > 0) {
                    while ($row2 = $result2->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row2['item']) . "</td>";
                        echo "<td>" . htmlspecialchars($row2['quantity']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>Κανένα προϊόν στο φορτηγό.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Ενότητες Φόρμας -->
    <div class="form-section">
        <!-- Φόρτωση Προϊόντων -->
        <div>
            <form id="loadForm">
                <h2>Φόρτωσε προϊόν στο φορτηγό</h2>
                <label for="loadItemSelect">Επιλέξτε προϊόν:</label>
                <select id="loadItemSelect" name="item"></select>
                <label for="loadQuantity">Ποσότητα:</label>
                <input type="number" id="loadQuantity" name="quantity" min="1" required>
                <button type="submit">Φόρτωσε</button>
            </form>
        </div>

        <!-- Εκφόρτωση Προϊόντων -->
        <div>
            <form id="unloadForm">
                <h2>Ξεφόρτωσε προϊόν από το φορτηγό</h2>
                <label for="unloadItemSelect">Επιλέξτε προϊόν:</label>
                <select id="unloadItemSelect" name="item"></select>
                <label for="unloadQuantity">Ποσότητα:</label>
                <input type="number" id="unloadQuantity" name="quantity" min="1" required>
                <button type="submit">Ξεφόρτωσε</button>
            </form>
        </div>
    </div>
</div>

<script>
// Συνάρτηση για την ανάκτηση και την πληκτρολόγηση επιλογών προϊόντων
function fetchItems() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var items = JSON.parse(this.responseText);
            populateItemSelects(items);
        }
    };
    xhttp.open("GET", "?fetch_items=1", true);
    xhttp.send();
}

// Συνάρτηση για την πλήρωση των στοιχείων επιλογών με προϊόντα
function populateItemSelects(items) {
    var loadItemSelect = document.getElementById("loadItemSelect");
    var unloadItemSelect = document.getElementById("unloadItemSelect");

    // Καθαρισμός υπάρχουσων επιλογών
    loadItemSelect.innerHTML = "";
    unloadItemSelect.innerHTML = "";

    items.forEach(function(item) {
        var option = document.createElement("option");
        option.value = item.name;
        option.textContent = item.name;
        loadItemSelect.appendChild(option);

        var option2 = document.createElement("option");
        option2.value = item.name;
        option2.textContent = item.name;
        unloadItemSelect.appendChild(option2);
    });
}

// Συνάρτηση για την φόρτωση ενός προϊόντος
function loadItem(event) {
    event.preventDefault();
    const itemName = document.getElementById("loadItemSelect").value;
    const quantityToLoad = parseInt(document.getElementById("loadQuantity").value, 10);

    if (!itemName || isNaN(quantityToLoad) || quantityToLoad <= 0) {
        alert("Παρακαλώ επιλέξτε ένα προϊόν και εισάγετε μια έγκυρη ποσότητα.");
        return;
    }

    // Αίτηση AJAX για τη φόρτωση του προϊόντος
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.status === 'success') {
                alert(`${quantityToLoad} ${itemName} φορτώθηκε στο φορτηγό.`);
                fetchTableData(); // Επαναφόρτωση των δεδομένων του πίνακα
            } else {
                alert("Σφάλμα κατά την φόρτωση του προϊόντος.");
            }
        }
    };
    xhttp.open("POST", "", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=load&item=" + encodeURIComponent(itemName) + "&quantity=" + encodeURIComponent(quantityToLoad));
}

// Συνάρτηση για την εκφόρτωση ενός προϊόντος
function unloadItem(event) {
    event.preventDefault();
    const itemName = document.getElementById("unloadItemSelect").value;
    const quantityToUnload = parseInt(document.getElementById("unloadQuantity").value, 10);

    if (!itemName || isNaN(quantityToUnload) || quantityToUnload <= 0) {
        alert("Παρακαλώ επιλέξτε ένα προϊόν και εισάγετε μια έγκυρη ποσότητα.");
        return;
    }

    // Αίτηση AJAX για την εκφόρτωση του προϊόντος
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.status === 'success') {
                alert(`${quantityToUnload} ${itemName} ξεφορτώθηκαν από το φορτηγό.`);
                fetchTableData(); // Επαναφόρτωση των δεδομένων του πίνακα
            } else {
                alert(response.message || "Σφάλμα κατά την αφαίρεση του προϊόντος.");
            }
        }
    };
    xhttp.open("POST", "", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=unload&item=" + encodeURIComponent(itemName) + "&quantity=" + encodeURIComponent(quantityToUnload));
}

// Συνάρτηση για την ανάκτηση και την ενημέρωση των δεδομένων του πίνακα
function fetchTableData() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var items = JSON.parse(this.responseText);
            updateTable(items);  // Συνάρτηση για την ενημέρωση του πίνακα με τα ανακτηθέντα δεδομένα
        }
    };
    xhttp.open("GET", "?fetch_car_table=1", true);
    xhttp.send();
}

// Συνάρτηση για την ενημέρωση του πίνακα με τα ανακτηθέντα προϊόντα
function updateTable(items) {
    var tableBody = document.querySelector(".inventory-section table tbody");
    tableBody.innerHTML = "";  // Καθαρισμός των υπαρχόντων γραμμών

    items.forEach(function(item) {
        var row = document.createElement("tr");
        var itemCell = document.createElement("td");
        var quantityCell = document.createElement("td");

        itemCell.textContent = item.item;
        quantityCell.textContent = item.quantity;

        row.appendChild(itemCell);
        row.appendChild(quantityCell);
        tableBody.appendChild(row);
    });
}

// Ανάκτηση προϊόντων και δεδομένων του πίνακα όταν φορτωθεί η σελίδα
document.addEventListener('DOMContentLoaded', function() {
    fetchItems();  // Ανάκτηση προϊόντων για τα dropdowns
    fetchTableData();  // Ανάκτηση αρχικών δεδομένων του πίνακα
});

// Σύνδεση των χειριστών γεγονότων με τις φόρμες
document.getElementById("loadForm").addEventListener("submit", loadItem);
document.getElementById("unloadForm").addEventListener("submit", unloadItem);

// Συνάρτηση για την ανάκτηση και την πληκτρολόγηση επιλογών προϊόντων
function fetchItems() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var items = JSON.parse(this.responseText);
            populateItemSelects(items);
        }
    };
    xhttp.open("GET", "?fetch_items=1", true);
    xhttp.send();
}

// Συνάρτηση για την ανάκτηση προϊόντων που είναι στο φορτίο του χρήστη
function fetchCarItems() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var items = JSON.parse(this.responseText);
            populateCarItemSelects(items);
        }
    };
    xhttp.open("GET", "?fetch_car_items=1", true);
    xhttp.send();
}

// Συνάρτηση για την πλήρωση των στοιχείων επιλογών με προϊόντα
function populateItemSelects(items) {
    var loadItemSelect = document.getElementById("loadItemSelect");

    // Καθαρισμός υπάρχουσων επιλογών
    loadItemSelect.innerHTML = "";

    items.forEach(function(item) {
        var option = document.createElement("option");
        option.value = item.name;
        option.textContent = item.name;
        loadItemSelect.appendChild(option);
    });
}

// Συνάρτηση για την πλήρωση των στοιχείων επιλογών με προϊόντα που είναι στο φορτίο
function populateCarItemSelects(items) {
    var unloadItemSelect = document.getElementById("unloadItemSelect");

    // Καθαρισμός υπάρχουσων επιλογών
    unloadItemSelect.innerHTML = "";

    items.forEach(function(item) {
        var option = document.createElement("option");
        option.value = item.item;
        option.textContent = item.item;
        unloadItemSelect.appendChild(option);
    });
}

// Συνάρτηση για την φόρτωση ενός προϊόντος
function loadItem(event) {
    event.preventDefault();
    const itemName = document.getElementById("loadItemSelect").value;
    const quantityToLoad = parseInt(document.getElementById("loadQuantity").value, 10);

    if (!itemName || isNaN(quantityToLoad) || quantityToLoad <= 0) {
        alert("Παρακαλώ επιλέξτε ένα προϊόν και εισάγετε μια έγκυρη ποσότητα.");
        return;
    }

    // Αίτηση AJAX για τη φόρτωση του προϊόντος
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.status === 'success') {
                alert(`${quantityToLoad} ${itemName} φορτώθηκε στο φορτηγό.`);
                fetchTableData(); // Επαναφόρτωση των δεδομένων του πίνακα
            } else {
                alert("Σφάλμα κατά την φόρτωση του προϊόντος.");
            }
        }
    };
    xhttp.open("POST", "", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=load&item=" + encodeURIComponent(itemName) + "&quantity=" + encodeURIComponent(quantityToLoad));
}

// Συνάρτηση για την εκφόρτωση ενός προϊόντος
function unloadItem(event) {
    event.preventDefault();
    const itemName = document.getElementById("unloadItemSelect").value;
    const quantityToUnload = parseInt(document.getElementById("unloadQuantity").value, 10);

    if (!itemName || isNaN(quantityToUnload) || quantityToUnload <= 0) {
        alert("Παρακαλώ επιλέξτε ένα προϊόν και εισάγετε μια έγκυρη ποσότητα.");
        return;
    }

    // Αίτηση AJAX για την εκφόρτωση του προϊόντος
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.status === 'success') {
                alert(`${quantityToUnload} ${itemName} ξεφορτώθηκαν από το φορτηγό.`);
                fetchTableData(); // Επαναφόρτωση των δεδομένων του πίνακα
            } else {
                alert(response.message || "Σφάλμα κατά την αφαίρεση του προϊόντος.");
            }
        }
    };
    xhttp.open("POST", "", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=unload&item=" + encodeURIComponent(itemName) + "&quantity=" + encodeURIComponent(quantityToUnload));
}

// Συνάρτηση για την ανάκτηση και την ενημέρωση των δεδομένων του πίνακα
function fetchTableData() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var items = JSON.parse(this.responseText);
            updateTable(items);  // Συνάρτηση για την ενημέρωση του πίνακα με τα ανακτηθέντα δεδομένα
        }
    };
    xhttp.open("GET", "?fetch_car_table=1", true);
    xhttp.send();
}

// Συνάρτηση για την ενημέρωση του πίνακα με τα ανακτηθέντα προϊόντα
function updateTable(items) {
    var tableBody = document.querySelector(".inventory-section table tbody");
    tableBody.innerHTML = "";  // Καθαρισμός των υπαρχόντων γραμμών

    items.forEach(function(item) {
        var row = document.createElement("tr");
        var itemCell = document.createElement("td");
        var quantityCell = document.createElement("td");

        itemCell.textContent = item.item;
        quantityCell.textContent = item.quantity;

        row.appendChild(itemCell);
        row.appendChild(quantityCell);
        tableBody.appendChild(row);
    });
}

// Ανάκτηση προϊόντων και δεδομένων του πίνακα όταν φορτωθεί η σελίδα
document.addEventListener('DOMContentLoaded', function() {
    fetchItems();  // Ανάκτηση προϊόντων για τα dropdowns
    fetchCarItems();  // Ανάκτηση προϊόντων που είναι στο φορτίο του χρήστη
    fetchTableData();  // Ανάκτηση αρχικών δεδομένων του πίνακα
});

// Σύνδεση των χειριστών γεγονότων με τις φόρμες
document.getElementById("loadForm").addEventListener("submit", loadItem);
document.getElementById("unloadForm").addEventListener("submit", unloadItem);

</script>

</body>
</html>
