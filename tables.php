<?php
require_once "db_connection.php";

// Retrieve all dining tables from MySQL
$sql = "
    SELECT
        table_id,
        table_number,
        capacity,
        section
    FROM DiningTable
    ORDER BY table_number ASC
";

$result = $con->query($sql);

if (!$result) {
    http_response_code(500);
    exit("Unable to load dining tables.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dining Table Management</title>

    <link rel="stylesheet" href="css/style.css?v=10">
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">

        <h2>Restaurant Manager</h2>

        <nav>
            <a href="index.php">Dashboard</a>
            <a href="customers.php">Customers</a>
            <a href="reservations.php">Reservations</a>
            <a href="tables.php" class="active">Dining Tables</a>
            <a href="orders.php">Food Orders</a>
        </nav>

    </div>

    <!-- Main Content -->
    <div class="main-content">

        <header>
            <h1>Dining Table Management</h1>
            <p>View restaurant dining tables and their availability status</p>
        </header>

        <div class="customer-section">

            <h2>Dining Table List</h2>

            <table class="customer-table">

                <thead>
                    <tr>
                        <th>Table ID</th>
                        <th>Table Number</th>
                        <th>Seating Capacity</th>
                        <th>Section</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo (int)$row['table_id']; ?>
                                </td>

                                <td>
                                    Table <?php echo (int)$row['table_number']; ?>
                                </td>

                                <td>
                                    <?php echo (int)$row['capacity']; ?> Guests
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $row['section'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4">
                                No dining tables found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <script src="js/script.js?v=6"></script>

</body>
</html>