<?php 
require_once 'includes/db.php';
require_once 'header.php'; 

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = null;
$related_articles = [];
$trending_topics = [];

if ($article_id > 0) {
    // Get article data
    $stmt = $conn->prepare("
        SELECT a.*, c.name as category_name, ad.full_name as author_name, ad.profile_image as author_image
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        LEFT JOIN admins ad ON a.admin_id = ad.id
        WHERE a.id = ?
    ");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $article = $result->fetch_assoc();
        $stmt->close();
        
        // Increment view count
        $update_stmt = $conn->prepare("UPDATE articles SET view_count = view_count + 1 WHERE id = ?");
        $update_stmt->bind_param("i", $article_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Get related articles from the same category
        $cat_id = $article['category_id'];
        $related_stmt = $conn->prepare("SELECT id, title, image FROM articles WHERE category_id = ? AND id != ? ORDER BY created_at DESC LIMIT 6");
        $related_stmt->bind_param("ii", $cat_id, $article_id);
        $related_stmt->execute();
        $related_result = $related_stmt->get_result();
        while($row = $related_result->fetch_assoc()){
            $related_articles[] = $row;
        }
        $related_stmt->close();
    }
}

// Fetch trending topics for sidebar
$trending_result = $conn->query("SELECT id, name FROM categories ORDER BY id DESC LIMIT 5");
while($row = $trending_result->fetch_assoc()){
    $trending_topics[] = $row;
}
?>
<head>
    <title><?php echo $article ? htmlspecialchars($article['title']) : 'Article Not Found'; ?> - NewsHub</title>
    <meta name="description" content="<?php echo $article ? htmlspecialchars(substr(strip_tags($article['content']), 0, 160)) : ''; ?>">
</head>

<main class="container mx-auto my-8 px-4">
    <?php if ($article): ?>
        <div class="grid grid-cols-12 gap-8">
            <div class="col-span-12 lg:col-span-8">
                <h1 class="text-4xl font-extrabold my-4 leading-tight"><?php echo htmlspecialchars($article['title']); ?></h1>
                
                <div class="flex justify-between items-center border-t border-b py-3 my-4">
                    <div class="flex items-center">
                        <?php if (!empty($article['author_image'])): ?>
                             <img src="admin/uploads/authors/<?php echo htmlspecialchars($article['author_image']); ?>" class="rounded-full mr-3 w-10 h-10 object-cover" alt="<?php echo htmlspecialchars($article['author_name'] ?? 'Author'); ?>">
                         <?php else: ?>
                             <img src="https://via.placeholder.com/40/CCCCCC/FFFFFF?Text=A" class="rounded-full mr-3 w-10 h-10 object-cover" alt="Author Icon">
                         <?php endif; ?>
                        <div>
                            <div class="font-bold"><?php echo htmlspecialchars($article['author_name'] ?? 'NewsHub Staff'); ?></div>
                            <div class="text-gray-500 text-sm"><?php echo date('F d, Y', strtotime($article['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="share-buttons">
                        <a href="#" class="bg-facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="bg-twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="bg-whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="bg-email"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
                
                <img src="uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-auto rounded-lg my-6">

                <div class="article-content text-lg">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>

                <div class="mt-12">
                    <h2 class="section-title pb-2 mb-4">More Stories</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach($related_articles as $related): ?>
                            <a href="single_article.php?id=<?php echo $related['id']; ?>" class="more-stories-card block group">
                                <img src="uploads/<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="rounded">
                                <h3 class="font-bold mt-2 group-hover:text-red-600"><?php echo htmlspecialchars($related['title']); ?></h3>
                            </a>
                        <?php endforeach; ?>
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
            <p class="mt-4">The article you are looking for does not exist or may have been moved.</p>
            <a href="index.php" class="mt-6 inline-block bg-red-600 text-white font-bold py-3 px-6 rounded">Go to Homepage</a>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'footer.php'; ?>