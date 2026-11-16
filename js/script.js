document.addEventListener("DOMContentLoaded", function () {

    const customerForm = document.getElementById("addCustomerForm");
    const customerMessage = document.getElementById("customerMessage");

    if (!customerForm) {
        return;
    }

    const customerId = document.getElementById("customer_id");
    const submitButton = document.getElementById("submitCustomerBtn");
    const cancelButton = document.getElementById("cancelEditBtn");

    const firstName = document.getElementById("first_name");
    const lastName = document.getElementById("last_name");
    const phone = document.getElementById("phone");
    const email = document.getElementById("email");

    // Reset form to Add Customer mode
    function resetForm() {
        customerForm.reset();
        customerId.value = "";
        submitButton.textContent = "Add Customer";
        cancelButton.hidden = true;
    }

    // Handle Edit button clicks
    document.querySelectorAll(".edit-btn").forEach(function (button) {

        button.addEventListener("click", function () {

            customerId.value = button.dataset.id;
            firstName.value = button.dataset.firstName;
            lastName.value = button.dataset.lastName;
            phone.value = button.dataset.phone;
            email.value = button.dataset.email;

            submitButton.textContent = "Update Customer";
            cancelButton.hidden = false;

            customerMessage.textContent = "";

            // Scroll to the form
            customerForm.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });

            firstName.focus();

        });

    });

    // Cancel editing
    cancelButton.addEventListener("click", function () {
        resetForm();
        customerMessage.textContent = "";
    });

    // Handle Add and Update
    customerForm.addEventListener("submit", async function (event) {

        event.preventDefault();

        const isEditing = customerId.value !== "";

        const formData = new FormData(customerForm);

        const method = isEditing ? "PUT" : "POST";

        submitButton.disabled = true;

        customerMessage.textContent = isEditing
            ? "Updating customer..."
            : "Adding customer...";

        customerMessage.style.color = "#475569";

        try {

            let options;

            if (isEditing) {

                // PHP reads URL-encoded data for PUT requests
                options = {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams(formData).toString()
                };

            } else {

                options = {
                    method: "POST",
                    body: formData
                };

            }

            const response = await fetch("api/customers.php", options);

            const result = await response.json();

            if (response.ok && result.success) {

                customerMessage.textContent = result.message;
                customerMessage.style.color = "green";

                resetForm();

                // Refresh to display updated records
                window.location.reload();

            } else {

                customerMessage.textContent =
                    result.message || "Unable to save customer.";

                customerMessage.style.color = "red";

            }

        } catch (error) {

            customerMessage.textContent =
                "An error occurred while saving customer.";

            customerMessage.style.color = "red";

            console.error(error);

        } finally {

            submitButton.disabled = false;

        }

    });

});

// Custom DELETE Customer Functionality

document.addEventListener("DOMContentLoaded", function () {

    const deleteButtons = document.querySelectorAll(".delete-btn");

    const deleteModal = document.getElementById("deleteModal");
    const deleteCustomerName = document.getElementById("deleteCustomerName");
    const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
    const deleteError = document.getElementById("deleteError");

    if (!deleteModal) {
        return;
    }

    let selectedCustomerId = null;
    let lastFocusedButton = null;

    function closeModal() {
        deleteModal.hidden = true;
        selectedCustomerId = null;
        deleteError.textContent = "";

        if (lastFocusedButton) {
            lastFocusedButton.focus();
        }
    }

    // Open confirmation popup
    deleteButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            selectedCustomerId = button.dataset.id;
            lastFocusedButton = button;

            deleteCustomerName.textContent = button.dataset.name;
            deleteError.textContent = "";

            deleteModal.hidden = false;
            cancelDeleteBtn.focus();

        });

    });

    // Cancel deletion
    cancelDeleteBtn.addEventListener("click", closeModal);

    // Close when clicking outside the popup
    deleteModal.addEventListener("click", function (event) {
        if (event.target === deleteModal && !confirmDeleteBtn.disabled) {
            closeModal();
        }
    });

    // Close with Escape key
    document.addEventListener("keydown", function (event) {
        if (
            event.key === "Escape" &&
            !deleteModal.hidden &&
            !confirmDeleteBtn.disabled
        ) {
            closeModal();
        }
    });

    // Confirm deletion
    confirmDeleteBtn.addEventListener("click", async function () {

        if (!selectedCustomerId) {
            return;
        }

        confirmDeleteBtn.disabled = true;
        confirmDeleteBtn.textContent = "Deleting...";
        cancelDeleteBtn.disabled = true;

        try {

            const response = await fetch("api/customers.php", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    customer_id: selectedCustomerId
                }).toString()
            });

            const result = await response.json();

            if (response.ok && result.success) {

                // Refresh automatically without a second popup
                window.location.reload();

            } else {

                deleteError.textContent =
                    result.message || "Unable to delete customer.";

            }

        } catch (error) {

            deleteError.textContent =
                "An error occurred while deleting the customer.";

            console.error(error);

        } finally {

            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.textContent = "Delete Customer";
            cancelDeleteBtn.disabled = false;

        }

    });

});

