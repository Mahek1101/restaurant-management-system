<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../db_connection.php";

// Get HTTP request method
$method = $_SERVER["REQUEST_METHOD"];

// Allow POST, PUT, and DELETE
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
// GET: RETRIEVE RESERVATIONS
// ==========================================

if ($method === "GET") {

    try {

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
                ) AS assigned_tables
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

            ORDER BY r.reservation_date DESC,
                     r.reservation_time DESC
        ";

        $result = $con->query($sql);

        $reservations = [];

        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }

        echo json_encode([
            "success" => true,
            "total" => count($reservations),
            "data" => $reservations
        ]);

    } catch (mysqli_sql_exception $e) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Unable to retrieve reservations."
        ]);

    }

    $con->close();
    exit;
}

// ==========================================
// DELETE RESERVATION
// ==========================================

if ($method === "DELETE") {

    parse_str(file_get_contents("php://input"), $deleteData);

    $reservation_id = filter_var(
        $deleteData["reservation_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$reservation_id || $reservation_id <= 0) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Valid reservation ID is required."
        ]);

        exit;
    }

    try {

        $con->begin_transaction();

        // Delete the reservation
        $stmt = $con->prepare(
            "DELETE FROM Reservation WHERE reservation_id = ?"
        );

        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();

        $deletedRows = $stmt->affected_rows;
        $stmt->close();

        if ($deletedRows === 0) {
            $con->rollback();

            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" => "Reservation not found."
            ]);

            exit;
        }

        $con->commit();

        echo json_encode([
            "success" => true,
            "message" => "Reservation deleted successfully!"
        ]);

    } catch (mysqli_sql_exception $e) {

        $con->rollback();

        if ($e->getCode() == 1451) {

            http_response_code(409);

            echo json_encode([
                "success" => false,
                "message" => "Cannot delete this reservation because it has existing food orders."
            ]);

        } else {

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "message" => "Unable to delete reservation."
            ]);
        }

    }

    $con->close();
    exit;
}

// Get request data
if ($method === "POST") {
    $data = $_POST;
} else {
    parse_str(file_get_contents("php://input"), $data);
}

// Retrieve form information
$customer_id = filter_var(
    $data["customer_id"] ?? null,
    FILTER_VALIDATE_INT
);

$table_id = filter_var(
    $data["table_id"] ?? null,
    FILTER_VALIDATE_INT
);

$reservation_date = trim($data["reservation_date"] ?? "");
$reservation_time = trim($data["reservation_time"] ?? "");

$party_size = filter_var(
    $data["party_size"] ?? null,
    FILTER_VALIDATE_INT
);

$status = $data["status"] ?? "Pending";

// Reservation ID is required only for UPDATE
$reservation_id = null;

if ($method === "PUT") {

    $reservation_id = filter_var(
        $data["reservation_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$reservation_id || $reservation_id <= 0) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Valid reservation ID is required."
        ]);
        exit;
    }
}

// Validate required fields
if (
    !$customer_id ||
    !$table_id ||
    !$party_size ||
    $party_size <= 0 ||
    $reservation_date === "" ||
    $reservation_time === ""
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please complete all required fields."
    ]);
    exit;
}

// Validate reservation status
$allowedStatuses = [
    "Pending",
    "Confirmed",
    "Cancelled",
    "Completed"
];

if (!in_array($status, $allowedStatuses, true)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid reservation status."
    ]);
    exit;
}

// New reservations can only be Pending or Confirmed
if (
    $method === "POST" &&
    !in_array($status, ["Pending", "Confirmed"], true)
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "New reservations must be Pending or Confirmed."
    ]);
    exit;
}

// Validate date and time
$dateTime = DateTimeImmutable::createFromFormat(
    "!Y-m-d H:i",
    $reservation_date . " " . substr($reservation_time, 0, 5)
);

if (
    !$dateTime ||
    $dateTime->format("Y-m-d H:i") !==
        $reservation_date . " " . substr($reservation_time, 0, 5)
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid reservation date or time."
    ]);
    exit;
}

// Active reservations must be in the future
if (
    in_array($status, ["Pending", "Confirmed"], true) &&
    $dateTime <= new DateTimeImmutable()
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please select a future reservation date and time."
    ]);
    exit;
}

$transactionStarted = false;

