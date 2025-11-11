<?php
session_start();
require_once 'includes/db.php';
require_once 'header.php';

// --- NEW FUNCTION: Create URL-friendly slugs from article titles ---
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/\s+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// --- DATA FETCHING ---
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = null;
$related_articles = [];
$trending_topics = [];
$comments = [];
$tags = [];

if ($article_id > 0) {
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name, ad.full_name as author_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id LEFT JOIN admins ad ON a.admin_id = ad.id WHERE a.id = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $article = $result->fetch_assoc();
        $stmt->close();

        $update_stmt = $conn->prepare("UPDATE articles SET view_count = view_count + 1 WHERE id = ?");
        $update_stmt->bind_param("i", $article_id);
        $update_stmt->execute();
        $update_stmt->close();

        $comment_stmt = $conn->prepare("SELECT name, created_at, comment FROM comments WHERE article_id = ? AND is_approved = 1 ORDER BY created_at DESC");
        $comment_stmt->bind_param("i", $article_id);
        $comment_stmt->execute();
        $comment_result = $comment_stmt->get_result();
        while($row = $comment_result->fetch_assoc()){
            $comments[] = $row;
        }
        $comment_stmt->close();

        $cat_id = $article['category_id'];
        $related_stmt = $conn->prepare("SELECT id, title, image FROM articles WHERE category_id = ? AND id != ? ORDER BY created_at DESC LIMIT 6");
        $related_stmt->bind_param("ii", $cat_id, $article_id);
        $related_stmt->execute();
        $related_result = $related_stmt->get_result();
        while($row = $related_result->fetch_assoc()){
            $related_articles[] = $row;
        }
        $related_stmt->close();

        if (empty($related_articles)) {
            $fallback_stmt = $conn->prepare("SELECT id, title, image FROM articles WHERE id != ? ORDER BY created_at DESC LIMIT 6");
            $fallback_stmt->bind_param("i", $article_id);
            $fallback_stmt->execute();
            $fallback_result = $fallback_stmt->get_result();
            while($row = $fallback_result->fetch_assoc()){
                $related_articles[] = $row;
            }
            $fallback_stmt->close();
        }

        $tag_stmt = $conn->prepare("SELECT t.id, t.name FROM tags t JOIN article_tag_map m ON t.id = m.tag_id WHERE m.article_id = ?");
        $tag_stmt->bind_param("i", $article_id);
        $tag_stmt->execute();
        $tag_result = $tag_stmt->get_result();
        while($row = $tag_result->fetch_assoc()){
            $tags[] = $row;
        }
        $tag_stmt->close();
    }
}

$trending_result = $conn->query("SELECT id, name FROM categories ORDER BY id DESC LIMIT 5");
while($row = $trending_result->fetch_assoc()){
    $trending_topics[] = $row;
}

$comment_message = $_SESSION['comment_message'] ?? null;
$comment_message_type = $_SESSION['comment_message_type'] ?? null;
unset($_SESSION['comment_message'], $_SESSION['comment_message_type']);

// NEW: Define article URL and title for sharing
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$article_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$article_title = $article ? htmlspecialchars($article['title']) : 'NewsHub Article';
?>
<head>
    <title><?php echo $article ? htmlspecialchars($article['title']) : 'Article Not Found'; ?> - NewsHub</title>
    
    <!-- FAVICON -->
    <link rel="icon" href="images/logo.png" type="image/png"> 
    
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3637721699586342"
     crossorigin="anonymous"></script>

</head>

