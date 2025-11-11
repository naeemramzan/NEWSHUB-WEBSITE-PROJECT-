<?php
// FILE: /header.php

// ### STABLE FIX: Database connection is included and checked directly inside this file. ###
require_once 'includes/db.php';

// Check if the database connection was successful.
if (!isset($conn) || !$conn) {
    die("CRITICAL ERROR: Database connection failed. Please check your 'includes/db.php' file.");
}

// ### UPGRADED VISITOR TRACKING CODE v2 ###
if (isset($conn)) {
    $visitor_ip = $_SERVER['REMOTE_ADDR'];
    $current_date = date("Y-m-d");
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'; // Get User Agent

    // Check if this IP has already been counted today
    $stmt = $conn->prepare("SELECT id FROM visitors WHERE ip_address = ? AND visit_date = ?");
    $stmt->bind_param("ss", $visitor_ip, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // --- GeoIP Lookup ---
        $geo_data = @json_decode(file_get_contents("http://ip-api.com/json/{$visitor_ip}"));
        $country = ($geo_data && $geo_data->status == 'success') ? $geo_data->country : 'Unknown';
        $city = ($geo_data && $geo_data->status == 'success') ? $geo_data->city : 'Unknown';
        $isp = ($geo_data && $geo_data->status == 'success') ? $geo_data->isp : 'Unknown';
        
        // Insert the new visitor record with all details
        $insert_stmt = $conn->prepare("INSERT INTO visitors (ip_address, visit_date, country, city, isp, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssss", $visitor_ip, $current_date, $country, $city, $isp, $user_agent);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $stmt->close();
}

// --- The rest of the header continues ---
date_default_timezone_set('Asia/Karachi');
$nav_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$current_page = basename($_SERVER['PHP_SELF']);
$current_cat_id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3637721699586342"
     crossorigin="anonymous"></script>
     
     
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsHub</title>
    
    <!-- FAVICON -->
    
    <!-- FAVICON -->
    <link rel="icon" href="images/logo.png" type="image/png">  
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #fff; }
        .header-top { background-color: #222222; }
        .logo-main-text { font-size: 1.5rem; font-weight: 900; }
        .logo-accent-text { background-color: #e52d27; padding: 0 5px; }
        .main-nav a.active { border-bottom: 3px solid #e52d27; color: #e52d27; }
        .main-nav a:hover { color: #e52d27; }
        .section-title { font-size: 1.25rem; font-weight: 900; text-transform: uppercase; border-bottom: 3px solid #333; }
        .footer-main { background-color: #222; color: #aaa; }
        .footer-bottom { background: #111; color: #777; }
        .article-content p { margin-bottom: 1.5rem; line-height: 1.8; font-size: 1.1rem; color: #333; }
        .share-buttons a { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; color: white; margin-left: 8px; transition: opacity 0.2s; }
        .share-buttons a:hover { opacity: 0.8; }
        .bg-facebook { background-color: #3b5998; }
        .bg-twitter { background-color: #1da1f2; }
        .bg-whatsapp { background-color: #25d366; }
        .bg-email { background-color: #777; }
        .more-stories-card img { width: 100%; height: 150px; object-fit: cover; }
        .sidebar-widget { border: 1px solid #eee; padding: 15px; margin-bottom: 20px; }
        .comment-section { border-top: 1px solid #ddd; margin-top: 2rem; padding-top: 2rem; }
        .comment { border-bottom: 1px solid #eee; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
        .comment:last-child { border-bottom: none; margin-bottom: 0; }
        .comment-author { font-weight: 700; font-size: 1.1rem; }
        .comment-date { font-size: 0.8rem; color: #888; }
        .comment-body { margin-top: 0.5rem; line-height: 1.6; }
        .comment-form input[type="text"], .comment-form input[type="email"], .comment-form textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; margin-bottom: 1rem; }
        .comment-form button { background-color: #e52d27; color: white; font-weight: bold; padding: 0.75rem 1.5rem; border-radius: 0.25rem; transition: background-color 0.2s; }
        .comment-form button:hover { background-color: #c4241f; }
        .alert-box { padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.25rem; border: 1px solid transparent; }
        .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .alert-error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <header>
        <div class="header-top py-2">
            <div class="container mx-auto px-4 flex justify-between items-center">
                <a href="index.php"><span class="logo-main-text text-white">NEWS<span class="logo-accent-text">HUB</span></span></a>
            </div>
        </div>
        <nav class="main-nav bg-white border-b border-gray-200">
            <div class="container mx-auto px-4 flex justify-between items-center">
                <div class="hidden lg:flex items-center">
                    <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?> font-bold text-sm py-4 px-3">Home</a>
                    <?php
                    if ($nav_categories_result && $nav_categories_result->num_rows > 0) {
                        while ($cat = $nav_categories_result->fetch_assoc()) {
                            $is_active = ($current_page == 'category.php' && $current_cat_id == $cat['id']) ? 'active' : '';
                            echo '<a href="category.php?id=' . $cat['id'] . '" class="' . $is_active . ' font-bold text-sm py-4 px-3">' . htmlspecialchars($cat['name']) . '</a>';
                        }
                    }
                    ?>
                </div>
                <a href="#" class="hidden lg:block p-4"><i class="fas fa-search"></i></a>
                <div class="lg:hidden">
                    <button id="mobile-menu-button" class="text-gray-600 p-4 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                </div>
            </div>
        </nav>
    </header>
    <div id="mobile-menu-overlay" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50">
        <button id="mobile-menu-close" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
        <div class="w-full h-full flex flex-col justify-center items-center">
            <a href="index.php" class="text-white text-2xl font-bold py-3">Home</a>
            <?php
            if ($nav_categories_result) {
                 $nav_categories_result->data_seek(0);
                 while ($cat = $nav_categories_result->fetch_assoc()) {
                    echo '<a href="category.php?id=' . $cat['id'] . '" class="text-white text-2xl font-bold py-3">' . htmlspecialchars($cat['name']) . '</a>';
                }
            }
            ?>
        </div>
    </div>