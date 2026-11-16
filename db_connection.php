<?php

// Load database configuration
$config = require __DIR__ . "/db_config.php";

// Retrieve database settings
$host = $config["host"];
$username = $config["username"];
$password = $config["password"];
$database = $config["database"];
$port = $config["port"];

// Connect to MySQL
try {

    $con = new mysqli(
        $host,
        $username,
        $password,
        $database,
        $port
    );

    $con->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    // Do not expose database credentials or technical errors
    http_response_code(500);

    die("Database connection failed. Please check your configuration.");

}

?>