<?php
header('Content-Type: application/json');

// MySQL connection parameters
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "army_personnel";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// SQL query
$sql = "SELECT * FROM army_personnel";
$result = $conn->query($sql);

$personnel = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $personnel[] = $row;
    }
}

$conn->close();

// Return data as JSON
echo json_encode($personnel);
?>
