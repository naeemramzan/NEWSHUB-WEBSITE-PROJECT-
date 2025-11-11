<?php
// FILE: admin/reset_password.php
// ---
// INSTRUCTIONS:
// 1. Create this new file and save it in your 'admin' folder.
// 2. Open your web browser and go to: http://localhost/newse/admin/reset_password.php
// 3. You should see a success message.
// 4. IMPORTANT: DELETE THIS FILE after you see the success message.
// 5. Try logging in again with username 'admin' and password 'admin'.
// ---

echo "<!DOCTYPE html><body style='font-family: sans-serif;'>";
echo "<h1>Admin Password Reset</h1>";

// Include the database connection
require_once '../includes/db.php';

// The password we want to set
$password_to_set = 'admin';

// Hash the password using PHP's default secure algorithm
$hashed_password = password_hash($password_to_set, PASSWORD_DEFAULT);

// The username of the admin to update
$admin_username = 'admin';

// Prepare an UPDATE statement to avoid SQL injection
$stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");

if ($stmt) {
    // Bind the new hashed password and the username to the statement
    $stmt->bind_param("ss", $hashed_password, $admin_username);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<p style='color: green;'>Password for user '{$admin_username}' has been updated successfully!</p>";
        echo "<p>The new hashed password is: <strong>{$hashed_password}</strong></p>";
        echo "<p>You can now log in.</p>";
        echo "<p style='color: red; font-weight: bold;'>Please delete this file (reset_password.php) now for security reasons.</p>";
    } else {
        echo "<p style='color: red;'>Error executing statement: " . $stmt->error . "</p>";
    }

    // Close the statement
    $stmt->close();
} else {
    echo "<p style='color: red;'>Error preparing statement: " . $conn->error . "</p>";
}

// Close the database connection
$conn->close();

echo "</body></html>";
?>
