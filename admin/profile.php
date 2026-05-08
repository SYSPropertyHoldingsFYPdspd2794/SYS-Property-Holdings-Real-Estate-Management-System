<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

// Since you are logged in as a CUSTOMER in the video, 
// this function will trigger, close the DB, and redirect you to login.php.
protect_admin_page('ADMIN', $conn); 

// The rest of this code will NEVER run for a customer
echo "Welcome, Admin!"; 
?>