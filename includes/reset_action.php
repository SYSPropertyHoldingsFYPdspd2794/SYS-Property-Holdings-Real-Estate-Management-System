<?php
// 1. Include requirements
include 'db_connect.php';
include 'functions.php';

// Check if an account ID was provided (Pre-condition: Admin holds an active session)
if (isset($_REQUEST['account_id'])) {
    $acc_id = intval($_REQUEST['account_id']);
    
    // STEP 1: Generate the plain-text string (US10 Step)
    $temp_password = generateDefaultTempPassword(8); 

    // STEP 2: HASH THE TEMPORARY PASSWORD STRING (Current Task)
    // Satisfies Task #9 and US10 Expected Result
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

    // STEP 3: Execute the UPDATE connection to the accounts table
    $sql = "UPDATE accounts SET password_hash = ? WHERE account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_password, $acc_id);

    if ($stmt->execute()) {
        // STEP 4: UI renders the temporary password string on the screen (US10 Expected Result)
        ?>
        <div style="padding: 20px; border: 2px solid #28a745; background: #e9f7ef; font-family: sans-serif; border-radius: 8px; max-width: 500px; margin: 20px auto; text-align: center;">
            <h3 style="color: #28a745;">Success!</h3>
            <p>New temporary password for Account ID #<?php echo $acc_id; ?>:</p>
            <div style="font-size: 24px; font-weight: bold; background: #fff; padding: 10px; border: 1px solid #ccc; display: inline-block; margin-bottom: 10px;">
                <?php echo $temp_password; ?>
            </div>
            <p style="font-size: 0.8em; color: #666;">Provide this to the staff member for their next login.</p>
        </div>
        <?php
    }
    $stmt->close();
} else {
    // Error handling if no ID is passed to the script
    echo "Error: No account ID provided for reset.";
}
?>