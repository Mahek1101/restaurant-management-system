<?php
require_once "db_connection.php";

// Retrieve customers for the reservation form
$customerOptions = $con->query("
    SELECT customer_id, first_name, last_name
    FROM Customer
    ORDER BY first_name, last_name
");

// Retrieve all dining tables for the reservation form
$tableOptions = $con->query("
    SELECT table_id, table_number, capacity, section
    FROM DiningTable
    ORDER BY table_number
");

// Retrieve reservations along with customer names
$sql = "
    SELECT
        r.reservation_id,
        r.customer_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        r.reservation_date,
        r.reservation_time,
        r.party_size,
        r.status,
        GROUP_CONCAT(
            d.table_number
            ORDER BY d.table_number
            SEPARATOR ', '
        ) AS assigned_tables,
        MIN(d.table_id) AS table_id
    FROM Reservation r
    INNER JOIN Customer c
        ON r.customer_id = c.customer_id
    LEFT JOIN ReservationTable rt
        ON r.reservation_id = rt.reservation_id
    LEFT JOIN DiningTable d
        ON rt.table_id = d.table_id
    GROUP BY
        r.reservation_id,
        r.customer_id,
        c.first_name,
        c.last_name,
        r.reservation_date,
        r.reservation_time,
        r.party_size,
        r.status
    ORDER BY r.reservation_date DESC, r.reservation_time DESC
";

$result = $con->query($sql);

if (!$result) {
    http_response_code(500);
    exit("Unable to load reservations.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reservation Management</title>

    <link rel="stylesheet" href="css/style.css?v=7">
</head>

<body>

<div class="sidebar">

    <h2>Restaurant Manager</h2>

    <nav>
        <a href="index.php">Dashboard</a>
        <a href="customers.php">Customers</a>
        <a href="reservations.php" class="active">Reservations</a>
        <a href="tables.php">Dining Tables</a>
        <a href="orders.php">Food Orders</a>
    </nav>

</div>

<div class="main-content">

    <header>
        <h1>Reservation Management</h1>
        <p>View and manage restaurant reservations</p>
    </header>

    <div class="customer-section">

    <h2>Add New Reservation</h2>

    <form id="addReservationForm">
        <input
            type="hidden"
            id="reservation_id"
            name="reservation_id"
        >

        <!-- Select Customer -->
        <div class="form-group">
            <label for="reservation_customer">Customer</label>

            <select id="reservation_customer" name="customer_id" required>
                <option value="">Select Customer</option>

                <?php while ($customer = $customerOptions->fetch_assoc()): ?>
                    <option value="<?php echo (int)$customer['customer_id']; ?>">
                        <?php echo htmlspecialchars(
                            $customer['first_name'] . ' ' . $customer['last_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </option>
                <?php endwhile; ?>

            </select>
        </div>

        <!-- Reservation Date -->
        <div class="form-group">
            <label for="reservation_date">Reservation Date</label>

            <input
                type="date"
                id="reservation_date"
                name="reservation_date"
                min="<?php echo date('Y-m-d'); ?>"
                required
            >
        </div>

        <!-- Reservation Time -->
        <div class="form-group">
            <label for="reservation_time">Reservation Time</label>

            <input
                type="time"
                id="reservation_time"
                name="reservation_time"
                required
            >
        </div>

        <!-- Number of Guests -->
        <div class="form-group">
            <label for="party_size">Number of Guests</label>

            <input
                type="number"
                id="party_size"
                name="party_size"
                min="1"
                required
            >
        </div>

        <!-- Dining Table -->
        <div class="form-group">
            <label for="reservation_table">Dining Table</label>

            <select id="reservation_table" name="table_id" required>
                <option value="">Select Dining Table</option>

                <?php while ($table = $tableOptions->fetch_assoc()): ?>
                    <option value="<?php echo (int)$table['table_id']; ?>">
                        <?php echo htmlspecialchars(
                            'Table ' . $table['table_number'] .
                            ' — Capacity: ' . $table['capacity'] .
                            ' (' . $table['section'] . ')',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </option>
                <?php endwhile; ?>

            </select>
        </div>

        <!-- Reservation Status -->
        <div class="form-group">
            <label for="reservation_status">Status</label>

            <select id="reservation_status" name="status" required>
                <option value="Pending">Pending</option>
                <option value="Confirmed">Confirmed</option>
                <option value="Cancelled">Cancelled</option>
                <option value="Completed">Completed</option>
            </select>
        </div>

        <button type="submit" class="add-btn" id="submitReservationBtn">
            Add Reservation
        </button>

        <button
            type="button"
            class="cancel-btn"
            id="cancelReservationEditBtn"
            hidden
        >
            Cancel Edit
        </button>

    </form>

    <p id="reservationMessage" role="status" aria-live="polite"></p>

    <h2 class="list-heading">Reservation List</h2>

        <table class="customer-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Guests</th>
                    <th>Table</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>
                            <td>
                                <?php echo (int)$row['reservation_id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['customer_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['reservation_date'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <?php echo date("h:i A", strtotime($row['reservation_time'])); ?>
                            </td>

                            <td>
                                <?php echo (int)$row['party_size']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['assigned_tables'] ?? 'Not Assigned', ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="reservation-edit-btn edit-btn"
                                    data-id="<?php echo (int)$row['reservation_id']; ?>"
                                    data-customer-id="<?php echo (int)$row['customer_id']; ?>"
                                    data-table-id="<?php echo (int)$row['table_id']; ?>"
                                    data-date="<?php echo htmlspecialchars($row['reservation_date'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-time="<?php echo htmlspecialchars(substr($row['reservation_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-party-size="<?php echo (int)$row['party_size']; ?>"
                                    data-status="<?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="reservation-delete-btn delete-btn"
                                    data-id="<?php echo (int)$row['reservation_id']; ?>"
                                    data-name="<?php echo htmlspecialchars($row['customer_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8">No reservations found.</td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- Delete Reservation Confirmation Modal -->

<div id="deleteReservationModal" class="modal-overlay" hidden>

    <div class="modal-box"
         role="dialog"
         aria-modal="true"
         aria-labelledby="deleteReservationTitle"
         aria-describedby="deleteReservationDescription">

        <div class="modal-icon">!</div>

        <h2 id="deleteReservationTitle">Delete Reservation?</h2>

        <p id="deleteReservationDescription">
            Are you sure you want to delete the reservation for
            <strong id="deleteReservationName"></strong>?
            This action cannot be undone.
        </p>

        <p id="deleteReservationError"
           class="modal-error"
           role="alert"></p>

        <div class="modal-actions">

            <button
                type="button"
                id="cancelReservationDeleteBtn"
                class="modal-cancel">
                Cancel
            </button>

            <button
                type="button"
                id="confirmReservationDeleteBtn"
                class="modal-delete">
                Delete Reservation
            </button>

        </div>

    </div>

</div>

<script src="js/script.js?v=3"></script>

</body>
</html>