// ==========================================
// ADD AND UPDATE RESERVATION FUNCTIONALITY
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    const reservationForm = document.getElementById("addReservationForm");

    if (!reservationForm) {
        return;
    }

    const reservationMessage = document.getElementById("reservationMessage");

    const reservationId = document.getElementById("reservation_id");
    const customer = document.getElementById("reservation_customer");
    const date = document.getElementById("reservation_date");
    const time = document.getElementById("reservation_time");
    const partySize = document.getElementById("party_size");
    const table = document.getElementById("reservation_table");
    const status = document.getElementById("reservation_status");

    const submitButton = document.getElementById("submitReservationBtn");
    const cancelButton = document.getElementById("cancelReservationEditBtn");

    const originalMinDate = date.min;

    // Reset form to Add Reservation mode
    function resetReservationForm() {

        reservationForm.reset();
        reservationId.value = "";

        submitButton.textContent = "Add Reservation";
        cancelButton.hidden = true;

        date.min = originalMinDate;

        status.querySelector('option[value="Cancelled"]').disabled = true;
        status.querySelector('option[value="Completed"]').disabled = true;
    }

    resetReservationForm();

    // Handle Edit button clicks
    document.querySelectorAll(".reservation-edit-btn").forEach(function (button) {

        button.addEventListener("click", function () {

            reservationId.value = button.dataset.id;
            customer.value = button.dataset.customerId;
            date.value = button.dataset.date;
            time.value = button.dataset.time;
            partySize.value = button.dataset.partySize;
            table.value = button.dataset.tableId;

            // Enable all statuses for editing
            status.querySelectorAll("option").forEach(function (option) {
                option.disabled = false;
            });

            status.value = button.dataset.status;

            // Allow past dates when editing
            date.removeAttribute("min");

            submitButton.textContent = "Update Reservation";
            cancelButton.hidden = false;

            reservationMessage.textContent = "";

            reservationForm.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });

            customer.focus();

        });

    });

    // Cancel editing
    cancelButton.addEventListener("click", function () {

        resetReservationForm();
        reservationMessage.textContent = "";

    });

    // Handle Add and Update
    reservationForm.addEventListener("submit", async function (event) {

        event.preventDefault();

        const isEditing = reservationId.value !== "";
        const formData = new FormData(reservationForm);

        submitButton.disabled = true;

        submitButton.textContent = isEditing
            ? "Updating Reservation..."
            : "Adding Reservation...";

        reservationMessage.textContent = isEditing
            ? "Updating reservation..."
            : "Saving reservation...";

        reservationMessage.style.color = "#475569";

        try {

            let options;

            if (isEditing) {

                options = {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams(formData).toString()
                };

            } else {

                options = {
                    method: "POST",
                    body: formData
                };

            }

            const response = await fetch("api/reservations.php", options);

            const result = await response.json();

            if (response.ok && result.success) {

                reservationMessage.textContent = result.message;
                reservationMessage.style.color = "green";

                resetReservationForm();

                window.location.reload();

            } else {

                reservationMessage.textContent =
                    result.message || "Unable to save reservation.";

                reservationMessage.style.color = "red";

            }

        } catch (error) {

            reservationMessage.textContent =
                "An error occurred while saving the reservation.";

            reservationMessage.style.color = "red";

            console.error(error);

        } finally {

            submitButton.disabled = false;

            submitButton.textContent =
                reservationId.value !== ""
                    ? "Update Reservation"
                    : "Add Reservation";

        }

    });

});

