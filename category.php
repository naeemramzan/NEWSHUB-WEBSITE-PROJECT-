<?php 
// Establish DB Connection and load the universal header
require_once 'includes/db.php';
require_once 'header.php'; 

// --- DATA FETCHING for this page ---
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$articles = [];
$category_name = "Unknown Category";

if ($category_id > 0) {
    // Get the name of the category for the page title
    $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $category_name = $result->fetch_assoc()['name'];
    }
    $stmt->close();

    // Get all articles that belong to this category
    $stmt = $conn->prepare("
        SELECT id, title, image, content, created_at 
        FROM articles 
        WHERE category_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    $stmt->close();
}
?>
<head>
    <title><?php echo htmlspecialchars($category_name); ?> - NewsHub</title>
    
    <!-- FAVICON -->
    
    <!-- FAVICON -->
    <link rel="icon" href="images/logo.png" type="image/png"> 
</head>

<main class="container mx-auto my-8 px-4">
    <h1 class="section-title pb-2 mb-4"><?php echo htmlspecialchars($category_name); ?></h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $article): ?>
                <a href="single_article.php?id=<?php echo $article['id']; ?>" class="block border rounded-lg overflow-hidden shadow hover:shadow-lg transition-shadow">
                    <img src="uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-lg leading-tight"><?php echo htmlspecialchars($article['title']); ?></h3>
                        <p class="text-gray-600 text-sm mt-2"><?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 100)) . '...'; ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-full text-center text-gray-500 py-10">No articles found in this category.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'footer.php'; // Includes the new footer ?>