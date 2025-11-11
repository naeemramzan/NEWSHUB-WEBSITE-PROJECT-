<?php
// FILE: load_more_search_results.php
// Handles AJAX requests for loading more search results (articles and videos).

require_once 'includes/db.php'; // Ensure database connection is established.

// Helper function to extract YouTube Video ID from URL.
function getYouTubeVideoId($url) {
    preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
    return $matches[1] ?? null;
}

// Ensure it's an AJAX POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['page'])) {
    http_response_code(403); // Forbidden
    echo "Invalid request.";
    exit;
}

// Sanitize and validate input
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$search_query = isset($_POST['query']) ? trim(htmlspecialchars($_POST['query'])) : '';
$filter_category_id = isset($_POST['category']) ? intval($_POST['category']) : 0;
$filter_content_type = isset($_POST['type']) ? htmlspecialchars($_POST['type']) : 'all';
$results_per_page = isset($_POST['results_per_page']) ? intval($_POST['results_per_page']) : 9;

if ($page < 1) $page = 1;
$offset = ($page - 1) * $results_per_page;

$search_term_like = "%" . $search_query . "%";
$output_html = '';

// --- Fetch Articles ---
if ($filter_content_type === 'all' || $filter_content_type === 'articles') {
    $article_query_params = [];
    $article_param_types = "";

    $article_sql = "
        SELECT a.id, a.title, a.image, a.content, a.created_at, c.name as category_name, ad.full_name as author_name, 'article' as item_type
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        LEFT JOIN admins ad ON a.author_id = ad.id
        WHERE (a.title LIKE ? OR a.content LIKE ?)
    ";
    $article_query_params[] = &$search_term_like;
    $article_query_params[] = &$search_term_like;
    $article_param_types .= "ss";

    if ($filter_category_id > 0) {
        $article_sql .= " AND a.category_id = ?";
        $article_query_params[] = &$filter_category_id;
        $article_param_types .= "i";
    }

    $article_sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
    $article_query_params[] = &$results_per_page;
    $article_query_params[] = &$offset;
    $article_param_types .= "ii";

    $stmt_articles = $conn->prepare($article_sql);
    if ($stmt_articles) {
        call_user_func_array([$stmt_articles, 'bind_param'], array_merge([$article_param_types], $article_query_params));
        $stmt_articles->execute();
        $articles_result = $stmt_articles->get_result();

        if ($articles_result->num_rows > 0) {
            $output_html .= '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">';
            while($article = $articles_result->fetch_assoc()) {
                $output_html .= '<div class="bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-2xl transition-shadow">';
                $output_html .= '<a href="single_article.php?id=' . htmlspecialchars($article['id']) . '">';
                $output_html .= '<img src="uploads/' . htmlspecialchars($article['image']) . '" alt="' . htmlspecialchars($article['title']) . '" class="w-full h-48 object-cover">';
                $output_html .= '<div class="p-4">';
                $output_html .= '<span class="text-red-400 text-xs font-semibold uppercase">' . htmlspecialchars($article['category_name']) . '</span>';
                $output_html .= '<h3 class="text-lg font-bold text-white mb-2">' . htmlspecialchars($article['title']) . '</h3>';
                $output_html .= '<p class="text-sm text-gray-400">';
                $output_html .= 'By ' . htmlspecialchars($article['author_name'] ?? 'NewsHub Team') . ' | ';
                $output_html .= date('F j, Y', strtotime($article['created_at']));
                $output_html .= '</p>';
                $output_html .= '</div>';
                $output_html .= '</a>';
                $output_html .= '</div>';
            }
            $output_html .= '</div>';
        }
        $stmt_articles->close();
    }
}

// --- Fetch Videos ---
if ($filter_content_type === 'all' || $filter_content_type === 'videos') {
    $video_query_params = [];
    $video_param_types = "";

    $video_sql = "
        SELECT v.id, v.title, v.image, v.youtube_embed_code, v.created_at, 'video' as item_type
        FROM videos v
        WHERE (v.title LIKE ? OR v.description LIKE ?)
    ";
    $video_query_params[] = &$search_term_like;
    $video_query_params[] = &$search_term_like;
    $video_param_types .= "ss";

    $video_sql .= " ORDER BY v.created_at DESC LIMIT ? OFFSET ?";
    $video_query_params[] = &$results_per_page;
    $video_query_params[] = &$offset;
    $video_param_types .= "ii";


    $stmt_videos = $conn->prepare($video_sql);
    if ($stmt_videos) {
        call_user_func_array([$stmt_videos, 'bind_param'], array_merge([$video_param_types], $video_query_params));
        $stmt_videos->execute();
        $videos_result = $stmt_videos->get_result();

        if ($videos_result->num_rows > 0) {
            $output_html .= '<h2 class="section-title mt-8 text-white">Videos</h2>'; // Only show title if there are videos
            $output_html .= '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
            while($video = $videos_result->fetch_assoc()) {
                $video_thumbnail = '';
                if (!empty($video['image'])) {
                    $video_thumbnail = 'uploads/' . htmlspecialchars($video['image']);
                } else {
                    $youtube_id = getYouTubeVideoId($video['youtube_embed_code']);
                    if ($youtube_id) {
                        $video_thumbnail = 'https://www.youtube.com/embed/' . htmlspecialchars($youtube_id) . '/default.jpg';
                    }
                }
                $output_html .= '<div class="bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-2xl transition-shadow">';
                $output_html .= '<a href="single_video.php?id=' . htmlspecialchars($video['id']) . '">';
                if (!empty($video_thumbnail)) {
                    $output_html .= '<img src="' . $video_thumbnail . '" alt="' . htmlspecialchars($video['title']) . '" class="w-full h-48 object-cover">';
                } else {
                    $output_html .= '<div class="w-full h-48 bg-gray-700 flex items-center justify-center text-gray-400">No Thumbnail</div>';
                }
                $output_html .= '<div class="p-4">';
                $output_html .= '<h3 class="text-lg font-bold text-white mb-2">' . htmlspecialchars($video['title']) . '</h3>';
                $output_html .= '<p class="text-sm text-gray-400">Published on ' . date('F j, Y', strtotime($video['created_at'])) . '</p>';
                $output_html .= '</div>';
                $output_html .= '</a>';
                $output_html .= '</div>';
            }
            $output_html .= '</div>';
        }
        $stmt_videos->close();
    }
}

echo $output_html; // Output the generated HTML

?>
