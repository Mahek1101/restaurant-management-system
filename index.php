<?php
require_once "db_connection.php";

// Get total customers
$customers = $con->query("SELECT COUNT(*) AS total FROM Customer");
$total_customers = $customers->fetch_assoc()['total'];

// Get total reservations
$reservations = $con->query("SELECT COUNT(*) AS total FROM Reservation");
$total_reservations = $reservations->fetch_assoc()['total'];

// Get total dining tables
$tables = $con->query("SELECT COUNT(*) AS total FROM DiningTable");
$total_tables = $tables->fetch_assoc()['total'];

// Get total food orders
$orders = $con->query("SELECT COUNT(*) AS total FROM FoodOrder");
$total_orders = $orders->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Restaurant Management System</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="sidebar">

        <h2>Restaurant Manager</h2>

        <nav>
            <a href="index.php" class="active">Dashboard</a>
            <a href="customers.php">Customers</a>
            <a href="reservations.php">Reservations</a>
            <a href="tables.php">Dining Tables</a>
            <a href="orders.php">Food Orders</a>
        </nav>

    </div>

    <div class="main-content">

        <header>
            <h1>Restaurant Dashboard</h1>
            <p>Welcome to your Restaurant Management System</p>
        </header>

        <div class="dashboard-cards">

            <div class="card">
                <h3>Total Customers</h3>
                <h2><?php echo $total_customers; ?></h2>
            </div>

            <div class="card">
                <h3>Total Reservations</h3>
                <h2><?php echo $total_reservations; ?></h2>
            </div>

            <div class="card">
                <h3>Dining Tables</h3>
                <h2><?php echo $total_tables; ?></h2>
            </div>

            <div class="card">
                <h3>Food Orders</h3>
                <h2><?php echo $total_orders; ?></h2>
            </div>

        </div>

    </div>

    <script src="js/script.js"></script>

</body>
</html>