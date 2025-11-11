<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- Data Validation ---
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    if (empty($name) || !$email || empty($subject) || empty($message)) {
        $_SESSION['form_message'] = "Error: Please fill out all fields correctly.";
        $_SESSION['form_message_type'] = "error";
        header('Location: feedback.php');
        exit;
    }

    // --- Insert into Database ---
    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if ($stmt->execute()) {
            $_SESSION['form_message'] = "Thank you! Your feedback has been sent successfully.";
            $_SESSION['form_message_type'] = "success";
        } else {
            $_SESSION['form_message'] = "Error: Could not send your message.";
            $_SESSION['form_message_type'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['form_message'] = "Error: Database preparation failed.";
        $_SESSION['form_message_type'] = "error";
    }

    $conn->close();
    header('Location: feedback.php');
    exit;

} else {
    header('Location: index.php');
    exit;
}
?>