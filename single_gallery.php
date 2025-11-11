<?php 
require_once 'includes/db.php';
require_once 'header.php'; 

// --- DATA FETCHING ---
$gallery_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$gallery = null;
$images = [];

if ($gallery_id > 0) {
    // 1. Get the gallery's main details (title, description)
    $stmt = $conn->prepare("SELECT title, description FROM galleries WHERE id = ?");
    $stmt->bind_param("i", $gallery_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $gallery = $result->fetch_assoc();
    }
    $stmt->close();

    // 2. Get all images associated with this gallery
    if ($gallery) {
        $stmt = $conn->prepare("SELECT image_filename, caption FROM gallery_images WHERE gallery_id = ? ORDER BY id ASC");
        $stmt->bind_param("i", $gallery_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        $stmt->close();
    }
}
?>
<head>
    <title><?php echo $gallery ? htmlspecialchars($gallery['title']) : 'Gallery Not Found'; ?> - NewsHub</title>
</head>

<main class="container mx-auto my-8 px-4">
    <?php if ($gallery): ?>
        <h1 class="section-title pb-2 mb-2"><?php echo htmlspecialchars($gallery['title']); ?></h1>
        <?php if (!empty($gallery['description'])): ?>
            <p class="text-lg text-gray-600 mb-8"><?php echo htmlspecialchars($gallery['description']); ?></p>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $image): ?>
                    <div>
                        <a href="uploads/galleries/<?php echo htmlspecialchars($image['image_filename']); ?>" target="_blank">
                            <img src="uploads/galleries/<?php echo htmlspecialchars($image['image_filename']); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>" class="w-full h-48 object-cover rounded-lg shadow-md hover:shadow-xl transition-shadow">
                        </a>
                        <?php if (!empty($image['caption'])): ?>
                            <p class="text-sm text-center text-gray-700 mt-2"><?php echo htmlspecialchars($image['caption']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="col-span-full text-center">There are no images in this gallery yet.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-20">
            <h1 class="text-3xl font-bold">Gallery Not Found</h1>
            <p class="mt-4">The gallery you are looking for does not exist.</p>
            <a href="galleries.php" class="mt-6 inline-block bg-red-600 text-white font-bold py-3 px-6 rounded">Back to Galleries</a>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'footer.php'; ?>