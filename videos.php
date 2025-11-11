<?php 
require_once 'includes/db.php';
require_once 'header.php'; 

// --- DATA FETCHING & FILTERING LOGIC ---
$video_categories_result = $conn->query("SELECT * FROM video_categories ORDER BY name ASC");

$selected_category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$page_title = 'All Videos';
$videos_to_display = [];

if ($selected_category_id > 0) {
    // --- FILTERED VIEW: Get videos for a specific category ---
    $cat_stmt = $conn->prepare("SELECT name FROM video_categories WHERE id = ?");
    $cat_stmt->bind_param("i", $selected_category_id);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    if($cat = $cat_result->fetch_assoc()){
        $page_title = $cat['name'];
    }
    $cat_stmt->close();

    $vid_stmt = $conn->prepare("
        SELECT v.id, v.title, v.youtube_embed_code FROM videos v
        JOIN video_category_map vcm ON v.id = vcm.video_id
        WHERE vcm.category_id = ?
        ORDER BY v.created_at DESC
    ");
    $vid_stmt->bind_param("i", $selected_category_id);
    $vid_stmt->execute();
    $result = $vid_stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $videos_to_display[] = $row;
    }
    $vid_stmt->close();
} else {
    // --- DEFAULT VIEW: Get all recent videos, grouped by category ---
    $all_videos_result = $conn->query("
        SELECT v.id, v.title, v.youtube_embed_code, vc.name as category_name, vc.id as category_id
        FROM videos v
        JOIN video_category_map vcm ON v.id = vcm.video_id
        JOIN video_categories vc ON vcm.category_id = vc.id
        ORDER BY vc.name ASC, v.created_at DESC
    ");
    // Group videos by category using PHP for efficiency
    while($video = $all_videos_result->fetch_assoc()) {
        $videos_to_display[$video['category_name']][] = $video;
    }
}

?>
<head>
    <title><?php echo htmlspecialchars($page_title); ?> - NewsHub Videos</title>
</head>

<main class="container mx-auto my-8 px-4">
    <div class="grid grid-cols-12 gap-8">
        <aside class="col-span-12 lg:col-span-3">
            <div class="sticky top-8 bg-gray-100 p-4 rounded-lg">
                <h3 class="font-bold text-lg mb-4 border-b pb-2">Video Categories</h3>
                <div class="space-y-2">
                    <a href="videos.php" class="block px-3 py-2 rounded font-semibold <?php echo ($selected_category_id == 0) ? 'bg-red-600 text-white' : 'hover:bg-gray-200'; ?>">
                        All Videos
                    </a>
                    <?php if ($video_categories_result && $video_categories_result->num_rows > 0): ?>
                        <?php while($category = $video_categories_result->fetch_assoc()): ?>
                            <a href="videos.php?category_id=<?php echo $category['id']; ?>" class="block px-3 py-2 rounded font-semibold <?php echo ($selected_category_id == $category['id']) ? 'bg-red-600 text-white' : 'hover:bg-gray-200'; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <div class="col-span-12 lg:col-span-9">
            <?php if ($selected_category_id > 0): ?>
                <h1 class="section-title pb-2 mb-4"><?php echo htmlspecialchars($page_title); ?></h1>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if (!empty($videos_to_display)): ?>
                        <?php foreach($videos_to_display as $video): ?>
                            <div class="group">
                                <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden border-2 border-transparent group-hover:border-red-500">
                                    <?php echo $video['youtube_embed_code']; ?>
                                </div>
                                <h4 class="mt-2 font-semibold text-gray-800 group-hover:text-red-600"><?php echo htmlspecialchars($video['title']); ?></h4>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="col-span-full text-gray-500">No videos found in this category.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php if (!empty($videos_to_display)): ?>
                    <?php foreach($videos_to_display as $category_name => $videos): ?>
                        <section class="mb-12">
                            <h2 class="section-title pb-2 mb-4"><?php echo htmlspecialchars($category_name); ?></h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach(array_slice($videos, 0, 3) as $video): // Show up to 3 videos per category on main page ?>
                                    <div class="group">
                                        <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden border-2 border-transparent group-hover:border-red-500">
                                            <?php echo $video['youtube_embed_code']; ?>
                                        </div>
                                        <h4 class="mt-2 font-semibold text-gray-800 group-hover:text-red-600"><?php echo htmlspecialchars($video['title']); ?></h4>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                <?php else: ?>
                     <p class="col-span-full text-gray-500">No videos have been posted yet.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>