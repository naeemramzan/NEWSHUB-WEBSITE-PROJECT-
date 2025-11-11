<?php 
require_once 'includes/db.php';
require_once 'header.php'; 

// Fetch the Category ID for 'Blogs'
$cat_stmt = $conn->prepare("SELECT id FROM categories WHERE name = 'Blogs' LIMIT 1");
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$blog_category = $cat_result->fetch_assoc();
$cat_stmt->close();

$blogs = [];
if ($blog_category) {
    // Fetch all articles belonging to the 'Blogs' category
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.image, a.content, a.created_at, ad.full_name as author_name
        FROM articles a
        LEFT JOIN admins ad ON a.admin_id = ad.id
        WHERE a.category_id = ? 
        ORDER BY a.created_at DESC
    ");
    $stmt->bind_param("i", $blog_category['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
    $stmt->close();
}
?>
<head>
    
    <title>Blogs - NewsHub</title>
    
    <!-- FAVICON -->
    <link rel="icon" href="images/logo.png" type="image/png"> 
</head>

<main class="container mx-auto my-8 px-4">
    <h1 class="section-title pb-2 mb-4">Our Blog</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($blogs)): ?>
            <?php foreach ($blogs as $blog): ?>
                <a href="single_article.php?id=<?php echo $blog['id']; ?>" class="block border rounded-lg overflow-hidden shadow hover:shadow-lg transition-shadow">
                    <img src="uploads/<?php echo htmlspecialchars($blog['image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-lg leading-tight"><?php echo htmlspecialchars($blog['title']); ?></h3>
                        <p class="text-gray-500 text-sm mt-2">By <?php echo htmlspecialchars($blog['author_name'] ?? 'NewsHub Team'); ?> on <?php echo date('M d, Y', strtotime($blog['created_at'])); ?></p>
                        <p class="text-gray-600 text-sm mt-2"><?php echo htmlspecialchars(substr(strip_tags($blog['content']), 0, 100)) . '...'; ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-full text-center">No blog posts found.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>