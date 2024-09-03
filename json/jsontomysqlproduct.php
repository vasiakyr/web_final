<?php
// Read the JSON data from a file
$jsonData = file_get_contents('product.json');

// Decode the JSON data into a PHP array
$dataArray = json_decode($jsonData, true);

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "web_project";
// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $db_name, 3307);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Loop through each item in the data array
foreach ($dataArray as $item) {
    $itemId = $item['id'];

    // Loop through each detail in the details array
    foreach ($item['details'] as $detail) {
        $detailName = $detail['detail_name'];
        $detailValue = $detail['detail_value'];

        // Prepare the SQL query
        $sql = "INSERT INTO `item_detail`(`item_id`, `detail_name`, `detail_value`) VALUES ('$itemId', '$detailName', '$detailValue')";

        // Execute the SQL query
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully for item ID: $itemId<br>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Close the database connection
$conn->close();
?>
