<?php
// customer/submit_booking.php
session_start();

// Pre-condition: Check if customer session exists
if (!isset($_SESSION['customer_id'])) {
    // If not logged in, boot them to the login screen
    header("Location: ../login.php");
    exit();
}

// Include your global database connection file
require_once '../includes/db_connect.php'; 

// Check if form was submitted via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Map post fields directly to your exact database table columns
    $customer_id       = $_SESSION['customer_id']; // Sourced from active session
    $property_id       = intval($_POST['property_id']);
    $service_type      = trim($_POST['service_type']);
    $appointment_date  = trim($_POST['appointment_date']);
    $appointment_time  = trim($_POST['appointment_time']); // Matches your exact column name 'appointyment_time'
    
    // Ensure all required user inputs are present
    if (!empty($property_id) && !empty($appointment_date) && !empty($appointment_time)) {
        try {
            // SQL syntax formatted to fit your exact column array pattern
            // status is hardcoded to 'PENDING', staff entries remain NULL at creation stage
            $sql = "INSERT INTO appointments (
                        customer_id, 
                        property_id, 
                        assigned_staff_id, 
                        service_type, 
                        appointment_date, 
                        appointyment_time, 
                        status, 
                        staff_remarks
                    ) VALUES (
                        :customer_id, 
                        :property_id, 
                        NULL, 
                        :service_type, 
                        :appointment_date, 
                        :appointment_time, 
                        'PENDING', 
                        NULL
                    )";
            
            $stmt = $conn->prepare($sql);
            
            // Protect application layer parameters from SQL injections via prepared parameters
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);
            $stmt->bindParam(':service_type', $service_type);
            $stmt->bindParam(':appointment_date', $appointment_date);
            $stmt->bindParam(':appointment_time', $appointment_time);
            
            // Execute structural transaction
            if ($stmt->execute()) {
                // Expected Result Met: Redirect directly to customer booking history view
                header("Location: booking_history.php");
                exit();
            } else {
                echo "Error: Could not record your application submission layer request.";
            }
            
        } catch (PDOException $e) {
            echo "Database Transaction Exception Error: " . $e->getMessage();
        }
    } else {
        echo "Error: Mandatory validation inputs are missing.";
    }
} else {
    // Prevent direct url tracking access to processing file
    header("Location: booking_form.php");
    exit();
}
?>