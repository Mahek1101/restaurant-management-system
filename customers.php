<?php
require_once "db_connection.php";

// Retrieve customers from database
$sql = "SELECT * FROM Customer ORDER BY customer_id DESC";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="css/style.css?v=5">
</head>

<body>

<div class="sidebar">
    <h2>Restaurant Manager</h2>

    <nav>
        <a href="index.php">Dashboard</a>
        <a href="customers.php" class="active">Customers</a>
        <a href="reservations.php">Reservations</a>
        <a href="tables.php">Dining Tables</a>
        <a href="orders.php">Food Orders</a>
    </nav>
</div>

<div class="main-content">

    <header>
        <h1>Customer Management</h1>
        <p>View and manage restaurant customers</p>
    </header>

    <div class="customer-section">

        <h2>Add New Customer</h2>

        <form id="addCustomerForm">
            <input type="hidden" id="customer_id" name="customer_id">

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name"
                    maxlength="50" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name"
                    maxlength="50" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone"
                    maxlength="20" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                    maxlength="100">
            </div>

            <button type="submit" class="add-btn" id="submitCustomerBtn">
                Add Customer
            </button>

            <button type="button" class="cancel-btn" id="cancelEditBtn" hidden>
                Cancel Edit
            </button>

        </form>

        <p id="customerMessage" role="status" aria-live="polite"></p>

        <h2 class="list-heading">Customer List</h2>

        <table class="customer-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>
                            <td><?php echo (int)$row['customer_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <button
                                    type="button"
                                    class="edit-btn"
                                    data-id="<?php echo (int)$row['customer_id']; ?>"
                                    data-first-name="<?php echo htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-last-name="<?php echo htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-phone="<?php echo htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-email="<?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="delete-btn"
                                    data-id="<?php echo (int)$row['customer_id']; ?>"
                                    data-name="<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6">No customers found.</td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- Custom Delete Confirmation Modal -->

<div id="deleteModal" class="modal-overlay" hidden>

    <div class="modal-box" role="dialog"
         aria-modal="true"
         aria-labelledby="deleteModalTitle"
         aria-describedby="deleteModalDescription">

        <div class="modal-icon">!</div>

        <h2 id="deleteModalTitle">Delete Customer?</h2>

        <p id="deleteModalDescription">
            Are you sure you want to delete
            <strong id="deleteCustomerName"></strong>?
            This action cannot be undone.
        </p>

        <p id="deleteError" class="modal-error" role="alert"></p>

        <div class="modal-actions">

            <button type="button" id="cancelDeleteBtn" class="modal-cancel">
                Cancel
            </button>

            <button type="button" id="confirmDeleteBtn" class="modal-delete">
                Delete Customer
            </button>

        </div>

    </div>

</div>

<script src="js/script.js"></script>

</body>
</html>