// ==========================================
// DELETE RESERVATION FUNCTIONALITY
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    const deleteButtons = document.querySelectorAll(
        ".reservation-delete-btn"
    );

    const deleteModal = document.getElementById(
        "deleteReservationModal"
    );

    // Run only on the Reservation Management page
    if (!deleteModal) {
        return;
    }

    const deleteReservationName = document.getElementById(
        "deleteReservationName"
    );

    const cancelButton = document.getElementById(
        "cancelReservationDeleteBtn"
    );

    const confirmButton = document.getElementById(
        "confirmReservationDeleteBtn"
    );

    const deleteError = document.getElementById(
        "deleteReservationError"
    );

    let selectedReservationId = null;
    let lastFocusedButton = null;

    // Close confirmation popup
    function closeModal() {

        deleteModal.hidden = true;
        selectedReservationId = null;
        deleteError.textContent = "";

        if (lastFocusedButton) {
            lastFocusedButton.focus();
        }
    }

    // Open confirmation popup
    deleteButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            selectedReservationId = button.dataset.id;
            lastFocusedButton = button;

            deleteReservationName.textContent = button.dataset.name;
            deleteError.textContent = "";

            deleteModal.hidden = false;
            cancelButton.focus();

        });

    });

    // Cancel deletion
    cancelButton.addEventListener("click", closeModal);

    // Close popup when clicking outside
    deleteModal.addEventListener("click", function (event) {

        if (
            event.target === deleteModal &&
            !confirmButton.disabled
        ) {
            closeModal();
        }

    });

    // Close popup using Escape key
    document.addEventListener("keydown", function (event) {

        if (
            event.key === "Escape" &&
            !deleteModal.hidden &&
            !confirmButton.disabled
        ) {
            closeModal();
        }

    });

    // Confirm reservation deletion
    confirmButton.addEventListener("click", async function () {

        if (!selectedReservationId) {
            return;
        }

        confirmButton.disabled = true;
        cancelButton.disabled = true;

        confirmButton.textContent = "Deleting...";

        try {

            const response = await fetch("api/reservations.php", {

                method: "DELETE",

                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },

                body: new URLSearchParams({
                    reservation_id: selectedReservationId
                }).toString()

            });

            const result = await response.json();

            if (response.ok && result.success) {

                // Refresh without showing another popup
                window.location.reload();

            } else {

                deleteError.textContent =
                    result.message || "Unable to delete reservation.";

            }

        } catch (error) {

            deleteError.textContent =
                "An error occurred while deleting the reservation.";

            console.error(error);

        } finally {

            confirmButton.disabled = false;
            cancelButton.disabled = false;

            confirmButton.textContent = "Delete Reservation";

        }

    });

});

// ==========================================
// ADD AND UPDATE FOOD ORDER FUNCTIONALITY
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    const orderForm = document.getElementById("addOrderForm");

    // Only run on the Food Order Management page
    if (!orderForm) {
        return;
    }

    const orderMessage = document.getElementById("orderMessage");

    const orderId = document.getElementById("order_id");
    const reservation = document.getElementById("order_reservation");
    const server = document.getElementById("order_server");
    const chef = document.getElementById("order_chef");
    const totalAmount = document.getElementById("total_amount");
    const paymentMethod = document.getElementById("payment_method");
    const orderStatus = document.getElementById("order_status");

    const submitButton = document.getElementById("submitOrderBtn");
    const cancelButton = document.getElementById("cancelOrderEditBtn");

    // Reset form to Add Food Order mode
    function resetOrderForm() {

        orderForm.reset();

        orderId.value = "";

        submitButton.textContent = "Add Food Order";

        cancelButton.hidden = true;

    }

    // Handle Edit button clicks
    document.querySelectorAll(".order-edit-btn").forEach(function (button) {

        button.addEventListener("click", function () {

            orderId.value = button.dataset.id;

            reservation.value = button.dataset.reservationId;
            server.value = button.dataset.serverId;
            chef.value = button.dataset.chefId;

            totalAmount.value = button.dataset.total;
            paymentMethod.value = button.dataset.payment;
            orderStatus.value = button.dataset.status;

            submitButton.textContent = "Update Food Order";

            cancelButton.hidden = false;

            orderMessage.textContent = "";

            // Scroll to the form
            orderForm.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });

            reservation.focus();

        });

    });

    // Cancel editing
    cancelButton.addEventListener("click", function () {

        resetOrderForm();

        orderMessage.textContent = "";

    });

    // Handle Add and Update
    orderForm.addEventListener("submit", async function (event) {

        event.preventDefault();

        const isEditing = orderId.value !== "";

        const formData = new FormData(orderForm);

        submitButton.disabled = true;

        submitButton.textContent = isEditing
            ? "Updating Food Order..."
            : "Adding Food Order...";

        orderMessage.textContent = isEditing
            ? "Updating food order..."
            : "Saving food order...";

        orderMessage.style.color = "#475569";

        try {

            let options;

            if (isEditing) {

                // Send PUT request for updating an order
                options = {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams(formData).toString()
                };

            } else {

                // Send POST request for adding an order
                options = {
                    method: "POST",
                    body: formData
                };

            }

            const response = await fetch("api/orders.php", options);

            const result = await response.json();

            if (response.ok && result.success) {

                orderMessage.textContent = result.message;
                orderMessage.style.color = "green";

                resetOrderForm();

                // Refresh page to display updated records
                window.location.reload();

            } else {

                orderMessage.textContent =
                    result.message || "Unable to save food order.";

                orderMessage.style.color = "red";

            }

        } catch (error) {

            orderMessage.textContent =
                "An error occurred while saving the food order.";

            orderMessage.style.color = "red";

            console.error(error);

        } finally {

            submitButton.disabled = false;

            submitButton.textContent =
                orderId.value !== ""
                    ? "Update Food Order"
                    : "Add Food Order";

        }

    });

});

