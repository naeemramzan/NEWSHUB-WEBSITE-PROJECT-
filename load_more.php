<?php
// This file acts as a background API for the "Load More" button.

require_once 'includes/db.php';

// --- Get Parameters ---
// The JavaScript sends an 'offset' to tell this script how many articles to skip.
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = 6; // The number of new articles to load each time.

// --- Fetch More Articles ---
// This query gets the next batch of articles from the database.
$stmt = $conn->prepare("SELECT id, title, image FROM articles ORDER BY id ASC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// --- Generate HTML for the New Articles ---
$html = '';
if ($result->num_rows > 0) {
    while ($article = $result->fetch_assoc()) {
        // Create the HTML for each article card, matching the style on the homepage.
        $html .= '<a href="single_article.php?id=' . $article['id'] . '" class="latest-news-card block rounded-lg overflow-hidden shadow-lg bg-gray-800 hover:bg-gray-700 transition-transform transform hover:-translate-y-1">';
        $html .= '    <img src="https://picsum.photos/400/200?random=' . $article['id'] . '" alt="' . htmlspecialchars($article['title']) . '" class="w-full h-32 object-cover">';
        $html .= '    <h3 class="p-3 font-medium text-sm leading-snug text-white">' . htmlspecialchars($article['title']) . '</h3>';
        $html .= '</a>';
    }
}

$stmt->close();
$conn->close();

// --- Send the new HTML back to the homepage's JavaScript ---
echo $html;
?>