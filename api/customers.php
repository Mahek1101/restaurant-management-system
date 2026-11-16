<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../db_connection.php";

// Get request method
$method = $_SERVER["REQUEST_METHOD"];

// Allow POST, PUT, and DELETE requests
if (!in_array($method, ["GET", "POST", "PUT", "DELETE"], true)) {
    header("Allow: GET, POST, PUT, DELETE");
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

// ==========================================
// GET: Retrieve Customers
// ==========================================

if ($method === "GET") {

    try {

        $result = $con->query(
            "SELECT customer_id, first_name, last_name, phone, email
             FROM Customer
             ORDER BY customer_id DESC"
        );

        $customers = [];

        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }

        echo json_encode([
            "success" => true,
            "total" => count($customers),
            "data" => $customers
        ]);

    } catch (mysqli_sql_exception $e) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Unable to retrieve customers."
        ]);

    }

    $con->close();
    exit;
}

// DELETE: Remove an existing customer
if ($method === "DELETE") {

    parse_str(file_get_contents("php://input"), $deleteData);

    $customer_id = filter_var(
        $deleteData["customer_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$customer_id || $customer_id <= 0) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Valid customer ID is required"
        ]);

        exit;
    }

    try {

        $stmt = $con->prepare(
            "DELETE FROM Customer WHERE customer_id = ?"
        );

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {

            echo json_encode([
                "success" => true,
                "message" => "Customer deleted successfully!"
            ]);

        } else {

            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" => "Customer not found"
            ]);
        }

        $stmt->close();

    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1451) {

            http_response_code(409);

            echo json_encode([
                "success" => false,
                "message" => "Cannot delete this customer because they have existing reservations."
            ]);

        } else {

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "message" => "Unable to delete customer."
            ]);
        }
    }

    $con->close();
    exit;
}

// Get form data based on request method
if ($method === "POST") {
    $data = $_POST;
} else {
    // PHP does not automatically populate $_POST for PUT requests
    parse_str(file_get_contents("php://input"), $data);
}

// Get customer information
$first_name = trim($data["first_name"] ?? "");
$last_name = trim($data["last_name"] ?? "");
$phone = trim($data["phone"] ?? "");
$email = trim($data["email"] ?? "");

// Validate required fields
if ($first_name === "" || $last_name === "" || $phone === "") {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "First name, last name, and phone are required"
    ]);
    exit;
}

// Validate email
if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email address"
    ]);
    exit;
}

// Validate field lengths
if (
    strlen($first_name) > 50 ||
    strlen($last_name) > 50 ||
    strlen($phone) > 20 ||
    strlen($email) > 100
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "One or more fields exceed the allowed length"
    ]);
    exit;
}

// Use NULL if email is empty
$email_value = $email === "" ? null : $email;

try {

    // CREATE: Add new customer
    if ($method === "POST") {

        $stmt = $con->prepare(
            "INSERT INTO Customer
             (first_name, last_name, phone, email)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $first_name,
            $last_name,
            $phone,
            $email_value
        );

        $stmt->execute();

        http_response_code(201);

        echo json_encode([
            "success" => true,
            "message" => "Customer added successfully!",
            "customer_id" => $con->insert_id
        ]);

    }

    // UPDATE: Edit existing customer
    elseif ($method === "PUT") {

        // Validate customer ID
        $customer_id = filter_var(
            $data["customer_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($customer_id === false || $customer_id === null || $customer_id <= 0) {
            http_response_code(400);

            echo json_encode([
                "success" => false,
                "message" => "Valid customer ID is required"
            ]);

            exit;
        }

        // Check whether customer exists
        $check = $con->prepare(
            "SELECT customer_id FROM Customer WHERE customer_id = ?"
        );

        $check->bind_param("i", $customer_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            $check->close();

            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" => "Customer not found"
            ]);

            exit;
        }

        $check->close();

        // Update customer information
        $stmt = $con->prepare(
            "UPDATE Customer
             SET first_name = ?,
                 last_name = ?,
                 phone = ?,
                 email = ?
             WHERE customer_id = ?"
        );

        $stmt->bind_param(
            "ssssi",
            $first_name,
            $last_name,
            $phone,
            $email_value,
            $customer_id
        );

        $stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Customer updated successfully!"
        ]);

    }

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() == 1062) {
        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "This email address already exists"
        ]);

    } else {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Database operation failed"
        ]);
    }
}

if (isset($stmt)) {
    $stmt->close();
}

$con->close();

?>