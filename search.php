<?php
// FILE: search.php (FIXED Undefined variables for section data, added full consistency and responsiveness)

// --- 1. SETUP & DATABASE CONNECTION ---
require_once 'includes/db.php';

// Helper function to extract YouTube Video ID from URL.
function getYouTubeVideoId($url) {
    preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
    return $matches[1] ?? null;
}

// --- 2. GET & SANITIZE SEARCH QUERY AND FILTERS ---
$search_query = isset($_GET['query']) ? trim(htmlspecialchars($_GET['query'])) : '';
$filter_category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$filter_content_type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'all'; // 'all', 'articles', 'videos'

// Pagination settings
$results_per_page = 9; // Number of results to show per page/load
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $results_per_page;

$articles_result_search = null;
$videos_result_search = null;
$total_results = 0;

if (!empty($search_query)) {
    $search_term_like = "%" . $search_query . "%";
    $query_params_articles = [];
    $param_types_articles = "";

    // --- Build Article Query ---
    $article_sql = "
        SELECT a.id, a.title, a.image, a.content, a.created_at, c.name as category_name, ad.full_name as author_name, 'article' as item_type
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        LEFT JOIN admins ad ON a.author_id = ad.id
        WHERE (a.title LIKE ? OR a.content LIKE ?)
    ";
    $query_params_articles[] = &$search_term_like;
    $query_params_articles[] = &$search_term_like;
    $param_types_articles .= "ss";

    if ($filter_category_id > 0) {
        $article_sql .= " AND a.category_id = ?";
        $query_params_articles[] = &$filter_category_id;
        $param_types_articles .= "i";
    }

    $article_sql_count = "SELECT COUNT(*) FROM articles a WHERE (a.title LIKE ? OR a.content LIKE ?)";
    $count_params_articles = [&$search_term_like, &$search_term_like];
    $count_param_types_articles = "ss";

    if ($filter_category_id > 0) {
        $article_sql_count .= " AND a.category_id = ?";
        $count_params_articles[] = &$filter_category_id;
        $count_param_types_articles .= "i";
    }

    // --- Build Video Query ---
    $video_sql = "
        SELECT v.id, v.title, v.image, v.youtube_embed_code, v.created_at, 'video' as item_type
        FROM videos v
        WHERE (v.title LIKE ? OR v.description LIKE ?)
    ";
    $video_query_params = [&$search_term_like, &$search_term_like];
    $video_param_types = "ss";

    $video_sql_count = "SELECT COUNT(*) FROM videos v WHERE (v.title LIKE ? OR v.description LIKE ?)";
    $video_count_params = [&$search_term_like, &$search_term_like];
    $video_count_param_types = "ss";


    // --- Execute Count Queries ---
    $total_articles_count = 0;
    $total_videos_count = 0;

    if ($filter_content_type === 'all' || $filter_content_type === 'articles') {
        $stmt_count_articles = $conn->prepare($article_sql_count);
        if ($stmt_count_articles) {
            call_user_func_array([$stmt_count_articles, 'bind_param'], array_merge([$count_param_types_articles], $count_params_articles));
            $stmt_count_articles->execute();
            $stmt_count_articles->bind_result($total_articles_count);
            $stmt_count_articles->fetch();
            $stmt_count_articles->close();
        }
    }

    if ($filter_content_type === 'all' || $filter_content_type === 'videos') {
        $stmt_count_videos = $conn->prepare($video_sql_count);
        if ($stmt_count_videos) {
            call_user_func_array([$stmt_count_videos, 'bind_param'], array_merge([$video_count_param_types], $video_count_params));
            $stmt_count_videos->execute();
            $stmt_count_videos->bind_result($total_videos_count);
            $stmt_count_videos->fetch();
            $stmt_count_videos->close();
        }
    }

    if ($filter_content_type === 'articles') {
        $total_results = $total_articles_count;
    } elseif ($filter_content_type === 'videos') {
        $total_results = $total_videos_count;
    } else { // 'all'
        $total_results = $total_articles_count + $total_videos_count;
    }

    // --- Execute Data Queries with LIMIT and OFFSET ---
    if ($filter_content_type === 'all' || $filter_content_type === 'articles') {
        $final_article_sql = $article_sql . " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
        $stmt_articles = $conn->prepare($final_article_sql);
        if ($stmt_articles) {
            $current_article_limit = $results_per_page;
            $current_article_offset = $offset;
            call_user_func_array([$stmt_articles, 'bind_param'], array_merge([$param_types_articles . "ii"], $query_params_articles, [&$current_article_limit, &$current_article_offset]));
            $stmt_articles->execute();
            $articles_result_search = $stmt_articles->get_result();
            $stmt_articles->close();
        }
    }

    if ($filter_content_type === 'all' || $filter_content_type === 'videos') {
        $final_video_sql = $video_sql . " ORDER BY v.created_at DESC LIMIT ? OFFSET ?";
        $stmt_videos = $conn->prepare($final_video_sql);
        if ($stmt_videos) {
            $current_video_limit = $results_per_page;
            $current_video_offset = $offset;
            call_user_func_array([$stmt_videos, 'bind_param'], array_merge([$video_param_types . "ii"], $video_query_params, [&$current_video_limit, &$current_video_offset]));
            $stmt_videos->execute();
            $videos_result_search = $stmt_videos->get_result();
            $stmt_videos->close();
        }
    }
}

