<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../db_connection.php";

// Get HTTP request method
$method = $_SERVER["REQUEST_METHOD"];

// Allow GET, POST, PUT, and DELETE requests
if (!in_array($method, ["GET", "POST", "PUT", "DELETE"], true)) {
    header("Allow: GET, POST, PUT, DELETE");
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Method not allowed."
    ]);

    exit;
}

// ==========================================
// GET: RETRIEVE FOOD ORDERS
// ==========================================

if ($method === "GET") {

    try {

        $sql = "
            SELECT
                f.order_id,
                f.reservation_id,
                f.server_id,
                f.chef_id,

                CONCAT(c.first_name, ' ', c.last_name)
                    AS customer_name,

                CONCAT(s.first_name, ' ', s.last_name)
                    AS server_name,

                CONCAT(ch.first_name, ' ', ch.last_name)
                    AS chef_name,

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

        $orders = [];

        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        echo json_encode([
            "success" => true,
            "total" => count($orders),
            "data" => $orders
        ]);

    } catch (mysqli_sql_exception $e) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Unable to retrieve food orders."
        ]);

    }

    $con->close();
    exit;
}

// ==========================================
// DELETE FOOD ORDER
// ==========================================

if ($method === "DELETE") {

    parse_str(file_get_contents("php://input"), $deleteData);

    $order_id = filter_var(
        $deleteData["order_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    // Validate order ID
    if (!$order_id || $order_id <= 0) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Valid order ID is required."
        ]);

        exit;
    }

    try {

        // Delete selected food order
        $stmt = $con->prepare(
            "DELETE FROM FoodOrder WHERE order_id = ?"
        );

        $stmt->bind_param("i", $order_id);

        $stmt->execute();

        $deletedRows = $stmt->affected_rows;

        $stmt->close();

        if ($deletedRows > 0) {

            echo json_encode([
                "success" => true,
                "message" => "Food order deleted successfully!"
            ]);

        } else {

            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" => "Food order not found."
            ]);
        }

    } catch (mysqli_sql_exception $e) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Unable to delete food order."
        ]);

    }

    $con->close();
    exit;
}

// Get form information
if ($method === "POST") {

    $data = $_POST;

} else {

    // PHP does not automatically populate $_POST for PUT requests
    parse_str(file_get_contents("php://input"), $data);

}

// Retrieve form information
$reservation_id = filter_var(
    $data["reservation_id"] ?? null,
    FILTER_VALIDATE_INT
);

$server_id = filter_var(
    $data["server_id"] ?? null,
    FILTER_VALIDATE_INT
);

$chef_id = filter_var(
    $data["chef_id"] ?? null,
    FILTER_VALIDATE_INT
);

$total_amount = trim($data["total_amount"] ?? "");
$payment_method = $data["payment_method"] ?? "";
$order_status = $data["order_status"] ?? "Pending";

// Order ID is required only for UPDATE
$order_id = null;

if ($method === "PUT") {

    $order_id = filter_var(
        $data["order_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$order_id || $order_id <= 0) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Valid order ID is required."
        ]);

        exit;
    }
}

// Validate required fields
if (
    !$reservation_id ||
    !$server_id ||
    !$chef_id ||
    $total_amount === "" ||
    $payment_method === ""
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please complete all required fields."
    ]);

    exit;
}

// Validate total amount
if (
    !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $total_amount) ||
    (float)$total_amount < 0
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid total amount."
    ]);

    exit;
}

// Validate payment method
$allowedPayments = [
    "Cash",
    "Credit Card",
    "Debit Card"
];

if (!in_array($payment_method, $allowedPayments, true)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid payment method."
    ]);

    exit;
}

// Validate order status
$allowedStatuses = [
    "Pending",
    "Preparing",
    "Served",
    "Completed"
];

if (!in_array($order_status, $allowedStatuses, true)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid order status."
    ]);

    exit;
}

try {

    // UPDATE: Check whether the food order exists
    if ($method === "PUT") {

        $stmt = $con->prepare(
            "SELECT order_id
             FROM FoodOrder
             WHERE order_id = ?"
        );

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {

            $stmt->close();

            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" => "Food order not found."
            ]);

            exit;
        }

        $stmt->close();
    }

    // Check whether reservation exists and is not cancelled
    $stmt = $con->prepare(
        "SELECT reservation_id
         FROM Reservation
         WHERE reservation_id = ?
           AND status <> 'Cancelled'"
    );

    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {

        $stmt->close();

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Selected reservation does not exist or is cancelled."
        ]);

        exit;
    }

    $stmt->close();

    // Check whether server exists
    $stmt = $con->prepare(
        "SELECT employee_id
         FROM Server
         WHERE employee_id = ?"
    );

    $stmt->bind_param("i", $server_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {

        $stmt->close();

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Selected server does not exist."
        ]);

        exit;
    }

    $stmt->close();

    // Check whether chef exists
    $stmt = $con->prepare(
        "SELECT employee_id
         FROM Chef
         WHERE employee_id = ?"
    );

    $stmt->bind_param("i", $chef_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {

        $stmt->close();

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Selected chef does not exist."
        ]);

        exit;
    }

    $stmt->close();

    // CREATE: Insert new food order
    if ($method === "POST") {

        $stmt = $con->prepare(
            "INSERT INTO FoodOrder
             (reservation_id, server_id, chef_id,
              total_amount, payment_method, order_status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "iiidss",
            $reservation_id,
            $server_id,
            $chef_id,
            $total_amount,
            $payment_method,
            $order_status
        );

        $stmt->execute();

        $order_id = $con->insert_id;

        $stmt->close();

        http_response_code(201);

        echo json_encode([
            "success" => true,
            "message" => "Food order added successfully!",
            "order_id" => $order_id
        ]);

    } else {

        // UPDATE: Modify existing food order
        $stmt = $con->prepare(
            "UPDATE FoodOrder
             SET reservation_id = ?,
                 server_id = ?,
                 chef_id = ?,
                 total_amount = ?,
                 payment_method = ?,
                 order_status = ?
             WHERE order_id = ?"
        );

        $stmt->bind_param(
            "iiidssi",
            $reservation_id,
            $server_id,
            $chef_id,
            $total_amount,
            $payment_method,
            $order_status,
            $order_id
        );

        $stmt->execute();
        $stmt->close();

        http_response_code(200);

        echo json_encode([
            "success" => true,
            "message" => "Food order updated successfully!",
            "order_id" => $order_id
        ]);

    }

} catch (mysqli_sql_exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to save food order."
    ]);

}

$con->close();

?>