<!-- ο πολίτης δηλώνει τι χρειάζεται , κατηγορία , προιόν και πόσοι χρειάζονται τη βοήθεια -->
<?php
require 'connection.php';
include 'session.php';

if (!$_SESSION['login_user']) {
    header("location:login.html");
    exit;
}

$username = $_SESSION['login_user'];

// Ανάκτηση των αιτημάτων του χρήστη από τη βάση δεδομένων
$query = "SELECT num_of_plp, item, added, status
          FROM requests 
          WHERE username = '$username'";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Storage</title>
    <link rel="stylesheet" type="text/css" href="menu.css">
    <link rel="stylesheet" type="text/css" href="form.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js?v=1"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js?v=1"></script>
    <script src="https://kit.fontawesome.com/e267099c50.js?v=1" crossorigin="anonymous"></script>






</head>

<body>
    <div class="bg"></div>
    <nav>
        <a href="citizen.php" class="active" onclick="location.reload();"><ion-icon name="home"></ion-icon></a>
        <br>
        <a href="user_announcements.php"><i class="fa-solid fa-bullhorn"></i></a>
        <br>
        <a href="new_request.php" style="font-size: 1.4rem"><i class="fa-solid fa-hands-helping"></i></i></a>
        <br>
        <div class="navspacer"></div>
        <a href="logout.php"><i class="fa-solid fa-sign-out"></i></a>
    </nav>

    <div class="content">
        <h1>Έχεις έλλειψη πρώτων αναγκών;</h1>
        
        <div class="form-container">
            <form name="form" action="save_request.php" method="POST">
                <div class="user-box">
                    <label for="category">Κατηγορία:</label>
                    <select id="category" name="category" onchange="fetchProducts()" required>
                        <option value="">Επέλεξε Κατηγορία</option>
                    </select>
                </div>

                <div class="user-box">
                    <label for="product">Επέλεξε Προϊόν:</label>
                    <select id="product" name="item" required>
                        <option value="">Επέλεξε Προϊόν</option>
                    </select>
                </div>

                <div class="user-box">
                    <label for="numplp">Πόσοι είστε:</label>
                    <input type="number" id="numplp" name="num_of_plp" min="0" step="1" required>
                </div>

                <button id="update-button" type="submit">Αποστολή αιτήματος</button>
            </form>
        </div>
        <!-- για το πίνακα αιτημάτων-->
        <div class="table-container">
            <h3>Τα αιτήματα σου:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Προϊόν</th>
                        <th>Άτομα</th>
                        <th>Ημερομηνία Δημιουργίας</th>
                        <th>Κατάσταση Αιτήματος</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['item']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['num_of_plp']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['added']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Δεν υπάρχουν αιτήματα.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        fetch('fetch_categories.php')
            .then(response => response.json()) // τα δεδομένα σε μορφη json
            .then(data => {
                const categorySelect = document.getElementById('category');
                data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.category_name;
                    categorySelect.appendChild(option);//προσθήκη επιλογής
                });
            });
        // εδώ βάζει τα προιόντα με βάση την κατηγορία που επιλέχθηκε
        function fetchProducts() {
            const categoryId = document.getElementById('category').value;
            fetch(`fetch_products.php?category_id=${categoryId}`)// αιτημα για να παρω τα προιοντα
                .then(response => response.json())
                .then(data => {
                    const productSelect = document.getElementById('product');
                    productSelect.innerHTML = '<option value="">Επέλεξε Προϊόν</option>';
                    data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
                        productSelect.appendChild(option);
                    });
                });
        }
    </script>
</body>

</html>
