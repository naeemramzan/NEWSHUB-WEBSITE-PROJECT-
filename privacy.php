<?php
// FILE: privacy.php
// This is a static page for the Privacy Policy.

// --- 1. SETUP & DATABASE CONNECTION ---
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - NewsHub</title>
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
        /* Styling for the content */
        .content-area h2 { font-size: 1.5rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; color: #ef4444; }
        .content-area p, .content-area ul { margin-bottom: 1.5rem; line-height: 1.8; color: #d1d5db; }
        .content-area ul { list-style-position: inside; list-style-type: disc; }
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
                        $nav_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 9");
                        if ($nav_categories_result && $nav_categories_result->num_rows > 0) {
                            while ($cat = $nav_categories_result->fetch_assoc()) {
                                $link_href = ($cat['name'] == 'Blogs') ? 'blogs.php' : 'category.php?id=' . $cat['id'];
                                echo '<a href="' . htmlspecialchars($link_href) . '" class="nav-link">' . strtoupper(htmlspecialchars($cat['name'])) . '</a>';
                            }
                        }
                    ?>
                    <a href="privacy.php" class="nav-link active">PRIVACY POLICY</a> </nav>
                 <div class="flex items-center">
                    <button class="text-white hover:text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <main class="max-w-screen-md mx-auto px-4 py-8">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg content-area">
            <h1 class="text-4xl font-extrabold text-white mb-6">Privacy Policy</h1>
            <p><strong>Last updated: <?php echo date("F j, Y"); ?></strong></p>

            <p>NewsHub ("us", "we", or "our") operates the NewsHub website (the "Service"). This page informs you of our policies regarding the collection, use, and disclosure of personal data when you use our Service and the choices you have associated with that data.</p>

            <h2>Information Collection and Use</h2>
            <p>We collect several different types of information for various purposes to provide and improve our Service to you.</p>
            <ul>
                <li><strong>Newsletter Subscription:</strong> When you subscribe to our newsletter, we collect your email address to send you news and updates.</li>
                <li><strong>Comments:</strong> When you leave a comment, we collect your name and the content of your comment.</li>
                <li><strong>Usage Data:</strong> We may also collect information on how the Service is accessed and used ("Usage Data"). This Usage Data may include information such as your computer's Internet Protocol address (e.g. IP address), browser type, browser version, the pages of our Service that you visit, the time and date of your visit, the time spent on those pages, unique device identifiers and other diagnostic data.</li>
            </ul>

            <h2>Use of Data</h2>
            <p>NewsHub uses the collected data for various purposes:</p>
            <ul>
                <li>To provide and maintain the Service</li>
                <li>To notify you about changes to our Service</li>
                <li>To allow you to participate in interactive features of our Service when you choose to do so</li>
                <li>To provide customer care and support</li>
                <li>To monitor the usage of the Service</li>
                <li>To detect, prevent and address technical issues</li>
            </ul>

            <h2>Security Of Data</h2>
            <p>The security of your data is important to us, but remember that no method of transmission over the Internet, or method of electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your Personal Data, we cannot guarantee its absolute security.</p>

            <h2>Changes To This Privacy Policy</h2>
            <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page. We will let you know via email and/or a prominent notice on our Service, prior to the change becoming effective and update the "last updated" date at the top of this Privacy Policy. You are advised to review this Privacy Policy periodically for any changes.</p>
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
