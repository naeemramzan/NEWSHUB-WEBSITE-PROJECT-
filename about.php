<?php 
// Establish DB Connection and load the universal header
require_once 'includes/db.php';
require_once 'header.php'; 
?>
<head>
    <title>About Us - NewsHub</title>
</head>

<main class="container mx-auto my-8 px-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-extrabold my-4 leading-tight border-b pb-4">About NewsHub</h1>
        
        <div class="prose lg:prose-xl max-w-none mt-6 text-lg leading-relaxed">
            <p><strong>Welcome to NewsHub, your primary source for reliable, timely, and unbiased news from around the corner and around the world.</strong></p>
            
            <h2 class="font-bold text-2xl mt-8">Our Mission</h2>
            <p>In an age of information overload, our mission is to cut through the noise and deliver news that is factual, relevant, and easy to understand. We are committed to upholding the highest standards of journalistic integrity, ensuring that our readers have access to information they can trust. Our goal is to empower our audience with knowledge, fostering a more informed and engaged society.</p>

            <h2 class="font-bold text-2xl mt-8">Our Vision</h2>
            <p>We envision a world where every citizen has access to free and fair information, enabling them to make sound decisions about their lives, their communities, and their governments. NewsHub strives to be a leading digital news platform recognized for its quality, accuracy, and dedication to the public good.</p>

            <h2 class="font-bold text-2xl mt-8">Our Team</h2>
            <p>Our team is composed of passionate, experienced journalists, editors, and digital media professionals who are dedicated to the craft of storytelling. From our reporters in the field to our editors in the newsroom, every member of the NewsHub team works tirelessly to bring you the news that matters most.</p>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; // Includes the new footer ?>