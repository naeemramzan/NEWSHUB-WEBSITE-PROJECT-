<?php
// FILE: rss.php
// This file generates an RSS 2.0 feed of the latest articles.

// Include the database connection.
require_once 'includes/db.php';

// Get the base URL of the website for constructing full links.
// This makes the links in the feed absolute, which is required by the RSS standard.
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$project_folder = dirname($_SERVER['PHP_SELF']); // Gets the folder the project is in, e.g., /newse
$base_url .= $project_folder;

// --- Set the Content-Type header to XML ---
// This tells the browser (and RSS readers) that the content is an XML document, not HTML.
header('Content-Type: application/rss+xml; charset=utf-8');

// --- Start XML Output ---
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>NewsHub - Latest News</title>
    <link><?php echo $base_url; ?></link>
    <description>The latest news and updates from NewsHub.</description>
    <language>en-us</language>
    <atom:link href="<?php echo $base_url; ?>/rss.php" rel="self" type="application/rss+xml" />

    <?php
    // --- Fetch latest 15 articles for the feed ---
    $rss_articles = $conn->query("
        SELECT id, title, content, created_at
        FROM articles
        ORDER BY created_at DESC
        LIMIT 15
    ");

    if ($rss_articles->num_rows > 0) {
        while($article = $rss_articles->fetch_assoc()) {
            // Prepare data for XML output, ensuring it's properly encoded.
            $title = htmlspecialchars($article['title']);
            // Create a full, absolute URL for the article.
            $link = $base_url . '/single_article.php?id=' . $article['id'];
            // Format the date according to the RSS specification (RFC 822).
            $pubDate = date(DATE_RSS, strtotime($article['created_at']));
            // Create a short description, stripping HTML tags.
            $description = htmlspecialchars(substr(strip_tags($article['content']), 0, 250)) . '...';
    ?>
    <item>
        <title><?php echo $title; ?></title>
        <link><?php echo $link; ?></link>
        <guid><?php echo $link; ?></guid>
        <pubDate><?php echo $pubDate; ?></pubDate>
        <description><?php echo $description; ?></description>
    </item>
    <?php
        }
    }
    ?>

</channel>
</rss>
