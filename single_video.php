<?php
// FILE: single_video.php (FIXED Undefined variables for section data, consistent styling, responsiveness)
// This file displays a single video based on its ID.

// --- 1. SETUP & DATABASE CONNECTION ---
require_once 'includes/db.php';

// Helper function to extract YouTube Video ID from URL.
function getYouTubeVideoId($input) {
    if (empty($input)) {
        return null;
    }
    // Check if it's a full embed code string
    if (strpos($input, 'iframe') !== false) {
        preg_match('/src="[^"]*youtube\.com\/(?:embed\/|v\/|watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})[^"]*"/', $input, $matches);
        return $matches[1] ?? null;
    }
    // Check if it's a raw YouTube URL
    preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $input, $matches);
    return $matches[1] ?? null;
}

// --- 2. GET VIDEO ID FROM URL ---
$video_id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Get the video ID and ensure it's an integer

// Redirect if no valid ID is provided
if ($video_id <= 0) { // Check if it's 0 or less after intval
    header("Location: index.php"); // Redirect to homepage or a general videos page
    exit();
}

// --- 3. FETCH VIDEO DATA ---
// Select all columns from videos table for the main video, and primary category if available
$video_query = $conn->prepare("SELECT v.*, vc.name as category_name FROM videos v LEFT JOIN video_category_map vcm ON v.id = vcm.video_id LEFT JOIN video_categories vc ON vcm.category_id = vc.id WHERE v.id = ? LIMIT 1");
$video_query->bind_param("i", $video_id);
$video_query->execute();
$video_result = $video_query->get_result();
$video = $video_result ? $video_result->fetch_assoc() : null; // Safe fetch

// Redirect if video not found
if (!$video) {
    header("Location: index.php"); // Or to a custom 404 page
    exit();
}

// Increment view count for this video.
$conn->query("UPDATE videos SET view_count = view_count + 1 WHERE id = " . $video_id);


// --- Logic for "More Videos" (Related Videos) ---
$more_videos_limit = 3;
$related_videos_query_parts = [];
$related_video_ids_fetched = [$video_id];

// Fetch categories associated with the current video
$current_video_categories = [];
$cat_map_res = $conn->query("SELECT category_id FROM video_category_map WHERE video_id = {$video_id}");
if ($cat_map_res) {
    while($row = $cat_map_res->fetch_assoc()) {
        $current_video_categories[] = $row['category_id'];
    }
}

// 1. Videos by Shared Categories (most relevant)
if (!empty($current_video_categories)) {
    $category_ids_string = implode(',', $current_video_categories);
    $related_videos_query_parts[] = "
        SELECT v.id, v.title, v.image, v.youtube_embed_code, v.created_at
        FROM videos v
        JOIN video_category_map vcm ON v.id = vcm.video_id
        WHERE vcm.category_id IN ({$category_ids_string}) AND v.id != {$video_id}
        GROUP BY v.id
        ORDER BY COUNT(vcm.category_id) DESC, v.created_at DESC
        LIMIT {$more_videos_limit}
    ";
}

// 2. Fallback: Most viewed videos
$related_videos_query_parts[] = "
    SELECT v.id, v.title, v.image, v.youtube_embed_code, v.created_at
    FROM videos v
    WHERE v.id != {$video_id}
    ORDER BY v.view_count DESC, v.created_at DESC
    LIMIT {$more_videos_limit}
";

$more_videos_result_final = []; // Initialize to empty array
foreach ($related_videos_query_parts as $query_str) {
    $current_results = $conn->query($query_str);
    if ($current_results) {
        while ($row = $current_results->fetch_assoc()) {
            if (!in_array($row['id'], $related_video_ids_fetched)) {
                $more_videos_result_final[$row['id']] = $row; // Use ID as key to prevent duplicates
                $related_video_ids_fetched[] = $row['id'];
                if (count($more_videos_result_final) >= $more_videos_limit) {
                    break 2;
                }
            }
        }
    }
}
$more_videos_result_final = array_values($more_videos_result_final); // Convert associative array back to indexed


// Retrieve and clear subscription message from session (for footer and popup)
$subscription_status_type = $_SESSION['subscription_status_type'] ?? '';
$subscription_status_message = $_SESSION['subscription_status_message'] ?? '';
if (isset($_SESSION['subscription_status_type'])) {
    unset($_SESSION['subscription_status_type']);
    unset($_SESSION['subscription_status_message']);
}

