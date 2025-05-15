<?php
$servername = "localhost"; // or your db server
$username = "root"; // your db username
$password = ""; // your db password
$dbname = "cybersphere_db"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully"; // Optional: for testing connection
?> 