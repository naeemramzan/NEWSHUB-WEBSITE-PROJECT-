<?php
// FILE: index.php (FINAL VERSION - "Load More" button added & SEO links)

// --- 1. SETUP & DATABASE CONNECTION ---
require_once 'includes/db.php';
date_default_timezone_set('Asia/Karachi');

// --- NEW FUNCTION: Create URL-friendly slugs from article titles ---
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/\s+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// --- 2. DATA FETCHING (STABLE) ---
function fetchArticles($conn, $sql, $params = [], $types = "") {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return [];
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

$top_news_main = fetchArticles($conn, "SELECT id, title, image, content FROM articles WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 1");
$top_news_list = fetchArticles($conn, "SELECT id, title FROM articles WHERE is_featured = 0 ORDER BY created_at DESC LIMIT 5");
$editors_picks = fetchArticles($conn, "SELECT id, title, image FROM articles ORDER BY view_count DESC LIMIT 5");
$trending_topics = fetchArticles($conn, "SELECT id, name FROM categories ORDER BY id DESC LIMIT 5");
$latest_news = fetchArticles($conn, "SELECT id, title, image FROM articles ORDER BY created_at DESC LIMIT 6");
$business_articles = fetchArticles($conn, "SELECT a.id, a.title, a.image FROM articles a JOIN categories c ON a.category_id = c.id WHERE c.name = ? ORDER BY a.created_at DESC LIMIT 5", ['Business'], 's');
$lifestyle_articles = fetchArticles($conn, "SELECT a.id, a.title, a.image FROM articles a JOIN categories c ON a.category_id = c.id WHERE c.name = ? ORDER BY a.created_at DESC LIMIT 5", ['Lifestyle'], 's');
$scitech_articles = fetchArticles($conn, "SELECT a.id, a.title, a.image FROM articles a JOIN categories c ON a.category_id = c.id WHERE c.name = ? ORDER BY a.created_at DESC LIMIT 5", ['Technology'], 's');
$blogs_articles = fetchArticles($conn, "SELECT a.id, a.title, a.image FROM articles a JOIN categories c ON a.category_id = c.id WHERE c.name = ? ORDER BY a.created_at DESC LIMIT 5", ['Blogs'], 's');
$nav_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3637721699586342"
     crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsHub - Your Source for Breaking News</title> 
    
  
    <!-- FAVICON -->
    <link rel="icon" href="images/logo.png" type="image/png"> 
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #fff; }
        .header-top { background-color: #222222; }
        .logo-main-text { font-size: 1.5rem; font-weight: 900; }
        .logo-accent-text { background-color: #e52d27; padding: 0 5px; }
        .main-nav a.active { border-bottom: 3px solid #e52d27; color: #e52d27; }
        .main-nav a:hover { color: #e52d27; }
        .section-title { font-size: 1.25rem; font-weight: 900; text-transform: uppercase; border-bottom: 3px solid #333; }
        .section-title.red { border-color: #e52d27; }
        .top-news-main h2:hover, .top-news-list a:hover, .editors-pick-item:hover h3, .category-column .article-item:hover h3 { color: #e52d27; }
        .latest-news-section { background-color: #111; }
        .latest-news-card { background: #222; }
        .latest-news-card:hover h3 { color: #e52d27; }
        .load-more-btn {
            display: inline-block;
            background-color: #333;
            color: #fff;
            padding: 10px 30px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
            border: 2px solid #555;
            transition: all 0.2s ease;
        }
        .load-more-btn:hover {
            background-color: #e52d27;
            border-color: #e52d27;
        }
        .main-footer { background-color: #222; color: #aaa; }
        .footer-col a:hover { color: #fff; }
        .footer-bottom { background: #111; color: #777; }
        .mobile-menu-overlay {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-top py-2">
            <div class="container mx-auto px-4 flex justify-between items-center">
                <a href="index.php"><span class="logo-main-text text-white">NEWS<span class="logo-accent-text">HUB</span></span></a>
            </div>
        </div>
        <nav class="main-nav bg-white border-b border-gray-200">
            <div class="container mx-auto px-4 flex justify-between items-center">
                <div class="hidden lg:flex items-center">
                    <a href="index.php" class="active font-bold text-sm py-4 px-3">Home</a>
                    <?php
                    $nav_categories_result->data_seek(0);
                    while ($cat = $nav_categories_result->fetch_assoc()) {
                        echo '<a href="category.php?id=' . $cat['id'] . '" class="font-bold text-sm py-4 px-3">' . htmlspecialchars($cat['name']) . '</a>';
                    }
                    ?>
                </div>
                <a href="#" class="hidden lg:block p-4"><i class="fas fa-search"></i></a>
                <div class="lg:hidden">
                    <button id="mobile-menu-button" class="text-gray-600 p-4 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                </div>
            </div>
        </nav>
    </header>
    <div id="mobile-menu-overlay" class="mobile-menu-overlay hidden fixed inset-0 bg-black bg-opacity-90 z-50">
        <button id="mobile-menu-close" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
        <div class="w-full h-full flex flex-col justify-center items-center">
            <a href="index.php" class="text-white text-2xl font-bold py-3">Home</a>
            <?php
            $nav_categories_result->data_seek(0);
            while ($cat = $nav_categories_result->fetch_assoc()) {
                echo '<a href="category.php?id=' . $cat['id'] . '" class="text-white text-2xl font-bold py-3">' . htmlspecialchars($cat['name']) . '</a>';
            }
            ?>
        </div>
    </div>
    <main class="container mx-auto my-8 px-4">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 md:col-span-6 lg:col-span-6">
                <h2 class="section-title pb-2 mb-4">Top News</h2>
                <?php if (!empty($top_news_main[0])): $main_article = $top_news_main[0]; ?>
                    <div class="top-news-main">
                        <a href="<?php echo createSlug($main_article['title']); ?>-<?php echo $main_article['id']; ?>" class="block">
                            <img src="uploads/<?php echo htmlspecialchars($main_article['image']); ?>" alt="<?php echo htmlspecialchars($main_article['title']); ?>" class="w-full h-auto">
                            <h2 class="mt-2 text-2xl font-bold leading-tight"><?php echo htmlspecialchars($main_article['title']); ?></h2>
                            <p class="text-gray-600 mt-2"><?php echo htmlspecialchars(substr(strip_tags($main_article['content']), 0, 150)) . '...'; ?></p>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="top-news-list mt-4">
                    <?php foreach($top_news_list as $item): ?>
                        <a href="<?php echo createSlug($item['title']); ?>-<?php echo $item['id']; ?>" class="block py-2 border-b border-gray-200 font-medium"><?php echo htmlspecialchars($item['title']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-3">
                <h2 class="section-title red pb-2 mb-4">Editors Pick</h2>
                <?php foreach($editors_picks as $item): ?>
                    <a href="<?php echo createSlug($item['title']); ?>-<?php echo $item['id']; ?>" class="editors-pick-item flex items-center gap-4 py-2 border-b border-gray-200">
                        <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-24 h-16 object-cover flex-shrink-0">
                        <h3 class="font-medium text-sm leading-tight"><?php echo htmlspecialchars($item['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="col-span-12 md:col-span-12 lg:col-span-3">
                <div class="trending-box border border-gray-200 p-4">
                    <h3 class="section-title text-base mb-2 pb-2">Trending</h3>
                    <?php foreach($trending_topics as $topic): ?>
                        <a href="category.php?id=<?php echo $topic['id']; ?>" class="block py-2 border-b border-gray-200 text-sm font-medium uppercase"><?php echo htmlspecialchars($topic['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
    <section class="latest-news-section py-8 mt-8">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-white border-red-600 pb-2 mb-4">Latest News</h2>
            <div id="latest-news-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <?php foreach($latest_news as $item): ?>
                    <a href="<?php echo createSlug($item['title']); ?>-<?php echo $item['id']; ?>" class="latest-news-card block">
                        <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-32 object-cover">
                        <h3 class="p-3 font-medium text-sm leading-snug text-white"><?php echo htmlspecialchars($item['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-8">
                <button id="load-more-button" class="load-more-btn">Load More</button>
            </div>
        </div>
    </section>
    <section class="container mx-auto my-8 px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php
            $category_cols = [ 'Business' => $business_articles, 'Lifestyle' => $lifestyle_articles, 'Science & Technology' => $scitech_articles, 'Blogs' => $blogs_articles ];
            foreach ($category_cols as $cat_name => $articles):
            ?>
            <div class="category-column">
                <h2 class="section-title pb-2 mb-2"><?php echo $cat_name; ?></h2>
                <?php foreach ($articles as $index => $article): ?>
                    <a href="<?php echo createSlug($article['title']); ?>-<?php echo $article['id']; ?>" class="article-item block py-2 border-b border-gray-200">
                        <?php if ($index === 0 && !empty($article['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="" class="w-full h-auto mb-2">
                        <?php endif; ?>
                        <h3 class="font-medium leading-snug text-sm"><?php echo htmlspecialchars($article['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <footer class="main-footer pt-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="footer-col">
                    <h4 class="mb-4 text-white font-bold uppercase">About Us</h4>
                    <p class="text-sm">NewsHub brings you 24/7 Live Streaming, Headlines, Bulletins, Talk Shows, and much more.</p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white" aria-label="Follow us on Facebook">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white" aria-label="Follow us on Twitter">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white" aria-label="Follow us on Instagram">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white" aria-label="Follow us on YouTube">
                            <i class="fab fa-youtube fa-lg"></i>
                        </a>
                        <a href="https://whatsapp.com/channel/0029Vb5m1sFDZ4LTBxoVTu2Y" class="text-gray-400 hover:text-white" aria-label="Join our WhatsApp Channel">
                            <i class="fab fa-whatsapp fa-lg"></i>
                        </a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4 class="mb-4 text-white font-bold uppercase">Corporate</h4>
                    <a href="feedback.php" class="text-sm block mb-2">Feedback</a>
                    <a href="contact.php" class="text-sm block mb-2">Contact Us</a>
                    <a href="about.php" class="text-sm block mb-2">About Us</a>
                    <a href="terms.php" class="text-sm block mb-2">Terms & Conditions</a>
                    <a href="privacypolicy.php" class="text-sm block mb-2 hover:text-white"> Privacy Policy</a>
                </div>
                <div class="footer-col">
                    <h4 class="mb-4 text-white font-bold uppercase">Our Network</h4>
                    <a href="#" class="text-sm block mb-2">NewsHub News</a>
                    <a href="#" class="text-sm block mb-2">NewsHub Digital</a>
                </div>
                <div class="footer-col">
                    <h4 class="mb-4 text-white font-bold uppercase">Download Now!</h4>
                    <a href="#" class="text-sm block mb-2">App Store</a>
                    <a href="#" class="text-sm block mb-2">Google Play</a>
                    <a href="#" class="text-sm block mb-2">AppGallery</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom mt-8 py-4">
            <p class="text-center text-xs">&copy; <?php echo date('Y'); ?> NewsHub. All Rights Reserved.</p>
        </div>
    </footer>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadMoreButton = document.getElementById('load-more-button');
        const latestNewsContainer = document.getElementById('latest-news-container');
        let offset = 6;
        const limit = 6;
        if (loadMoreButton && latestNewsContainer) {
            loadMoreButton.addEventListener('click', function(e) {
                e.preventDefault();
                fetch(`load_more.php?offset=${offset}&limit=${limit}`)
                    .then(response => response.text())
                    .then(html => {
                        if (html.trim() !== '') {
                            latestNewsContainer.insertAdjacentHTML('beforeend', html);
                            offset += limit;
                        } else {
                            loadMoreButton.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Error loading more articles:', error));
            });
        }
        const menuButton = document.getElementById('mobile-menu-button');
        const menuOverlay = document.getElementById('mobile-menu-overlay');
        const closeButton = document.getElementById('mobile-menu-close');
        function openMenu() {
            menuOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            menuOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
        menuButton.addEventListener('click', openMenu);
        closeButton.addEventListener('click', closeMenu);
        menuOverlay.addEventListener('click', function(event) {
            if (event.target === menuOverlay) {
                closeMenu();
            }
        });
    });
    </script>
</body>
</html>
