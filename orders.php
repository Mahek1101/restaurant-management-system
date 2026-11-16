<?php
require_once "db_connection.php";

// Retrieve reservations with customer names
$reservationOptions = $con->query("
    SELECT
        r.reservation_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name
    FROM Reservation r
    INNER JOIN Customer c
        ON r.customer_id = c.customer_id
    WHERE r.status IN ('Pending', 'Confirmed', 'Completed')
    ORDER BY r.reservation_id DESC
");

// Retrieve existing servers
$serverOptions = $con->query("
    SELECT
        s.employee_id,
        CONCAT(e.first_name, ' ', e.last_name) AS server_name
    FROM Server s
    INNER JOIN Employee e
        ON s.employee_id = e.employee_id
    ORDER BY e.first_name
");

// Retrieve existing chefs
$chefOptions = $con->query("
    SELECT
        ch.employee_id,
        CONCAT(e.first_name, ' ', e.last_name) AS chef_name
    FROM Chef ch
    INNER JOIN Employee e
        ON ch.employee_id = e.employee_id
    ORDER BY e.first_name
");

// Retrieve food orders with customer, server, and chef names
$sql = "
    SELECT
        f.order_id,
        f.reservation_id,
        f.server_id,
        f.chef_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        CONCAT(s.first_name, ' ', s.last_name) AS server_name,
        CONCAT(ch.first_name, ' ', ch.last_name) AS chef_name,
        f.order_date,
        f.total_amount,
        f.payment_method,
        f.order_status
    FROM FoodOrder f

    INNER JOIN Reservation r
        ON f.reservation_id = r.reservation_id

    INNER JOIN Customer c
        ON r.customer_id = c.customer_id

    INNER JOIN Employee s
        ON f.server_id = s.employee_id

    INNER JOIN Employee ch
        ON f.chef_id = ch.employee_id

    ORDER BY f.order_id DESC
";

$result = $con->query($sql);

if (!$result) {
    http_response_code(500);
    exit("Unable to load food orders.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Food Order Management</title>

    <link rel="stylesheet" href="css/style.css?v=9">
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">

        <h2>Restaurant Manager</h2>

        <nav>
            <a href="index.php">Dashboard</a>
            <a href="customers.php">Customers</a>
            <a href="reservations.php">Reservations</a>
            <a href="tables.php">Dining Tables</a>
            <a href="orders.php" class="active">Food Orders</a>
        </nav>

    </div>

    <!-- Main Content -->
    <div class="main-content">

        <header>
            <h1>Food Order Management</h1>
            <p>View and manage restaurant food orders</p>
        </header>

        <div class="customer-section">

            <h2>Add New Food Order</h2>

            <form id="addOrderForm">
                <input
                    type="hidden"
                    id="order_id"
                    name="order_id"
                >

                <!-- Select Reservation -->
                <div class="form-group">
                    <label for="order_reservation">Reservation</label>

                    <select id="order_reservation" name="reservation_id" required>
                        <option value="">Select Reservation</option>

                        <?php while ($reservation = $reservationOptions->fetch_assoc()): ?>
                            <option value="<?php echo (int)$reservation['reservation_id']; ?>">
                                <?php echo htmlspecialchars(
                                    'Reservation #' . $reservation['reservation_id'] .
                                    ' - ' . $reservation['customer_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <!-- Select Server -->
                <div class="form-group">
                    <label for="order_server">Server</label>

                    <select id="order_server" name="server_id" required>
                        <option value="">Select Server</option>

                        <?php while ($server = $serverOptions->fetch_assoc()): ?>
                            <option value="<?php echo (int)$server['employee_id']; ?>">
                                <?php echo htmlspecialchars(
                                    $server['server_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <!-- Select Chef -->
                <div class="form-group">
                    <label for="order_chef">Chef</label>

                    <select id="order_chef" name="chef_id" required>
                        <option value="">Select Chef</option>

                        <?php while ($chef = $chefOptions->fetch_assoc()): ?>
                            <option value="<?php echo (int)$chef['employee_id']; ?>">
                                <?php echo htmlspecialchars(
                                    $chef['chef_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <!-- Total Amount -->
                <div class="form-group">
                    <label for="total_amount">Total Amount ($)</label>

                    <input
                        type="number"
                        id="total_amount"
                        name="total_amount"
                        min="0"
                        max="99999999.99"
                        step="0.01"
                        placeholder="Enter total amount"
                        required
                    >
                </div>

                <!-- Payment Method -->
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>

                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="Cash">Cash</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                    </select>
                </div>

                <!-- Order Status -->
                <div class="form-group">
                    <label for="order_status">Order Status</label>

                    <select id="order_status" name="order_status" required>
                        <option value="Pending">Pending</option>
                        <option value="Preparing">Preparing</option>
                        <option value="Served">Served</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="add-btn" id="submitOrderBtn">
                    Add Food Order
                </button>

                <button
                    type="button"
                    class="cancel-btn"
                    id="cancelOrderEditBtn"
                    hidden
                >
                    Cancel Edit
                </button>

            </form>

            <p id="orderMessage" role="status" aria-live="polite"></p>

            <h2 class="list-heading">Food Order List</h2>

            <table class="customer-table">

                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Server</th>
                        <th>Chef</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo (int)$row['order_id']; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $row['customer_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $row['server_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $row['chef_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $row['order_date'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    $<?php echo number_format(
                                        (float)$row['total_amount'],
                                        2
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $row['payment_method'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $row['order_status'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="order-edit-btn edit-btn"
                                        data-id="<?php echo (int)$row['order_id']; ?>"
                                        data-reservation-id="<?php echo (int)$row['reservation_id']; ?>"
                                        data-server-id="<?php echo (int)$row['server_id']; ?>"
                                        data-chef-id="<?php echo (int)$row['chef_id']; ?>"
                                        data-total="<?php echo htmlspecialchars($row['total_amount'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-payment="<?php echo htmlspecialchars($row['payment_method'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-status="<?php echo htmlspecialchars($row['order_status'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="order-delete-btn delete-btn"
                                        data-id="<?php echo (int)$row['order_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['customer_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        Delete
                                    </button>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9">
                                No food orders found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- Delete Food Order Confirmation Modal -->

<div id="deleteOrderModal" class="modal-overlay" hidden>

    <div class="modal-box"
         role="dialog"
         aria-modal="true"
         aria-labelledby="deleteOrderTitle"
         aria-describedby="deleteOrderDescription">

        <div class="modal-icon">!</div>

        <h2 id="deleteOrderTitle">Delete Food Order?</h2>

        <p id="deleteOrderDescription">
            Are you sure you want to delete food order
            <strong id="deleteOrderNumber"></strong>
            for <strong id="deleteOrderName"></strong>?
            This action cannot be undone.
        </p>

        <p id="deleteOrderError"
           class="modal-error"
           role="alert"></p>

        <div class="modal-actions">

            <button
                type="button"
                id="cancelOrderDeleteBtn"
                class="modal-cancel">
                Cancel
            </button>

            <button
                type="button"
                id="confirmOrderDeleteBtn"
                class="modal-delete">
                Delete Food Order
            </button>

        </div>

    </div>

</div>

<script src="js/script.js?v=6"></script>

</body>
</html>