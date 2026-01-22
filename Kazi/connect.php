<?php
// mysqli connection (for other files)
$conn = mysqli_connect("localhost", "root", "", "kazi");
if (!$conn) {
    die("DB not connected" . mysqli_connect_error());
}

// PDO connection function (for signup.php)
function getDbConnection()
{
    $host = 'localhost';
    $dbname = "kazi";
    $username = "root";
    $password = "";

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>