<?php
// FILE: contact.php
// This page now saves contact form submissions to the database.

// --- 1. SETUP & DATABASE CONNECTION ---
require_once 'includes/db.php';

// Initialize a variable to store messages, either from a new submission or from a session after redirect.
$message_status = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    // Sanitize and validate inputs
    $name = trim(htmlspecialchars($_POST['name']));
    $email = trim(htmlspecialchars($_POST['email']));
    $subject = trim(htmlspecialchars($_POST['subject']));
    $message_content = trim(htmlspecialchars($_POST['message']));

    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($subject) && !empty($message_content)) {

        // --- Save Message to Database ---
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message_content);

        if ($stmt->execute()) {
            $_SESSION['contact_form_message'] = '<div class="bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-6" role="alert">Thank you! Your message has been received.</div>';
        } else {
            $_SESSION['contact_form_message'] = '<div class="bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-6" role="alert">Sorry, there was an error submitting your message. Please try again later.</div>';
        }
        $stmt->close();
    } else {
        $_SESSION['contact_form_message'] = '<div class="bg-yellow-800 border border-yellow-600 text-yellow-200 px-4 py-3 rounded relative mb-6" role="alert">Please fill out all fields with valid information.</div>';
    }
    // Redirect to self to prevent form resubmission and display message via session.
    header('Location: contact.php');
    exit;
}

// Retrieve and clear message from session after redirect.
if (isset($_SESSION['contact_form_message'])) {
    $message_status = $_SESSION['contact_form_message'];
    unset($_SESSION['contact_form_message']); // Clear the message after displaying it.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3637721699586342"
     crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - NewsHub</title>
    
    
    <!-- FAVICON -->
    <link rel="icon" href="img/logo/logo1.png" type="image/png"> 
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #111827; color: #f9fafb; }
        .nav-link {
            padding: 0.5rem 1rem;
            color: #d1d5db;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: #ffffff;
            border-bottom-color: #ef4444; /* red-500 */
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">

    <header class="bg-black shadow-lg sticky top-0 z-50">
        <div class="max-w-screen-xl mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <a href="index.php" class="text-2xl font-extrabold border-2 border-white px-2 py-1">NEWS<span class="bg-red-600 text-white px-1">HUB</span></a>
                <nav class="hidden lg:flex items-center space-x-2">
                    <a href="index.php" class="nav-link">HOME</a>
                    <?php
                        $nav_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 8");
                        if ($nav_categories_result && $nav_categories_result->num_rows > 0) {
                            while ($cat = $nav_categories_result->fetch_assoc()) {
                                $link_href = ($cat['name'] == 'Blogs') ? 'blogs.php' : 'category.php?id=' . $cat['id'];
                                echo '<a href="' . htmlspecialchars($link_href) . '" class="nav-link">' . strtoupper(htmlspecialchars($cat['name'])) . '</a>';
                            }
                        }
                    ?>
                     <a href="contact.php" class="nav-link active">CONTACT</a>
                </nav>
                 <div class="flex items-center">
                    <button class="text-white hover:text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <main class="max-w-screen-md mx-auto px-4 py-8">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg">
            <h1 class="text-4xl font-extrabold text-white mb-6">Contact Us</h1>
            <p class="text-gray-400 mb-8">We'd love to hear from you. Please fill out the form below to get in touch with our team.</p>

            <?php echo $message_status; ?>

            <form action="contact.php" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label for="name" class="block text-gray-300 font-semibold mb-2">Your Name</label>
                        <input type="text" name="name" id="name" required class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 text-white">
                    </div>
                     <div>
                        <label for="email" class="block text-gray-300 font-semibold mb-2">Your Email</label>
                        <input type="email" name="email" id="email" required class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 text-white">
                    </div>
                </div>
                <div class="mb-4">
                     <label for="subject" class="block text-gray-300 font-semibold mb-2">Subject</label>
                     <input type="text" name="subject" id="subject" required class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 text-white">
                </div>
                <div class="mb-6">
                     <label for="message" class="block text-gray-300 font-semibold mb-2">Message</label>
                     <textarea name="message" id="message" rows="6" required class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-red-500 text-white"></textarea>
                </div>
                <div class="text-right">
                    <button type="submit" name="send_message" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-md transition-colors">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-black mt-12 border-t border-gray-800">
        <div class="max-w-screen-xl mx-auto py-8 px-4 text-center">
             <a href="index.php" class="text-2xl font-extrabold border-2 border-white px-2 py-1 inline-block">NEWS<span class="bg-red-600 text-white px-1">HUB</span></a>
             <p class="text-gray-400 text-sm mt-4">© <?php echo date('Y'); ?> NewsHub. All Rights Reserved.</p>
        </div>
    </footer>
    </body>
</html>
