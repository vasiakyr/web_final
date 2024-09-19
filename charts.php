<?php

require 'connection.php'; // Συνδέει τη βάση δεδομένων με το αρχείο connection.php
include 'session.php'; // Συμπεριλαμβάνει το session.php για διαχείριση session (έλεγχος σύνδεσης χρήστη)

// SQL ερώτημα για ανάκτηση δεδομένων. Επιλέγει τον μήνα από τη στήλη 'added', την κατάσταση 'status', και το πλήθος (COUNT) των αιτημάτων από τον πίνακα 'requests'
// Κάνει χρήση της GROUP BY για να ομαδοποιήσει τα αποτελέσματα ανά μήνα και κατάσταση
$query = "
    SELECT 
        MONTH(added) as month, 
        status, 
        COUNT(*) as count 
    FROM 
        requests 
    WHERE 
        status IN ('finished', 'waiting') -- Φιλτράρει τα αποτελέσματα μόνο για αιτήματα με κατάσταση 'finished' ή 'waiting'
    GROUP BY 
        MONTH(added), status -- Ομαδοποίηση των αποτελεσμάτων ανά μήνα και κατάσταση
";

// Εκτέλεση του SQL ερωτήματος και αποθήκευση του αποτελέσματος
$result = mysqli_query($conn, $query);

// Αρχικοποιεί έναν πίνακα $data με δύο κλειδιά, 'finished' και 'waiting', όπου αποθηκεύονται δεδομένα για 12 μήνες (0 έως 11)
$data = [
    'finished' => array_fill(0, 12, 0), // Γεμίζει με 0 για κάθε μήνα (0-based index)
    'waiting' => array_fill(0, 12, 0)
];

// Επεξεργάζεται τα αποτελέσματα του ερωτήματος
while ($row = mysqli_fetch_assoc($result)) {
    $month = $row['month'] - 1; // Το MONTH() επιστρέφει 1 για Ιανουάριο, 2 για Φεβρουάριο κτλ., το -1 το μετατρέπει σε 0-based index
    $status = $row['status']; // Καταγράφει την κατάσταση (finished ή waiting)
    $count = $row['count']; // Καταγράφει το πλήθος αιτημάτων για τον μήνα και την κατάσταση

    // Ελέγχει την κατάσταση και ενημερώνει τον αντίστοιχο πίνακα
    if ($status === 'finished') {
        $data['finished'][$month] = $count; // Αν η κατάσταση είναι 'finished', αποθηκεύει το πλήθος στον πίνακα 'finished'
    } elseif ($status === 'waiting') {
        $data['waiting'][$month] = $count; // Αν η κατάσταση είναι 'waiting', αποθηκεύει το πλήθος στον πίνακα 'waiting'
    }
}

// Μεταφορά των δεδομένων από PHP σε JavaScript μέσω της εντολής echo
// Χρησιμοποιεί την json_encode() για να μετατρέψει τους πίνακες PHP σε JSON μορφή, που είναι συμβατή με JavaScript
echo "<script>
    const finishedData = " . json_encode($data['finished']) . "; // Μεταφέρει τον πίνακα με τα τελειωμένα αιτήματα
    const waitingData = " . json_encode($data['waiting']) . "; // Μεταφέρει τον πίνακα με τα αιτήματα σε αναμονή