// ==========================================
// DELETE FOOD ORDER FUNCTIONALITY
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    const deleteModal = document.getElementById("deleteOrderModal");

    // Only run on the Food Order Management page
    if (!deleteModal) {
        return;
    }

    const deleteButtons = document.querySelectorAll(".order-delete-btn");

    const deleteOrderNumber = document.getElementById("deleteOrderNumber");
    const deleteOrderName = document.getElementById("deleteOrderName");
    const deleteError = document.getElementById("deleteOrderError");

    const cancelButton = document.getElementById("cancelOrderDeleteBtn");
    const confirmButton = document.getElementById("confirmOrderDeleteBtn");

    let selectedOrderId = null;
    let lastFocusedButton = null;

    // Close confirmation popup
    function closeModal() {

        deleteModal.hidden = true;
        selectedOrderId = null;
        deleteError.textContent = "";

        if (lastFocusedButton) {
            lastFocusedButton.focus();
        }
    }

    // Open confirmation popup
    deleteButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            selectedOrderId = button.dataset.id;
            lastFocusedButton = button;

            deleteOrderNumber.textContent = "#" + selectedOrderId;
            deleteOrderName.textContent = button.dataset.name;

            deleteError.textContent = "";

            deleteModal.hidden = false;
            cancelButton.focus();

        });

    });

    // Cancel deletion
    cancelButton.addEventListener("click", function () {

        if (!confirmButton.disabled) {
            closeModal();
        }

    });

    // Close popup when clicking outside
    deleteModal.addEventListener("click", function (event) {

        if (
            event.target === deleteModal &&
            !confirmButton.disabled
        ) {
            closeModal();
        }

    });

    // Close popup using Escape
    document.addEventListener("keydown", function (event) {

        if (
            event.key === "Escape" &&
            !deleteModal.hidden &&
            !confirmButton.disabled
        ) {
            closeModal();
        }

    });

    // Confirm food order deletion
    confirmButton.addEventListener("click", async function () {

        if (!selectedOrderId) {
            return;
        }

        confirmButton.disabled = true;
        cancelButton.disabled = true;

        confirmButton.textContent = "Deleting...";

        try {

            const response = await fetch("api/orders.php", {

                method: "DELETE",

                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },

                body: new URLSearchParams({
                    order_id: selectedOrderId
                }).toString()

            });

            const result = await response.json();

            if (response.ok && result.success) {

                // Refresh automatically without a second popup
                window.location.reload();

            } else {

                deleteError.textContent =
                    result.message || "Unable to delete food order.";

            }

        } catch (error) {

            deleteError.textContent =
                "An error occurred while deleting the food order.";

            console.error(error);

        } finally {

            confirmButton.disabled = false;
            cancelButton.disabled = false;

            confirmButton.textContent = "Delete Food Order";

        }

    });

});