// Fetch all categories for filter dropdown
$all_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories_list = [];
if ($all_categories_result) {
    while($cat = $all_categories_result->fetch_assoc()) {
        $categories_list[] = $cat;
    }
}

$total_pages = ceil($total_results / $results_per_page);

// Retrieve and clear subscription message from session (for footer and popup)
$subscription_status_type = $_SESSION['subscription_status_type'] ?? '';
$subscription_status_message = $_SESSION['subscription_status_message'] ?? '';
if (isset($_SESSION['subscription_status_type'])) {
    unset($_SESSION['subscription_status_type']);
    unset($_SESSION['subscription_status_message']);
}

// --- Data for General Sections (to avoid Undefined variable warnings in common header/footer) ---
// Initialize general data arrays to empty
$nav_categories_result_general = null;
$footer_categories_result_general = null;
$business_articles_data = [];
$world_articles_data = [];
$sports_articles_data = [];
$blogs_articles_data = [];
$international_articles_data = [];
$headlines_data = []; // Also ensure headlines data is fetched

// Fetch main nav categories (needed for header)
$nav_categories_result_general = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 8");
// Fetch footer categories (needed for footer)
$footer_categories_result_general = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 4");

// Fetch data for the sidebars/footer sections (these were causing undefined variable errors)
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
    <title>Search Results for "<?php echo htmlspecialchars($search_query); ?>" - NewsHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #333; }

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

        .article-card {
            background-color: #fff;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .article-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .article-card img {
            width: 100%;
            height: 12rem;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
        }
        .article-card-content {
            padding: 1rem;
            padding-top: 0;
        }
        .article-card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.4;
        }
        .article-card-meta {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }
        .article-card-category {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #ef4444;
            text-transform: uppercase;
            display: block;
            margin-bottom: 0.25rem;
        }

        /* Video specific for search results */
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
                    <a href="index.php" class="main-nav-link active">HOME</a>
                    <?php
                    $nav_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 8");
                    if ($nav_categories_result && $nav_categories_result->num_rows > 0) {
                        while ($cat = $nav_categories_result->fetch_assoc()) {
                            $link_href = ($cat['name'] == 'Blogs') ? 'blogs.php' : 'category.php?id=' . $cat['id'];
                            $is_active = '';
                            if (basename($_SERVER['PHP_SELF']) == 'blogs.php' && $cat['name'] == 'Blogs') {
                                $is_active = 'active';
                            } else if (basename($_SERVER['PHP_SELF']) == 'category.php' && isset($_GET['id']) && $_GET['id'] == $cat['id']) {
                                $is_active = 'active';
                            }
                            echo '<a href="' . htmlspecialchars($link_href) . '" class="main-nav-link ' . $is_active . '">' . strtoupper(htmlspecialchars($cat['name'])) . '</a>';
                        }
                    }
                    ?>
                    <a href="videos.php" class="main-nav-link">MULTIMEDIA</a>
                    <a href="#" class="main-nav-link">LIVE TV</a>
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
                    $is_active = ''; // Active state for mobile nav can be added if needed
                    echo '<a href="' . htmlspecialchars($link_href) . '" class="mobile-nav-link ' . $is_active . '">' . strtoupper(htmlspecialchars($cat['name'])) . '</a>';
                }
            }
            ?>
            <a href="videos.php" class="mobile-nav-link">MULTIMEDIA</a>
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

        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 lg:col-span-9">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="md:col-span-1 section-block-bg">
                         <h2 class="section-title">Top News</h2>
                        <?php if(!empty($top_news_articles_display)): ?>
                            <?php $main_top_news_item = array_shift($top_news_articles_display); ?>
                            <div class="featured-large-card mb-4">
                                <a href="single_article.php?id=<?php echo htmlspecialchars($main_top_news_item['id']); ?>">
                                    <img src="uploads/<?php echo htmlspecialchars($main_top_news_item['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($main_top_news_item['title']); ?>">
                                    <div class="featured-large-card-content">
                                        <span class="article-card-category"><?php echo htmlspecialchars($main_top_news_item['category_name'] ?? 'News'); ?></span>
                                        <h3 class="featured-large-card-title"><?php echo htmlspecialchars($main_top_news_item['title']); ?></h3>
                                        <p class="featured-large-card-meta">
                                            By <span class="font-semibold"><?php echo htmlspecialchars($main_top_news_item['author_name'] ?? 'NewsHub Team'); ?></span> -
                                            <?php echo date('F j, Y', strtotime($main_top_news_item['created_at'])); ?>
                                        </p>
                                    </div>
                                </a>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <?php foreach($top_news_articles_display as $article_item): ?>
                                    <div class="article-card flex items-center space-x-3 shadow-none hover:shadow-none p-0 bg-transparent rounded-none">
                                        <a href="single_article.php?id=<?php echo htmlspecialchars($article_item['id']); ?>" class="flex items-center space-x-3 w-full border-b border-gray-200 pb-3 last:border-b-0 last:pb-0">
                                            <img src="uploads/<?php echo htmlspecialchars($article_item['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($article_item['title']); ?>" class="w-24 h-16 object-cover rounded-sm flex-shrink-0">
                                            <div class="flex-1">
                                                <span class="article-card-category text-sm"><?php echo htmlspecialchars($article_item['category_name'] ?? 'News'); ?></span>
                                                <h4 class="text-base font-semibold text-gray-800 leading-tight"><?php echo htmlspecialchars($article_item['title']); ?></h4>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-600">No top news articles found.</p>
                        <?php endif; ?>
                    </div>

                    <div class="md:col-span-1 section-block-bg">
                        <h2 class="section-title">Editor's Pick</h2>
                        <div class="sidebar-block shadow-none p-0 bg-transparent">
                            <?php if (!empty($editors_picks_data)): ?>
                                <?php foreach($editors_picks_data as $pick): ?>
                                    <a href="single_article.php?id=<?php echo htmlspecialchars($pick['id']); ?>" class="sidebar-article-item hover:text-red-600">
                                        <img src="uploads/<?php echo htmlspecialchars($pick['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($pick['title']); ?>">
                                        <div>
                                            <h4><?php echo htmlspecialchars($pick['title']); ?></h4>
                                            <p class="meta">By <span class="font-semibold"><?php echo htmlspecialchars($pick['author_name'] ?? 'NewsHub Team'); ?></span> - <?php echo date('M j, Y', strtotime($pick['created_at'])); ?></p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-600">No editor's picks found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="section-block-bg mb-8">
                    <h2 class="section-title">Latest Videos</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <?php if (!empty($all_videos_for_display)): ?>
                            <?php foreach ($all_videos_for_display as $video_item): ?>
                                <div class="group relative">
                                    <a href="single_video.php?id=<?php echo htmlspecialchars($video_item['id']); ?>" class="block">
                                        <div class="w-full aspect-video rounded-lg overflow-hidden relative video-item-thumbnail-overlay">
                                            <?php
                                            $youtube_id = getYouTubeVideoId($video_item['youtube_embed_code'] ?? '');
                                            if ($youtube_id) {
                                                echo '<img src="http://img.youtube.com/vi/' . htmlspecialchars($youtube_id) . '/hqdefault.jpg" alt="Video Thumbnail" class="w-full h-full object-cover">';
                                            } else {
                                                echo '<img src="uploads/' . htmlspecialchars($video_item['image'] ?? 'placeholder.jpg') . '" alt="Video Thumbnail" class="w-full h-full object-cover">';
                                                if(empty($video_item['image'])) {
                                                    echo '<div class="bg-gray-200 w-full h-full flex items-center justify-center text-gray-500">No Thumbnail</div>';
                                                }
                                            }
                                            ?>
                                            <div class="video-item-play-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm14.024-.828a.75.75 0 0 0 0-1.343l-4.995-3.002A.75.75 0 0 0 8.25 8.66l.004 6.68a.75.75 0 0 0 1.259.613l4.996-3.003Z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <h4 class="mt-2 text-sm font-semibold text-gray-800 leading-tight group-hover:text-red-600"><?php echo htmlspecialchars($video_item['title']); ?></h4>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="col-span-full text-gray-600">No videos available yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div class="md:col-span-1 section-block-bg">
                        <h2 class="section-title">International</h2>
                        <ul class="space-y-3">
                            <?php if (!empty($international_articles_data)): ?>
                                <?php foreach($international_articles_data as $article_item): ?>
                                     <li class="pb-2 border-b border-gray-200 last:border-b-0 last:pb-0">
                                        <a href="single_article.php?id=<?php echo htmlspecialchars($article_item['id']); ?>" class="font-medium text-gray-800 hover:text-red-600">
                                            <?php echo htmlspecialchars($article_item['title']); ?>
                                        </a>
                                        <p class="text-xs text-gray-500 mt-1">By <span class="font-semibold"><?php echo htmlspecialchars($article_item['author_name'] ?? 'NewsHub Team'); ?></span> - <?php echo date('M j, Y', strtotime($article_item['created_at'])); ?></p>
                                     </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><p class="text-gray-600">No international articles yet.</p></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="md:col-span-2 section-block-bg">
                         <h2 class="section-title">Pakistan</h2>
                         <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <?php if (!empty($pakistan_articles_data)): ?>
                                <?php foreach($pakistan_articles_data as $article_item): ?>
                                    <div class="article-card">
                                        <a href="single_article.php?id=<?php echo htmlspecialchars($article_item['id']); ?>">
                                            <img src="uploads/<?php echo htmlspecialchars($article_item['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($article_item['title']); ?>">
                                            <div class="article-card-content">
                                                <h3 class="article-card-title"><?php echo htmlspecialchars($article_item['title']); ?></h3>
                                                <p class="article-card-meta">
                                                    By <span class="font-semibold"><?php echo htmlspecialchars($article_item['author_name'] ?? 'NewsHub Team'); ?></span> -
                                                    <?php echo date('F j, Y', strtotime($article_item['created_at'])); ?>
                                                </p>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="col-span-full text-center text-gray-600">No Pakistan articles yet.</p>
                            <?php endif; ?>
                         </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <?php
                    $sections_to_display = [
                        ['title' => 'Business', 'articles_data_arr' => $business_articles_data],
                        ['title' => 'World', 'articles_data_arr' => $world_articles_data],
                        ['title' => 'Sports', 'articles_data_arr' => $sports_articles_data],
                        ['title' => 'Blogs', 'articles_data_arr' => $blogs_articles_data],
                    ];

                    foreach ($sections_to_display as $section):
                    ?>
                        <div class="col-span-1 section-block-bg">
                            <h2 class="section-title"><?php echo htmlspecialchars($section['title']); ?></h2>
                            <ul class="space-y-3">
                                <?php if (!empty($section['articles_data_arr'])): ?>
                                    <?php
                                    $count = 0;
                                    foreach($section['articles_data_arr'] as $article_item):
                                        if ($count == 0 && ($section['title'] == 'Business' || $section['title'] == 'Lifestyle' || $section['title'] == 'Science & Technology')):
                                    ?>
                                        <li class="mb-4">
                                            <div class="article-card">
                                                <a href="single_article.php?id=<?php echo htmlspecialchars($article_item['id']); ?>">
                                                    <img src="uploads/<?php echo htmlspecialchars($article_item['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($article_item['title']); ?>">
                                                    <div class="article-card-content">
                                                        <h3 class="article-card-title"><?php echo htmlspecialchars($article_item['title']); ?></h3>
                                                        <p class="article-card-meta">
                                                            By <span class="font-semibold"><?php echo htmlspecialchars($article_item['author_name'] ?? 'NewsHub Team'); ?></span> -
                                                            <?php echo date('F j, Y', strtotime($article_item['created_at'])); ?>
                                                        </p>
                                                    </div>
                                                </a>
                                            </div>
                                        </li>
                                    <?php else: ?>
                                        <li class="pb-2 border-b border-gray-200 last:border-b-0 last:pb-0">
                                            <a href="single_article.php?id=<?php echo htmlspecialchars($article_item['id']); ?>" class="font-medium text-gray-800 hover:text-red-600">
                                                <?php echo htmlspecialchars($article_item['title']); ?>
                                            </a>
                                            <p class="text-xs text-gray-500 mt-1">By <span class="font-semibold"><?php echo htmlspecialchars($article_item['author_name'] ?? 'NewsHub Team'); ?></span> - <?php echo date('M j, Y', strtotime($article_item['created_at'])); ?></p>
                                        </li>
                                    <?php endif;
                                    $count++;
                                    endforeach; ?>
                                <?php else: ?>
                                    <li><p class="text-gray-600">No <?php echo strtolower(htmlspecialchars($section['title'])); ?> articles yet.</p></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <aside class="col-span-12 lg:col-span-3">
                <div class="sidebar-block sticky top-24 mb-6">
                    <h2 class="section-title">News Headlines</h2>
                    <div class="space-y-4">
                        <?php if (!empty($headlines_data)): ?>
                            <?php foreach($headlines_data as $headline_item): ?>
                                <a href="single_article.php?id=<?php echo htmlspecialchars($headline_item['id']); ?>" class="sidebar-article-item hover:text-red-600">
                                    <img src="uploads/<?php echo htmlspecialchars($headline_item['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($headline_item['title']); ?>">
                                    <div>
                                        <h4><?php echo htmlspecialchars($headline_item['title']); ?></h4>
                                        <p class="meta">By <span class="font-semibold"><?php echo htmlspecialchars($headline_item['author_name'] ?? 'NewsHub Team'); ?></span> - <?php echo date('F j, Y', strtotime($headline_item['created_at'])); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-600">No recent headlines.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
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
                        $footer_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 4");
                        if ($footer_categories_result && $footer_categories_result->num_rows > 0) {
                            while ($cat = $footer_categories_result->fetch_assoc()) {
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