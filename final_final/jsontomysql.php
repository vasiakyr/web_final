<?php
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "web_project";
$conn = new mysqli($servername, $username, $password, $db_name, 3308);
if ($conn->connect_error) {
    die("Connection failed" . $conn->connect_error);
}

// Read the JSON file contents
$jsondata = file_get_contents('product.json');

// Convert JSON object to PHP associative array
$data = json_decode($jsondata, true);

foreach ($data as $value) {
    $query = "INSERT INTO `items`(`id`, `name`, `category`) VALUES ('" . $value['id'] . "','" . $value['name'] . "', '" . $value['category'] . "')";

    mysqli_query($conn, $query);

    // Get the last inserted item ID
    $lastInsertId = mysqli_insert_id($conn);

    // Insert details for the current item
    foreach ($value['details'] as $detail) {
        $detailName = $detail['detail_name'];
        $detailValue = $detail['detail_value'];

        $query = "INSERT INTO `item_detail`(`item_id`, `detail_name`, `detail_value`) VALUES ('" . $value['id'] . "', '$detailName', '$detailValue')";
        mysqli_query($conn, $query);
    }
}

// Read the JSON file contents for categories
$jsdata = file_get_contents('categories.json');

// Convert JSON object to PHP associative array
$dt = json_decode($jsdata, true);

foreach ($dt as $value) {
    $query = "INSERT INTO `cate`(`id`, `category_name`) VALUES ('" . $value['id'] . "','" . $value['category_name'] . "')";

    mysqli_query($conn, $query);
}

// Close the database connection
mysqli_close($conn);
?>