</script>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="menu.css"> <!-- Σύνδεση με το εξωτερικό αρχείο CSS για το μενού -->

    <!-- Εξωτερικές βιβλιοθήκες -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" /> <!-- Leaflet CSS για χάρτες -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script> <!-- Leaflet JS για χάρτες -->
    <script src="https://kit.fontawesome.com/e267099c50.js" crossorigin="anonymous"></script> <!-- FontAwesome για εικονίδια -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script> <!-- Chart.js για δημιουργία γραφημάτων -->

    <!-- Custom Styles -->
    <style>
        #legend {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: white;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); /* Στυλ για το πλαίσιο του legend */
        }
        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center; /* Στυλ για το κύριο περιεχόμενο */
        }
        .chart-container {
            width: 100%;
            max-width: 600px;
            margin-bottom: 20px; /* Στυλ για το container του γραφήματος */
        }
        .input-box {
            background-color: white;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); /* Στυλ για το input box */
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="bg"></div> <!-- Διακόσμηση για background -->
    
    <!-- Πλευρικό Μενού Πλοήγησης -->
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
    
    <div class="content">
        <h1>Welcome Admin!</h1>
        <h3>What would you like to see or do today?</h3> 

        <!-- Περιοχή θρύλου για το γράφημα -->
        <div id="legend">
            <strong>Legend:</strong>
            <ul>
                <li style="color: green;">Finished Requests: Πράσινη γραμμή</li>
                <li style="color: red;">Waiting Requests: Κόκκινη γραμμή</li>
            </ul>
        </div>

        <!-- Περιοχή γραφήματος -->
        <div class="chart-container">
            <canvas id="myChart" style="width:100%;"></canvas> <!-- Το γραφικό στοιχείο canvas για το γράφημα -->
        </div>

        <!-- Περιοχή επιλογής μήνα -->
        <div class="input-box">
            <label for="start">Συγκεκριμένος Μήνας:</label>
            <select id="start">
                <option value="-1">All</option> <!-- Προστέθηκε η επιλογή "All" για εμφάνιση όλων των μηνών -->
                <option value="0">January</option>
                <option value="1">February</option>
                <option value="2">March</option>
                <option value="3">April</option>
                <option value="4">May</option>
                <option value="5">June</option>
                <option value="6">July</option>
                <option value="7">August</option>
                <option value="8">September</option>
                <option value="9">October</option>
                <option value="10">November</option>
                <option value="11">December</option>
            </select>
            <button onclick="updateChart()">Update Chart</button> <!-- Κουμπί που καλεί τη συνάρτηση updateChart για να ανανεώσει το γράφημα -->
        </div>
    </div>

    <!-- JavaScript για το γράφημα -->
    <script>
    // Πίνακας με τα ονόματα των μηνών
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    // Δεδομένα για το γράφημα (ολοκληρωμένα και σε αναμονή αιτήματα) μεταβιβασμένα από την PHP
    const datasets = [
        { 
            label: 'Finished Requests',
            data: finishedData, // Δεδομένα που πέρασαν από την PHP
            borderColor: "green", // Χρώμα γραμμής
            fill: false
        },
        { 
            label: 'Waiting Requests',
            data: waitingData, // Δεδομένα που πέρασαν από την PHP
            borderColor: "red", // Χρώμα γραμμής
            fill: false
        }
    ];

    // Δημιουργία γραφήματος με Chart.js
        //βαζει τα χρωματα
    const ctx = document.getElementById('myChart').getContext('2d');
    let chart = new Chart(ctx, {
        type: 'line', // Είδος γραφήματος: Γραμμή
        data: {
            labels: months, // Ετικέτες άξονα x (μήνες)
            datasets: datasets // Δεδομένα
        },
        options: {
            legend: { display: true } // Εμφάνιση θρύλου
        }
    });

    // Συνάρτηση για ανανέωση του γραφήματος όταν ο χρήστης επιλέγει διαφορετικό μήνα
    function updateChart() {
        const selectedMonth = parseInt(document.getElementById('start').value); // Παίρνει τον επιλεγμένο μήνα από το dropdown

        if (selectedMonth === -1) {
            // Αν έχει επιλεγεί "All", εμφανίζει δεδομένα για όλους τους μήνες
            chart.data.labels = months; // Όλοι οι μήνες
            chart.data.datasets[0].data = finishedData; // Όλα τα ολοκληρωμένα αιτήματα
            chart.data.datasets[1].data = waitingData;  // Όλα τα αιτήματα σε αναμονή
        } else {
            // Εμφανίζει μόνο τον επιλεγμένο μήνα
            chart.data.labels = [months[selectedMonth]]; // Εμφανίζει μόνο τον επιλεγμένο μήνα
            chart.data.datasets[0].data = [finishedData[selectedMonth]]; // Δεδομένα για ολοκληρωμένα αιτήματα του επιλεγμένου μήνα
            chart.data.datasets[1].data = [waitingData[selectedMonth]];  // Δεδομένα για αιτήματα σε αναμονή του επιλεγμένου μήνα
        }

        chart.update(); // Ενημέρωση του γραφήματος με τα νέα δεδομένα
    }
    </script>

</body>
</html>
