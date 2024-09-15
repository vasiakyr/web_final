<!--εδώ ο admin επεξεργάζεται τη βάση ενημερώνει κατηγορίες και προιόντα  -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Storage</title>
    <link rel="stylesheet" type="text/css" href="menu.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js?v=1"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js?v=1"></script>
    <script src="https://kit.fontawesome.com/e267099c50.js?v=1" crossorigin="anonymous"></script>
    <style>
        /* Στυλ για το container της ποσότητας */
        #quantity-container {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f3e5f5; 
        }

        #quantity-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #800080; 
        }

        #quantity-container input {
            margin-bottom: 15px;
            width: 120px;
            padding: 8px;
            border: 1px solid #800080; 
            border-radius: 5px;
        }

        /* Στυλ για το κείμενο της τρέχουσας ποσότητας */
        #current-quantity {
            margin-bottom: 15px;
            font-weight: bold;
            color: #333;
        }

        /* Στυλ για τα κουμπιά */
        #update-button, #stro-button {
            background-color: #d1c4e9; 
            color: #800080; 
            border: 1px solid #800080;
            padding: 12px 25px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Εφέ hover στα κουμπιά */
        #update-button:hover, #stro-button:hover {
            background-color: #b39ddb; 
            color: white;
        }

        /* Ευθυγράμμιση των κουμπιών */
        .button-container {
            display: flex;
            justify-content: flex-start; /* Στοίχιση αριστερά */
            gap: 20px; /* Απόσταση μεταξύ των κουμπιών */
            margin-top: 20px;
        }

        /* Διάταξη και styling για το περιεχόμενο */
        .content {
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f3e5f5; 
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .user-box {
            margin-bottom: 20px;
        }

        .user-box label {
            font-weight: bold;
            color: #800080; 
        }

        .user-box select {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #800080;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="bg"></div>
    <nav>
        <a href="menu.php" class="active"><ion-icon name="home"></ion-icon></a>
        <br>
        <a href="map.html"><i class="fa-solid fa-map"></i></a>
        <br>
        <a href="Charts.html"><i class="fa-solid fa-line-chart"></i></a>
        <br>
        <a href="NewAnnouncements.php" style="font-size: 1.4rem"><i class="fa-solid fa-bullhorn"></i></a>
        <br>
        <a href="storage.html"><i class="fa-solid fa-warehouse"></i></a>
        <br>
        <a href="adminadds.php"><i class="fa-solid fa-user-plus"></i></a>
        <div class="navspacer"></div>
        <a href="logout.php"><i class="fa-solid fa-sign-out"></i></a>
    </nav>

    <div class="content">
        <h2>Επεξεργασία Βάσης</h2>

        <!-- Πεδία επιλογής κατηγορίας και προϊόντος -->
        <div class="user-box">
            <label for="category">Κατηγορία:</label>
            <select id="category" onchange="fetchProducts()">
                <option value="">Διάλεξε Κατηγορία</option>
                <!-- Οι επιλογές θα προστεθούν από το JavaScript -->
            </select>
        </div>

        <div class="user-box">
            <label for="product">Επιλογή Προϊόντος:</label>
            <select id="product" onchange="displayProductDetails()">
                <option value="">Διάλεξε Προϊόν</option>
                <!-- Οι επιλογές θα προστεθούν από το JavaScript -->
            </select>
        </div>

        <!-- Περιοχή για την ενημέρωση ποσότητας -->
        <div id="quantity-container">
            <label for="quantity">Ποσότητα:</label>
            <input type="number" id="quantity" min="0" step="1">
            <div id="current-quantity">Τρέχουσα Ποσότητα: </div>
        </div>

        <!-- Περιοχή για τα κουμπιά με ευθυγράμμιση -->
        <div class="button-container">
            <button id="update-button" onclick="updateQuantity()">Ενημέρωση Ποσότητας</button>
            <button id="stro-button" onclick="window.location.href='storage_check.php'">Μετάβαση στον εξοπλισμό</button>
        </div>
    </div>

    <script>
        /* Φόρτωση κατηγοριών από το fetch_categories.php */
        fetch('fetch_categories.php')
            .then(response => response.json())
            .then(data => {
                const categorySelect = document.getElementById('category');
                data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.category_name;
                    categorySelect.appendChild(option);
                });
            });

        /* Φόρτωση προϊόντων όταν επιλεγεί κατηγορία */
        function fetchProducts() {
            const categoryId = document.getElementById('category').value;
            fetch(`fetch_products.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    const productSelect = document.getElementById('product');
                    productSelect.innerHTML = '<option value="">Select Product</option>';
                    data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
                        productSelect.appendChild(option);
                    });
                });
        }

        /* Εμφάνιση λεπτομερειών προϊόντος */
        function displayProductDetails() {
            const productId = document.getElementById('product').value;
            if (productId) {
                fetch(`fetch_product_details.php?product_id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            document.getElementById('current-quantity').textContent = `Τρέχουσα Ποσότητα: ${data.quantity}`;
                            document.getElementById('quantity').value = data.quantity;
                        } else {
                            document.getElementById('current-quantity').textContent = 'Το προϊόν δεν βρέθηκε';
                            document.getElementById('quantity').value = '';
                        }
                    });
            } else {
                document.getElementById('current-quantity').textContent = '';
                document.getElementById('quantity').value = '';
            }
        }

        /* Ενημέρωση ποσότητας προϊόντος */
        function updateQuantity() {
            const productId = document.getElementById('product').value;
            const quantity = document.getElementById('quantity').value;
            if (productId && quantity !== '') {
                fetch('update_quantity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `product_id=${productId}&quantity=${quantity}`
                })
                .then(response => response.text())
                .then(result => {
                    alert(result);
                    displayProductDetails(); // Ενημέρωση της ποσότητας που εμφανίζεται
                });
                //αν δε βαλω κάτι βγάλε μήνυμα
            } else {
                alert('Πρέπει να επιλέξεις προϊόν και να εισάγεις κάποια ποσότητα');
            }
        }
    </script>
</body>

</html>