try {

    // Start database transaction
    $con->begin_transaction();
    $transactionStarted = true;

    // For UPDATE, check that reservation exists
    if ($method === "PUT") {

        $stmt = $con->prepare(
            "SELECT reservation_id
             FROM Reservation
             WHERE reservation_id = ?
             FOR UPDATE"
        );

        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $stmt->close();
            throw new Exception("Reservation not found.", 404);
        }

        $stmt->close();
    }

    // Check customer exists
    $stmt = $con->prepare(
        "SELECT customer_id
         FROM Customer
         WHERE customer_id = ?"
    );

    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        throw new Exception("Selected customer does not exist.");
    }

    $stmt->close();

    // Lock selected dining table and check capacity
    $stmt = $con->prepare(
        "SELECT capacity
        FROM DiningTable
        WHERE table_id = ?
        FOR UPDATE"
    );

    $stmt->bind_param("i", $table_id);
    $stmt->execute();
    $stmt->bind_result($capacity);

    if (!$stmt->fetch()) {
        $stmt->close();
        throw new Exception("Selected dining table does not exist.");
    }

    $stmt->close();

    // Check whether the table can accommodate the guests
    if ($party_size > $capacity) {
        throw new Exception(
            "The selected dining table cannot accommodate this many guests."
        );
    }
        // Check overlapping reservations
    // Each reservation occupies a table for 2 hours
    // Exclude the reservation currently being edited

    $excludeId = $method === "PUT" ? $reservation_id : 0;

    if (in_array($status, ["Pending", "Confirmed"], true)) {

        $stmt = $con->prepare(
            "SELECT COUNT(*)
             FROM ReservationTable rt
             JOIN Reservation r
                 ON rt.reservation_id = r.reservation_id
             WHERE rt.table_id = ?
               AND r.reservation_date = ?
               AND r.status IN ('Pending', 'Confirmed')
               AND r.reservation_id <> ?
               AND TIME_TO_SEC(r.reservation_time) <
                   TIME_TO_SEC(?) + 7200
               AND TIME_TO_SEC(r.reservation_time) + 7200 >
                   TIME_TO_SEC(?)"
        );

        $stmt->bind_param(
            "isiss",
            $table_id,
            $reservation_date,
            $excludeId,
            $reservation_time,
            $reservation_time
        );

        $stmt->execute();
        $stmt->bind_result($conflicts);
        $stmt->fetch();
        $stmt->close();

        if ($conflicts > 0) {
            throw new Exception(
                "This dining table is already reserved during the selected time."
            );
        }
    }

    // CREATE RESERVATION
    if ($method === "POST") {

        $stmt = $con->prepare(
            "INSERT INTO Reservation
             (customer_id, reservation_date, reservation_time, party_size, status)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issis",
            $customer_id,
            $reservation_date,
            $reservation_time,
            $party_size,
            $status
        );

        $stmt->execute();

        $reservation_id = $con->insert_id;
        $stmt->close();

        // Assign dining table
        $stmt = $con->prepare(
            "INSERT INTO ReservationTable
             (reservation_id, table_id)
             VALUES (?, ?)"
        );

        $stmt->bind_param("ii", $reservation_id, $table_id);
        $stmt->execute();
        $stmt->close();

        $message = "Reservation created successfully!";
        $responseCode = 201;

    } else {

        // UPDATE RESERVATION
        $stmt = $con->prepare(
            "UPDATE Reservation
             SET customer_id = ?,
                 reservation_date = ?,
                 reservation_time = ?,
                 party_size = ?,
                 status = ?
             WHERE reservation_id = ?"
        );

        $stmt->bind_param(
            "issisi",
            $customer_id,
            $reservation_date,
            $reservation_time,
            $party_size,
            $status,
            $reservation_id
        );

        $stmt->execute();
        $stmt->close();

        // Remove previous dining table assignments
        $stmt = $con->prepare(
            "DELETE FROM ReservationTable
             WHERE reservation_id = ?"
        );

        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $stmt->close();

        // Assign selected dining table
        $stmt = $con->prepare(
            "INSERT INTO ReservationTable
             (reservation_id, table_id)
             VALUES (?, ?)"
        );

        $stmt->bind_param("ii", $reservation_id, $table_id);
        $stmt->execute();
        $stmt->close();

        $message = "Reservation updated successfully!";
        $responseCode = 200;
    }

    // Save changes
    $con->commit();
    $transactionStarted = false;

    http_response_code($responseCode);

    echo json_encode([
        "success" => true,
        "message" => $message,
        "reservation_id" => $reservation_id
    ]);

} catch (Throwable $e) {

    if ($transactionStarted) {
        $con->rollback();
    }

    $responseCode = $e->getCode() === 404 ? 404 : 400;

    http_response_code($responseCode);

    echo json_encode([
        "success" => false,
        "message" => $e instanceof mysqli_sql_exception
            ? "Unable to save reservation."
            : $e->getMessage()
    ]);

}

$con->close();

?>