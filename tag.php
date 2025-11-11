<?php 
require_once 'includes/db.php';
require_once 'header.php'; 

$tag_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$articles = [];
$tag_name = "Unknown Tag";

if ($tag_id > 0) {
    // Get tag name from the database
    $stmt = $conn->prepare("SELECT name FROM tags WHERE id = ?");
    $stmt->bind_param("i", $tag_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $tag_name = $result->fetch_assoc()['name'];
    }
    $stmt->close();

    // Get all articles that have this tag
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.image, a.content, a.created_at
        FROM articles a
        JOIN article_tag_map m ON a.id = m.article_id
        WHERE m.tag_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->bind_param("i", $tag_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    $stmt->close();
}
?>
<head>
    <title>Articles tagged "<?php echo htmlspecialchars($tag_name); ?>" - NewsHub</title>
</head>

<main class="container mx-auto my-8 px-4">
    <h1 class="section-title pb-2 mb-4">
        Articles Tagged With: "<?php echo htmlspecialchars($tag_name); ?>"
    </h1>

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
            <p class="col-span-full text-center text-gray-500 py-10">No articles found with this tag.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'footer.php'; // This line fixes the layout issue ?>