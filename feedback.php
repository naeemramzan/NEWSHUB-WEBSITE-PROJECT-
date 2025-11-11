<?php 
session_start();
require_once 'includes/db.php';
require_once 'header.php'; 

// Get (and then clear) any message from the session after a form submission
$form_message = $_SESSION['form_message'] ?? null;
$form_message_type = $_SESSION['form_message_type'] ?? null;
unset($_SESSION['form_message'], $_SESSION['form_message_type']);
?>
<head>
    <title>Feedback - NewsHub</title>
</head>

<main class="container mx-auto my-8 px-4">
    <div class="max-w-2xl mx-auto bg-white p-8 border rounded-lg shadow-lg">
        <h1 class="text-3xl font-extrabold mb-2">Send Us Your Feedback</h1>
        <p class="text-gray-600 mb-6">We'd love to hear from you! Please fill out the form below to get in touch.</p>

        <?php if ($form_message): ?>
            <div class="alert-box <?php echo $form_message_type === 'success' ? 'alert-success' : 'alert-error'; ?> mb-6">
                <?php echo htmlspecialchars($form_message); ?>
            </div>
        <?php endif; ?>
        
        <form action="save_feedback.php" method="POST" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                <input type="text" name="name" id="name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Your Email</label>
                <input type="email" name="email" id="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
            </div>
            
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                <input type="text" name="subject" id="subject" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" id="message" rows="6" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"></textarea>
            </div>

            <div>
                <button type="submit" class="w-full bg-red-600 text-white font-bold py-3 px-4 rounded-md hover:bg-red-700 transition-colors">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</main>

<?php require_once 'footer.php'; ?>