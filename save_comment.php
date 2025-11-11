<?php
session_start();
require_once 'includes/db.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- Data Validation ---
    $article_id = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $comment_text = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING));

    // Redirect back to the article page if any validation fails
    $redirect_url = 'single_article.php?id=' . $article_id;

    if (!$article_id || empty($name) || !$email || empty($comment_text)) {
        $_SESSION['comment_message'] = "Error: Please fill out all fields correctly.";
        $_SESSION['comment_message_type'] = "error";
        header('Location: ' . $redirect_url);
        exit;
    }

    // --- Insert into Database ---
    // Comments are not approved by default (is_approved = 0)
    $sql = "INSERT INTO comments (article_id, name, email, comment, is_approved) VALUES (?, ?, ?, ?, 0)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("isss", $article_id, $name, $email, $comment_text);
        if ($stmt->execute()) {
            $_SESSION['comment_message'] = "Success! Your comment has been submitted and is awaiting approval.";
            $_SESSION['comment_message_type'] = "success";
        } else {
            $_SESSION['comment_message'] = "Error: Could not submit your comment.";
            $_SESSION['comment_message_type'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['comment_message'] = "Error: Database preparation failed.";
        $_SESSION['comment_message_type'] = "error";
    }

    $conn->close();

    // Redirect back to the article page
    header('Location: ' . $redirect_url);
    exit;

} else {
    // If not a POST request, redirect to homepage
    header('Location: index.php');
    exit;
}
?>