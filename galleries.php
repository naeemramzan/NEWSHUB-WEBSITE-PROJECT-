<?php 
require_once 'includes/db.php';
require_once 'header.php'; 

// Fetch all galleries and a preview image for each
$galleries_result = $conn->query("
    SELECT g.id, g.title, g.description, 
           (SELECT gi.image_filename FROM gallery_images gi WHERE gi.gallery_id = g.id ORDER BY gi.id ASC LIMIT 1) as preview_image
    FROM galleries g
    ORDER BY g.created_at DESC
");
?>
<head>
    <title>Image Galleries - NewsHub</title>
</head>

<main class="container mx-auto my-8 px-4">
    <h1 class="section-title pb-2 mb-4">Image Galleries</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if ($galleries_result->num_rows > 0): ?>
            <?php while($gallery = $galleries_result->fetch_assoc()): ?>
                <a href="single_gallery.php?id=<?php echo $gallery['id']; ?>" class="block border rounded-lg overflow-hidden shadow hover:shadow-lg transition-shadow">
                    <?php 
                    $preview_image = !empty($gallery['preview_image']) ? 'uploads/galleries/' . htmlspecialchars($gallery['preview_image']) : 'https://via.placeholder.com/400x300.png?text=No+Image';
                    ?>
                    <img src="<?php echo $preview_image; ?>" alt="<?php echo htmlspecialchars($gallery['title']); ?>" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-lg leading-tight"><?php echo htmlspecialchars($gallery['title']); ?></h3>
                        <p class="text-gray-600 text-sm mt-2"><?php echo htmlspecialchars(substr($gallery['description'], 0, 100)) . '...'; ?></p>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="col-span-full text-center">No image galleries have been created yet.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>