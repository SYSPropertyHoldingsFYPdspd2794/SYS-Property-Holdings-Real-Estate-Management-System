<?php
// 1. Include requirements
include 'db_connect.php';
include 'functions.php';

// This checks if an ID was sent. If not, it shows the error you saw.
if (isset($_REQUEST['account_id'])) {
    $acc_id = intval($_REQUEST['account_id']);
    
    // Generate the plain-text string
    $temp_password = generateDefaultTempPassword(8); 

    // HASH THE STRING (Task #9 Security Requirement)
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

    // Update the password_hash column in the accounts table
    $sql = "UPDATE accounts SET password_hash = ? WHERE account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_password, $acc_id);

    if ($stmt->execute()) {
        // Success: This is what you will see when you provide an ID
        ?>
        <div style="padding: 20px; border: 2px solid #28a745; background: #e9f7ef; font-family: sans-serif; border-radius: 8px; max-width: 500px; margin: 20px auto; text-align: center;">
            <h3 style="color: #28a745;">Success!</h3>
            <p>New temporary password for Account ID #<?php echo $acc_id; ?>:</p>
            <div style="font-size: 24px; font-weight: bold; background: #fff; padding: 10px; border: 1px solid #ccc; display: inline-block; margin-bottom: 10px;">
                <?php echo $temp_password; ?>
            </div>
            <p style="font-size: 0.8em; color: #666;">Provide this to the user for their next login.</p>
        </div>
        <?php
    }
    $stmt->close();
} else {
    // This is the message you see in Screenshot (328).png
    echo "Error: No account ID provided for reset.";
}
?>