// --- Data for General Sections (to avoid Undefined variable warnings in common header/footer) ---
// Initialize general data arrays for safety
$nav_categories_result_general_data = [];
$footer_categories_result_general_data = [];
$business_articles_data = [];
$world_articles_data = [];
$sports_articles_data = [];
$blogs_articles_data = [];
$international_articles_data = [];
$headlines_data = [];

// Fetch main nav categories (needed for header)
$nav_categories_result_general = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 8");
if ($nav_categories_result_general) {
    while($row = $nav_categories_result_general->fetch_assoc()) { $nav_categories_result_general_data[] = $row; }
}
// Fetch footer categories (needed for footer)
$footer_categories_result_general = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 4");
if ($footer_categories_result_general) {
    while($row = $footer_categories_result_general->fetch_assoc()) { $footer_categories_result_general_data[] = $row; }
}

// Fetch data for the footer sections (these were causing undefined variable errors)
$business_articles_res = $conn->query("
    SELECT a.id, a.title, a.image, a.created_at, ad.full_name as author_name
    FROM articles a LEFT JOIN admins ad ON a.admin_id = ad.id
    WHERE a.category_id = (SELECT id FROM categories WHERE name = 'Business' LIMIT 1)
    ORDER BY a.created_at DESC LIMIT 4
");
if ($business_articles_res) { while($row = $business_articles_res->fetch_assoc()) { $business_articles_data[] = $row; }}

$world_articles_res = $conn->query("
    SELECT a.id, a.title, a.image, a.created_at, ad.full_name as author_name
    FROM articles a LEFT JOIN admins ad ON a.admin_id = ad.id
    WHERE a.category_id = (SELECT id FROM categories WHERE name = 'World' LIMIT 1)
    ORDER BY a.created_at DESC LIMIT 4
");
if ($world_articles_res) { while($row = $world_articles_res->fetch_assoc()) { $world_articles_data[] = $row; }}

$sports_articles_res = $conn->query("
    SELECT a.id, a.title, a.image, a.created_at, ad.full_name as author_name
    FROM articles a LEFT JOIN admins ad ON a.admin_id = ad.id
    WHERE a.category_id = (SELECT id FROM categories WHERE name = 'Sports' LIMIT 1)
    ORDER BY a.created_at DESC LIMIT 4
");
if ($sports_articles_res) { while($row = $sports_articles_res->fetch_assoc()) { $sports_articles_data[] = $row; }}

$blogs_articles_res = $conn->query("
    SELECT a.id, a.title, a.image, a.content, a.created_at, ad.full_name as author_name
    FROM articles a LEFT JOIN admins ad ON a.admin_id = ad.id
    WHERE a.category_id = (SELECT id FROM categories WHERE name = 'Blogs' LIMIT 1)
    ORDER BY a.created_at DESC LIMIT 4
");
if ($blogs_articles_res) { while($row = $blogs_articles_res->fetch_assoc()) { $blogs_articles_data[] = $row; }}

$international_articles_all_res = $conn->query("
    SELECT a.id, a.title, a.image, a.created_at, ad.full_name as author_name
    FROM articles a LEFT JOIN admins ad ON a.admin_id = ad.id
    WHERE a.category_id = (SELECT id FROM categories WHERE name = 'International' LIMIT 1)
    ORDER BY a.created_at DESC LIMIT 7
");
if ($international_articles_all_res) { while($row = $international_articles_all_res->fetch_assoc()) { $international_articles_data[] = $row; }}

$headlines_result_res = $conn->query("
    SELECT a.id, a.title, a.image, a.created_at, ad.full_name as author_name
    FROM articles a LEFT JOIN admins ad ON a.admin_id = ad.id
    ORDER BY a.created_at DESC LIMIT 4
");
if ($headlines_result_res) { while($row = $headlines_result_res->fetch_assoc()) { $headlines_data[] = $row; }}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['title']); ?> - NewsHub Videos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #333; } /* Body background set to white */

        /* NewsHub Specific Styles (replicated for consistency) */
        .newshub-bg-dark { background-color: #1a1a1a; }
        .newshub-red-accent { background-color: #ef4444; }
        .newshub-red-text { color: #ef4444; }
        .newshub-border-red { border-bottom-color: #ef4444; }

        .header-top-bar {
            background-color: #0d121c;
            color: #a0a0a0;
            font-size: 0.75rem;
            padding: 0.25rem 0;
        }
        .header-top-bar a {
            color: #a0a0a0;
            padding: 0 0.5rem;
            border-right: 1px solid #333;
        }
        .header-top-bar a:last-child {
            border-right: none;
        }
        .header-top-bar .social-icon {
            color: #a0a0a0;
            transition: color 0.2s ease;
        }
        .header-top-bar .social-icon:hover {
            color: #fff;
        }

        .main-header {
            background-color: #1a1a1a;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .main-nav-link {
            padding: 0.75rem 1rem;
            color: #f9fafb;
            font-weight: 700;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        .main-nav-link:hover, .main-nav-link.active {
            color: #ef4444;
            border-bottom-color: #ef4444;
        }
        .logo-text-main {
            font-size: 1.875rem;
            font-weight: 900;
            color: white;
        }
        .logo-text-accent {
            background-color: #ef4444;
            color: white;
            padding: 0.1rem 0.3rem;
        }
        .search-button-header {
             color: white;
             transition: color 0.2s ease;
        }
        .search-button-header:hover {
            color: #ef4444;
        }

        /* Mobile Menu Overlay (for main navigation) */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            z-index: 998; /* Below search, above content */
            display: none; /* Controlled by JS */
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
        }
        .mobile-menu-overlay.show {
            display: flex;
        }
        .mobile-menu-overlay .mobile-nav-link {
            padding: 1rem 0;
            color: #f9fafb;
            font-weight: 700;
            font-size: 1.5rem;
            text-transform: uppercase;
            text-align: center;
            width: 100%;
            transition: background-color 0.2s ease;
        }
        .mobile-menu-overlay .mobile-nav-link:hover {
            background-color: #333;
        }
        .mobile-menu-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: white;
            font-size: 2.5rem;
            cursor: pointer;
        }


        /* Mobile Search Overlay */
        .mobile-search-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 999; /* Above mobile menu overlay */
            display: none; /* Controlled by JS */
            align-items: center;
            justify-content: center;
        }
        .mobile-search-overlay.show {
            display: flex;
        }
        .mobile-search-overlay input {
            background-color: #333;
            border: 1px solid #555;
            color: #fff;
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            width: 80%;
            max-width: 500px;
            font-size: 1.25rem;
        }
        .mobile-search-overlay button {
            background-color: #ef4444;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.25rem;
            margin-left: 0.5rem;
        }
        .mobile-search-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }


        .section-title {
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 3px solid #ef4444;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            color: #1a1a1a;
        }
        .section-block-bg {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Video Player & Thumbnails */
        .video-player-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            background-color: #000;
            border-radius: 0.5rem;
        }
        .video-player-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        .video-thumbnail-overlay {
            position: relative;
        }
        .video-thumbnail-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.4);
            pointer-events: none;
            border-radius: 0.5rem;
        }
        .video-thumbnail-play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ef4444;
            border-radius: 50%;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }
        .video-thumbnail-play-icon svg {
            color: white;
            width: 1.5rem;
            height: 1.5rem;
        }
        .video-thumbnail-overlay:hover .video-thumbnail-play-icon {
            transform: translate(-50%, -50%) scale(1.1);
            background-color: #dc2626;
        }

        .main-footer {
            background-color: #1a1a1a;
            color: #a0a0a0;
        }
        .footer-logo {
            border: 2px solid #a0a0a0;
            padding: 0.25rem 0.5rem;
            font-size: 1.5rem;
            font-weight: 900;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .footer-logo span {
            background-color: #ef4444;
            color: white;
            padding: 0.1rem 0.2rem;
        }
        .footer-link {
            color: #a0a0a0;
            transition: color 0.2s ease;
        }
        .footer-link:hover {
            color: #fff;
        }
        .footer-subscribe-input {
            background-color: #333;
            border: 1px solid #555;
            color: #fff;
        }
        .footer-subscribe-input::placeholder {
            color: #888;
        }
        .footer-subscribe-button {
            background-color: #ef4444;
            transition: background-color 0.2s ease;
        }
        .footer-subscribe-button:hover {
            background-color: #dc2626;
        }

        /* Newsletter Pop-up Styles */
        .newsletter-popup {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #1a1a1a;
            color: #f9fafb;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            z-index: 1000;
            max-width: 350px;
            display: none;
            transform: translateY(100%);
            transition: transform 0.5s ease-out, opacity 0.5s ease-out;
            opacity: 0;
            border: 1px solid #333;
        }
        .newsletter-popup.show {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }
        .newsletter-popup-close-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: none;
            border: none;
            color: #a0a0a0;
            font-size: 1.25rem;
            cursor: pointer;
            line-height: 1;
            transition: color 0.2s ease;
        }
        .newsletter-popup-close-btn:hover {
            color: #ef4444;
        }
        .newsletter-popup h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #f9fafb;
        }
        .newsletter-popup p {
            font-size: 0.875rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
        .newsletter-popup input[type="email"] {
            background-color: #333;
            border: 1px solid #555;
            color: #fff;
            padding: 0.6rem 0.8rem;
            border-radius: 0.25rem;
            width: 100%;
            margin-bottom: 0.75rem;
        }
        .newsletter-popup input[type="email"]::placeholder {
            color: #888;
        }
        .newsletter-popup button[type="submit"] {
            background-color: #ef4444;
            color: white;
            font-weight: 600;
            padding: 0.6rem 1rem;
            border-radius: 0.25rem;
            width: 100%;
            transition: background-color 0.2s ease;
        }
        .newsletter-popup button[type="submit"]:hover {
            background-color: #dc2626;
        }
        .popup-message {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .popup-message.success {
            background-color: #1a4220;
            color: #d4edda;
            border: 1px solid #28a745;
        }
        .popup-message.error {
            background-color: #4a1c1d;
            color: #f8d7da;
            border: 1px solid #dc3545;
        }
    </style>
</head>
<body>

    <div class="header-top-bar hidden lg:block">
        <div class="max-w-7xl mx-auto flex justify-between items-center px-4">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1 17h-2v-9h2v9zm3-12h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm3-12h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1z"/></svg>
                <?php date_default_timezone_set('Asia/Karachi'); ?>
                <span><?php echo date('D, F j, Y'); ?> Karachi</span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="https://www.facebook.com/newshubofficial" target="_blank" class="social-icon"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v2.385z"/></svg></a>
                <a href="https://twitter.com/newshub_official" target="_blank" class="social-icon"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616v.064c0 2.298 1.634 4.218 3.791 4.66-1.05.286-2.206.34-3.268.125.658 1.946 2.564 3.328 4.816 3.362-1.794 1.407-4.062 2.242-6.52 2.242-1.018 0-1.986-.065-2.934-.173 2.32 1.503 5.078 2.382 8.046 2.382 9.648 0 14.941-8.219 14.538-15.526.982-.701 1.825-1.578 2.5-2.585z"/></svg></a>
                <a href="https://www.youtube.com/embed/" target="_blank" class="social-icon"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M21.5 8.3c0-1.1-.9-2-2-2.1-1.4-.2-6.7-.4-8.8-.4-2.2 0-7.5.2-8.8.4-1.1.1-2 .9-2.1 2-.2 1.4-.4 6.7-.4 8.8 0 2.2.2 7.5.4 8.8.1 1.1.9 2 2.1 2.1 1.4.2 6.7.4 8.8.4 2.2 0 7.5-.2 8.8-.4 1.1-.1 2-.9 2.1-2 .2-1.4.4-6.7.4-8.8 0-2.2-.2-7.5-.4-8.8zm-11.5 7.1v-6l5 3-5 3z"/></svg></a>
            </div>
        </div>
    </div>

    <header class="main-header sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <a href="index.php" class="flex-shrink-0">
                    <span class="logo-text-main">NEWS<span class="logo-text-accent">HUB</span></span>
                </a>
                <nav class="hidden lg:flex items-center space-x-2">
                    <a href="index.php" class="main-nav-link">HOME</a>
                    <?php
                    $nav_categories_result_general = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 8");
                    if ($nav_categories_result_general && $nav_categories_result_general->num_rows > 0) {
                        while ($cat = $nav_categories_result_general->fetch_assoc()) {
                            $link_href = ($cat['name'] == 'Blogs') ? 'blogs.php' : 'category.php?id=' . $cat['id'];
                            $is_active = '';
                            if (basename($_SERVER['PHP_SELF']) == 'blogs.php' && $cat['name'] == 'Blogs') {
                                $is_active = 'active';
                            } else if (basename($_SERVER['PHP_SELF']) == 'category.php' && isset($_GET['id']) && $_GET['id'] == $cat['id']) {
                                $is_active = 'active';
                            }
                            // Highlight the Videos nav link explicitly
                            if (basename($_SERVER['PHP_SELF']) == 'videos.php') {
                                $is_active = 'active';
                            }
                            echo '<a href="' . htmlspecialchars($link_href) . '" class="main-nav-link ' . $is_active . '">' . strtoupper(htmlspecialchars($cat['name'])) . '</a>';
                        }
                    }
                    ?>
                    <a href="videos.php" class="main-nav-link active">MULTIMEDIA</a> <a href="#" class="main-nav-link">LIVE TV</a>
                </nav>
                 <div class="flex items-center">
                    <form action="search.php" method="GET" class="hidden md:flex">
                        <input type="text" name="query" placeholder="Search..." class="border-gray-700 bg-gray-800 text-white rounded-l-md py-1 px-2 text-sm focus:border-red-500 focus:ring-red-500">
                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded-r-md text-sm hover:bg-red-700 transition-colors">Go</button>
                    </form>
                    <button class="lg:hidden text-white hover:text-red-500 ml-4" id="mobile-search-toggle">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                    <button class="lg:hidden text-white hover:text-red-500 ml-2" id="mobile-menu-button">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                </div>
            </div>
            <div class="hidden md:flex justify-start items-center py-1 border-t border-b border-gray-700 space-x-4">
                <a href="gold_rates.php" class="text-xs font-semibold py-1 px-2 bg-gray-700 text-white rounded-sm hover:bg-gray-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'gold_rates.php') ? 'bg-red-600' : ''; ?>">#GOLD RATES</a>
                <a href="currency_exchange.php" class="text-xs font-semibold py-1 px-2 bg-gray-700 text-white rounded-sm hover:bg-gray-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'currency_exchange.php') ? 'bg-red-600' : ''; ?>">#CURRENCY EXCHANGE</a>
                <a href="psx_updates.php" class="text-xs font-semibold py-1 px-2 bg-gray-700 text-white rounded-sm hover:bg-gray-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'psx_updates.php') ? 'bg-red-600' : ''; ?>">#PSX</a>
                <a href="psl_2025.php" class="text-xs font-semibold py-1 px-2 bg-gray-700 text-white rounded-sm hover:bg-gray-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'psl_2025.php') ? 'bg-red-600' : ''; ?>">#PSL 2025</a>
            </div>
        </div>
    </header>
    <div id="mobile-search-overlay" class="mobile-search-overlay">
        <span id="mobile-search-close" class="mobile-search-close">×</span>
        <form action="search.php" method="GET" class="flex items-center justify-center w-full">
            <input type="text" name="query" id="mobile-search-input" placeholder="Search NewsHub..." autofocus>
            <button type="submit">Search</button>
        </form>
    </div>

    <div id="mobile-menu-overlay" class="mobile-menu-overlay">
        <span id="mobile-menu-close" class="mobile-menu-close">×</span>
        <nav class="flex flex-col items-center w-full">
            <a href="index.php" class="mobile-nav-link">HOME</a>
            <?php
            $mobile_nav_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 8");
            if ($mobile_nav_categories_result && $mobile_nav_categories_result->num_rows > 0) {
                while ($cat = $mobile_nav_categories_result->fetch_assoc()) {
                    $link_href = ($cat['name'] == 'Blogs') ? 'blogs.php' : 'category.php?id=' . $cat['id'];
                    $is_active = '';
                    echo '<a href="' . htmlspecialchars($link_href) . '" class="mobile-nav-link ' . $is_active . '">' . strtoupper(htmlspecialchars($cat['name'])) . '</a>';
                }
            }
            ?>
            <a href="videos.php" class="mobile-nav-link active">MULTIMEDIA</a>
            <a href="#" class="mobile-nav-link">LIVE TV</a>
            <hr class="w-2/3 border-gray-700 my-4">
            <a href="gold_rates.php" class="mobile-nav-link">#GOLD RATES</a>
            <a href="currency_exchange.php" class="mobile-nav-link">#CURRENCY EXCHANGE</a>
            <a href="psx_updates.php" class="mobile-nav-link">#PSX</a>
            <a href="psl_2025.php" class="mobile-nav-link">#PSL 2025</a>
        </nav>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <?php if (!empty($subscription_status_message)): ?>
            <div class="mx-auto px-4 py-3 mb-6
                <?php echo ($subscription_status_type == 'success') ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>
                rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($subscription_status_message); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($video['title']); ?></h1>

            <div class="text-gray-600 text-sm mb-4">
                <p>Published on: <?php echo date('F j, Y', strtotime($video['created_at'] ?? '')); ?></p>
                <?php if (!empty($video['source'])): ?>
                    <p>Source: <?php echo htmlspecialchars($video['source']); ?></p>
                <?php endif; ?>
            </div>

            <?php
            // Reconstruct the embed URL using the extracted ID for robustness
            $youtube_id_for_embed = getYouTubeVideoId($video['youtube_embed_code'] ?? '');
            if (!empty($youtube_id_for_embed)):
            ?>
                <div class="video-player-container mb-6">
                    <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id_for_embed); ?>?autoplay=0&controls=1&showinfo=0&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            <?php else: ?>
                <p class="text-red-600 mb-4">No video embed code available or invalid YouTube URL for this video.</p>
            <?php endif; ?>

            <?php if (!empty($video['description'])): ?>
                <div class="prose max-w-none text-gray-800 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($video['description'])); ?>
                </div>
            <?php endif; ?>

            <div class="mt-8 pt-4 border-t border-gray-200">
                <h2 class="section-title">More Videos</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    // Ensure $more_videos_result_final is an array before looping
                    if (!empty($more_videos_result_final)) {
                        foreach ($more_videos_result_final as $more_video) {
                            echo '<a href="single_video.php?id=' . htmlspecialchars($more_video['id']) . '" class="bg-gray-100 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center space-x-3">';
                            // Prefer custom uploaded image, else generate YouTube thumbnail
                            echo '<div class="relative w-20 h-14 rounded overflow-hidden flex-shrink-0 video-thumbnail-overlay">';
                            $more_video_youtube_id = getYouTubeVideoId($more_video['youtube_embed_code'] ?? '');
                            if (!empty($more_video['image'])) {
                                echo '<img src="uploads/' . htmlspecialchars($more_video['image']) . '" alt="' . htmlspecialchars($more_video['title'] ?? 'Video Thumbnail') . '" class="w-full h-full object-cover">';
                            } elseif ($more_video_youtube_id) {
                                echo '<img src="http://img.youtube.com/vi/' . htmlspecialchars($more_video_youtube_id) . '/default.jpg" alt="' . htmlspecialchars($more_video['title'] ?? 'Video Thumbnail') . '" class="w-full h-full object-cover">';
                            } else {
                                echo '<div class="w-full h-full bg-gray-300 rounded flex items-center justify-center text-gray-600 text-xs text-center">Video Thumbnail</div>';
                            }
                            // Play icon for thumbnail
                            echo '<div class="video-thumbnail-play-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm14.024-.828a.75.75 0 0 0 0-1.343l-4.995-3.002A.75.75 0 0 0 8.25 8.66l.004 6.68a.75.75 0 0 0 1.259.613l4.996-3.003Z" clip-rule="evenodd" /></svg></div>';
                            echo '</div>'; // End video-thumbnail-overlay

                            echo '<div><h4 class="text-md font-semibold text-gray-800">' . htmlspecialchars($more_video['title']) . '</h4></div>';
                            echo '</a>';
                        }
                    } else {
                        echo '<p class="col-span-full text-gray-600">No other videos available.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
            <div class="col-span-full md:col-span-1 lg:col-span-1">
                <a href="index.php" class="footer-logo">NEWS<span class="bg-red-600 text-white px-1">HUB</span></a>
                <p class="text-sm mt-2">Your ultimate source for reliable and timely news from around the world.</p>
            </div>
            <div>
                <h3 class="font-bold text-lg mb-2">SECTIONS</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="index.php" class="footer-link">Home</a></li>
                    <?php
                        // Use general fetches for footer navigation
                        // Check if $footer_categories_result_general exists and has rows before looping
                        if ($footer_categories_result_general && $footer_categories_result_general->num_rows > 0) {
                            while ($cat = $footer_categories_result_general->fetch_assoc()) {
                                $link_href = ($cat['name'] == 'Blogs') ? 'blogs.php' : 'category.php?id=' . $cat['id'];
                                echo '<li><a href="' . htmlspecialchars($link_href) . '" class="footer-link">' . htmlspecialchars($cat['name']) . '</a></li>';
                            }
                        }
                    ?>
                </ul>
            </div>
             <div>
                <h3 class="font-bold text-lg mb-2">ABOUT</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="about.php" class="footer-link">About Us</a></li>
                    <li><a href="privacy.php" class="footer-link">Privacy Policy</a></li>
                    <li><a href="contact.php" class="footer-link">Contact Us</a></li>
                </ul>
            </div>
             <div>
                <h3 class="font-bold text-lg mb-2">MORE</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="footer-link">Sitemap</a></li>
                    <li><a href="#" class="footer-link">Careers</a></li>
                    <li><a href="#" class="footer-link">Advertise</a></li>
                </ul>
            </div>
            <div class="col-span-full md:col-span-2 lg:col-span-1">
                 <h3 class="font-bold text-lg mb-2">SUBSCRIBE</h3>
                 <p class="text-sm mb-3">Get the latest news and updates delivered straight to your inbox.</p>
                 <form action="save_subscriber.php" method="POST" class="flex">
                    <input type="email" name="email" placeholder="Your email" class="w-full rounded-l-md p-2 footer-subscribe-input focus:ring-red-500 focus:border-red-500">
                    <button type="submit" class="p-2 rounded-r-md text-white font-bold footer-subscribe-button">Go</button>
                 </form>
            </div>
        </div>
        <div class="bg-gray-900 py-4 text-center text-sm mt-8">
            <p>© <?php echo date('Y'); ?> NewsHub. All Rights Reserved.</p>
        </div>
    </footer>

    <div id="newsletter-popup" class="newsletter-popup">
        <button id="newsletter-popup-close" class="newsletter-popup-close-btn">×</button>
        <h3>Subscribe to Our Newsletter!</h3>
        <p>Get the latest news and exclusive updates directly in your inbox.</p>
        <form action="save_subscriber.php" method="POST">
            <input type="email" name="email" placeholder="Your email address" required>
            <button type="submit">Subscribe Now</button>
        </form>
        <?php if (!empty($subscription_status_message)): ?>
            <div class="popup-message <?php echo ($subscription_status_type == 'success') ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($subscription_status_message); ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.getElementById('newsletter-popup');
            const closeBtn = document.getElementById('newsletter-popup-close');
            const popupSeenKey = 'newsletterPopupSeen';

            function showPopup() {
                if (!sessionStorage.getItem(popupSeenKey)) {
                    popup.classList.add('show');
                    sessionStorage.setItem(popupSeenKey, 'true');
                }
            }

            setTimeout(showPopup, 5000);

            let scrollTriggered = false;
            window.addEventListener('scroll', function() {
                if (!sessionStorage.getItem(popupSeenKey) && !scrollTriggered && window.scrollY > (document.body.scrollHeight * 0.2)) {
                    showPopup();
                    scrollTriggered = true;
                }
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    popup.classList.remove('show');
                });
            }

            <?php if (!empty($subscription_status_message)): ?>
                showPopup();
            <?php endif; ?>

            // Mobile Search Toggle Logic
            const mobileSearchToggle = document.getElementById('mobile-search-toggle');
            const mobileSearchOverlay = document.getElementById('mobile-search-overlay');
            const mobileSearchClose = document.getElementById('mobile-search-close');

            if (mobileSearchToggle && mobileSearchOverlay && mobileSearchClose) {
                mobileSearchToggle.addEventListener('click', function() {
                    mobileSearchOverlay.classList.add('show');
                    document.getElementById('mobile-search-input').focus();
                });

                mobileSearchClose.addEventListener('click', function() {
                    mobileSearchOverlay.classList.remove('show');
                });

                mobileSearchOverlay.addEventListener('click', function(event) {
                    if (event.target === mobileSearchOverlay) {
                        mobileSearchOverlay.classList.remove('show');
                    }
                });
            }

            // Mobile Menu Logic (Hamburger Menu)
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
            const mobileMenuCloseButton = document.getElementById('mobile-menu-close');

            if (mobileMenuButton && mobileMenuOverlay && mobileMenuCloseButton) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenuOverlay.classList.add('show');
                    document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
                });

                mobileMenuCloseButton.addEventListener('click', function() {
                    mobileMenuOverlay.classList.remove('show');
                    document.body.style.overflow = ''; // Restore scrolling
                });

                mobileMenuOverlay.addEventListener('click', function(event) {
                    if (event.target === mobileMenuOverlay) { // If clicked on overlay background
                        mobileMenuOverlay.classList.remove('show');
                        document.body.style.overflow = ''; // Restore scrolling
                    }
                });
            }
        });
    </script>
</body>
</html>
