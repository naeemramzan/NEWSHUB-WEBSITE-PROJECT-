<?php
// FILE: author.php
// Redesigned to match the ARY News style with multiple author-specific sections.

// --- 1. SETUP & DATABASE CONNECTION ---
require_once 'includes/db.php';

// --- Input Validation ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$author_id = $_GET['id'];

// --- Fetch Author's Details ---
$author_stmt = $conn->prepare("SELECT full_name, bio FROM admins WHERE id = ?");
$author_stmt->bind_param("i", $author_id);
$author_stmt->execute();
$author_result = $author_stmt->get_result();

if ($author_result->num_rows == 0) {
    // A simple header for the error page
    echo '<!DOCTYPE html><html lang="en"><head><title>Error</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-900 text-white p-8"><p class="text-center text-red-500">Author not found.</p></body></html>';
    exit;
}
$author = $author_result->fetch_assoc();
$author_stmt->close();

// --- Fetch all articles by this author for the main "Top News" section ---
$top_articles_stmt = $conn->prepare("
    SELECT a.id, a.title, a.image, a.content
    FROM articles a
    WHERE a.author_id = ?
    ORDER BY a.created_at DESC
    LIMIT 7
");
$top_articles_stmt->bind_param("i", $author_id);
$top_articles_stmt->execute();
$top_articles_result = $top_articles_stmt->get_result();

// --- Fetch articles for the "Blogs" section by this author ---
$blogs_articles_stmt = $conn->prepare("
    SELECT a.id, a.title, a.image
    FROM articles a JOIN categories c ON a.category_id = c.id
    WHERE a.author_id = ? AND c.name = 'Blogs'
    ORDER BY a.created_at DESC
    LIMIT 4
");
$blogs_articles_stmt->bind_param("i", $author_id);
$blogs_articles_stmt->execute();
$blogs_articles_result = $blogs_articles_stmt->get_result();


// --- Fetch latest videos (general, not author-specific as per current schema) ---
$videos_result = $conn->query("SELECT id, title, youtube_embed_code FROM videos ORDER BY created_at DESC LIMIT 6");

// --- Fetch articles for International and Pakistan sections by this author ---
$international_articles_stmt = $conn->prepare("
    SELECT a.id, a.title
    FROM articles a JOIN categories c ON a.category_id = c.id
    WHERE a.author_id = ? AND c.name = 'International'
    ORDER BY a.created_at DESC
    LIMIT 7
");
$international_articles_stmt->bind_param("i", $author_id);
$international_articles_stmt->execute();
$international_articles_result = $international_articles_stmt->get_result();

$pakistan_articles_stmt = $conn->prepare("
    SELECT a.id, a.title, a.image
    FROM articles a JOIN categories c ON a.category_id = c.id
    WHERE a.author_id = ? AND c.name = 'Pakistan'
    ORDER BY a.created_at DESC
    LIMIT 1
");
$pakistan_articles_stmt->bind_param("i", $author_id);
$pakistan_articles_stmt->execute();
$pakistan_articles_result = $pakistan_articles_stmt->get_result();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles by <?php echo htmlspecialchars($author['full_name']); ?> - NewsHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #111827; color: #f9fafb; }
        .section-title {
            font-size: 1.25rem; /* 20px */
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .nav-link {
            padding: 0.5rem 1rem;
            color: #d1d5db;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: #ffffff;
            border-bottom-color: #ef4444; /* red-500 */
        }
         .video-embed-container iframe {
            width: 100%;
            height: 100%;
            aspect-ratio: 16 / 9;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">

    <!-- START: HEADER & NAVIGATION -->
    <header class="bg-black shadow-lg sticky top-0 z-50">
        <div class="max-w-screen-xl mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <a href="index.php" class="text-2xl font-extrabold border-2 border-white px-2 py-1">NEWS<span class="bg-red-600 text-white px-1">HUB</span></a>
                <nav class="hidden lg:flex items-center space-x-2">
                    <a href="index.php" class="nav-link">HOME</a>
                    <?php
                        $nav_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 9");
                        if ($nav_categories_result && $nav_categories_result->num_rows > 0) {
                            while ($cat = $nav_categories_result->fetch_assoc()) {
                                echo '<a href="category.php?id=' . $cat['id'] . '" class="nav-link">' . strtoupper(htmlspecialchars($cat['name'])) . '</a>';
                            }
                        }
                    ?>
                </nav>
                 <div class="flex items-center">
                    <button class="text-white hover:text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <!-- END: HEADER & NAVIGATION -->

    <main class="max-w-screen-xl mx-auto px-4 py-8">

        <!-- Author Profile Section -->
        <div class="mb-12 text-center">
            <h1 class="text-4xl font-extrabold mb-2"><?php echo htmlspecialchars($author['full_name']); ?></h1>
            <?php if (!empty($author['bio'])): ?>
                <p class="text-lg text-gray-400 max-w-3xl mx-auto"><?php echo htmlspecialchars($author['bio']); ?></p>
            <?php endif; ?>
        </div>

        <!-- LATEST NEWS BY AUTHOR Section -->
        <h2 class="section-title">Latest News by <?php echo htmlspecialchars($author['full_name']); ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
             <?php
             if ($top_articles_result && $top_articles_result->num_rows > 0):
                $articles_array = [];
                while($row = $top_articles_result->fetch_assoc()) { $articles_array[] = $row; }

                if(count($articles_array) > 0):
                    $first_article = array_shift($articles_array);
                ?>
                    <div class="lg:col-span-2 relative group">
                        <a href="single_article.php?id=<?php echo $first_article['id']; ?>">
                            <img src="uploads/<?php echo $first_article['image']; ?>" class="w-full h-80 object-cover rounded-lg">
                            <div class="absolute bottom-0 left-0 bg-gradient-to-t from-black to-transparent w-full h-1/2 p-4 flex items-end rounded-b-lg">
                                <h3 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($first_article['title']); ?></h3>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                     <?php foreach($articles_array as $article): ?>
                        <div class="relative group">
                             <a href="single_article.php?id=<?php echo $article['id']; ?>">
                                <img src="uploads/<?php echo $article['image']; ?>" class="w-full h-36 object-cover rounded-lg">
                                 <div class="absolute bottom-0 left-0 bg-gradient-to-t from-black to-transparent w-full h-1/2 p-2 flex items-end rounded-b-lg">
                                    <h4 class="text-md font-semibold text-white leading-tight"><?php echo htmlspecialchars($article['title']); ?></h4>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="col-span-full text-center text-gray-500">This author has not published any articles yet.</p>
            <?php endif; ?>
        </div>

         <!-- BLOGS Section -->
        <h2 class="section-title">Blogs by <?php echo htmlspecialchars($author['full_name']); ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
             <?php while($article = $blogs_articles_result->fetch_assoc()): ?>
                <a href="single_article.php?id=<?php echo $article['id']; ?>" class="flex items-start space-x-3 group">
                    <img src="uploads/<?php echo $article['image']; ?>" class="w-24 h-16 object-cover rounded-md flex-shrink-0">
                    <h3 class="font-semibold text-gray-200 group-hover:text-red-500"><?php echo htmlspecialchars($article['title']); ?></h3>
                </a>
            <?php endwhile; ?>
        </div>

         <!-- LATEST VIDEOS Section -->
        <h2 class="section-title">Latest Videos</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-12">
            <?php while($video = $videos_result->fetch_assoc()): ?>
                <div class="group">
                    <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden border-2 border-transparent group-hover:border-red-500 video-embed-container">
                        <?php echo $video['youtube_embed_code']; ?>
                    </div>
                    <h4 class="mt-2 text-sm font-semibold text-gray-300 group-hover:text-white"><?php echo htmlspecialchars($video['title']); ?></h4>
                </div>
            <?php endwhile; ?>
        </div>

         <!-- INTERNATIONAL & PAKISTAN Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-1">
                <h2 class="section-title">International</h2>
                <ul class="space-y-3">
                    <?php while($article = $international_articles_result->fetch_assoc()): ?>
                         <li><a href="single_article.php?id=<?php echo $article['id']; ?>" class="font-medium hover:text-red-500"><?php echo htmlspecialchars($article['title']); ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="md:col-span-2">
                 <h2 class="section-title">Pakistan</h2>
                 <?php if($pakistan_article_main = $pakistan_articles_result->fetch_assoc()): ?>
                    <a href="single_article.php?id=<?php echo $pakistan_article_main['id']; ?>" class="group">
                         <img src="uploads/<?php echo $pakistan_article_main['image']; ?>" class="w-full h-64 object-cover rounded-lg">
                         <h3 class="text-xl font-bold mt-2 group-hover:text-red-500"><?php echo htmlspecialchars($pakistan_article_main['title']); ?></h3>
                    </a>
                 <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- START: FOOTER -->
    <footer class="bg-black mt-12 border-t border-gray-800">
        <div class="max-w-screen-xl mx-auto py-8 px-4 text-center">
             <a href="index.php" class="text-2xl font-extrabold border-2 border-white px-2 py-1 inline-block">NEWS<span class="bg-red-600 text-white px-1">HUB</span></a>
             <p class="text-gray-400 text-sm mt-4">&copy; <?php echo date('Y'); ?> NewsHub. All Rights Reserved.</p>
        </div>
    </footer>
    <!-- END: FOOTER -->

</body>
</html>