<main class="container mx-auto my-8 px-4">
    <?php if ($article): ?>
        <div class="grid grid-cols-12 gap-8">
            <div class="col-span-12 lg:col-span-8">
                <h1 class="text-4xl font-extrabold my-4 leading-tight"><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="flex justify-between items-center border-t border-b py-3 my-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <div class="font-bold"><?php echo htmlspecialchars($article['author_name'] ?? 'NewsHub Staff'); ?></div>
                            <div class="text-gray-500 text-sm"><?php echo date('F d, Y', strtotime($article['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($article_url); ?>" target="_blank" class="bg-facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($article_title); ?>&url=<?php echo urlencode($article_url); ?>" target="_blank" class="bg-twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($article_title . ' ' . $article_url); ?>" target="_blank" class="bg-whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="mailto:?subject=<?php echo urlencode($article_title); ?>&body=Check out this article: <?php echo urlencode($article_url); ?>" class="bg-email"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>

                <img src="uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-auto rounded-lg my-6">
                <div class="article-content text-lg leading-relaxed"><?php echo $article['content']; ?></div>

                <?php if (!empty($tags)): ?>
                <div class="mt-8 pt-4 border-t">
                    <h4 class="font-bold mb-2">Tags:</h4>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                            <a href="tag.php?id=<?php echo $tag['id']; ?>" class="bg-gray-200 text-gray-700 text-sm font-medium px-3 py-1 rounded-full hover:bg-gray-300"><?php echo htmlspecialchars($tag['name']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($related_articles)): ?>
                <div class="mt-12">
                    <h2 class="section-title pb-2 mb-4">More Stories</h2>
                    <?php
                        $grid_articles = array_slice($related_articles, 0, 3);
                        $list_articles = array_slice($related_articles, 3);
                    ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach($grid_articles as $related): ?>
                            <a href="<?php echo createSlug($related['title']); ?>-<?php echo $related['id']; ?>" class="more-stories-card block group">
                                <img src="uploads/<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="w-full h-32 object-cover rounded">
                                <h3 class="font-bold text-sm mt-2 group-hover:text-red-600"><?php echo htmlspecialchars($related['title']); ?></h3>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if(!empty($list_articles)): ?>
                    <div class="mt-6 border-t pt-4">
                        <?php foreach($list_articles as $related): ?>
                            <a href="<?php echo createSlug($related['title']); ?>-<?php echo $related['id']; ?>" class="block font-semibold py-2 border-b hover:text-red-600"><?php echo htmlspecialchars($related['title']); ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="comment-section">
                    <h2 class="text-2xl font-bold mb-6"><?php echo count($comments); ?> Comments</h2>
                    <div class="space-y-6">
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <p class="comment-author"><?php echo htmlspecialchars($comment['name']); ?></p>
                                    <p class="comment-date"><?php echo date('F d, Y \a\t h:i A', strtotime($comment['created_at'])); ?></p>
                                    <p class="comment-body"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No comments yet. Be the first to share your thoughts!</p>
                        <?php endif; ?>
                    </div>
                    <div class="mt-10">
                        <h3 class="text-2xl font-bold mb-4">Leave a Reply</h3>
                        <?php if ($comment_message): ?>
                            <div class="alert-box <?php echo $comment_message_type === 'success' ? 'alert-success' : 'alert-error'; ?>">
                                <?php echo htmlspecialchars($comment_message); ?>
                            </div>
                        <?php endif; ?>
                        <form action="save_comment.php" method="POST" class="comment-form">
                            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="text" name="name" placeholder="Your Name" required>
                                <input type="email" name="email" placeholder="Your Email" required>
                            </div>
                            <textarea name="comment" rows="5" placeholder="Your Comment..." required></textarea>
                            <button type="submit" class="bg-red-600 text-white font-bold py-2 px-4 rounded hover:bg-red-700">Post Comment</button>
                        </form>
                    </div>
                </div>

            </div>

            <aside class="col-span-12 lg:col-span-4">
                <div class="sticky top-8">
                    <div class="sidebar-widget">
                        <h3 class="section-title text-base mb-2 pb-2">Trending</h3>
                        <?php foreach($trending_topics as $topic): ?>
                            <a href="category.php?id=<?php echo $topic['id']; ?>" class="block py-2 border-b border-gray-200 text-sm font-medium uppercase hover:text-red-600"><?php echo htmlspecialchars($topic['name']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </div>
    <?php else: ?>
        <div class="text-center py-20">
            <h1 class="text-3xl font-bold">Article Not Found</h1>
            <a href="index.php" class="mt-6 inline-block bg-red-600 text-white font-bold py-3 px-6 rounded">Go to Homepage</a>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'footer.php'; ?>
