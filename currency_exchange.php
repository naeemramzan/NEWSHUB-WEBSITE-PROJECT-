<?php
// FILE: currency_exchange.php
// Displays live/daily currency exchange rates, mimicking ARY News style.

require_once 'includes/db.php';

// --- Helper function (from index.php, included here for standalone use) ---
function getYouTubeVideoId($url) {
    preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
    return $matches[1] ?? null;
}

// You would typically fetch live/daily currency rates here from an API.
// For now, using static placeholder data.
$currency_rates = [
    [
        'currency_code' => 'USD',
        'currency_name' => 'US Dollar',
        'flag_emoji' => '🇺🇸',
        'rate_rs' => '283.53'
    ],
    [
        'currency_code' => 'SAR',
        'currency_name' => 'Saudi Riyal',
        'flag_emoji' => '🇸🇦',
        'rate_rs' => '75.61'
    ],
    [
        'currency_code' => 'AED',
        'currency_name' => 'UAE Dirham',
        'flag_emoji' => '🇦🇪',
        'rate_rs' => '77.20'
    ],
    [
        'currency_code' => 'AUD',
        'currency_name' => 'Australian Dollar',
        'flag_emoji' => '🇦🇺',
        'rate_rs' => '185.15'
    ],
    [
        'currency_code' => 'GBP',
        'currency_name' => 'UK Pound',
        'flag_emoji' => '🇬🇧',
        'rate_rs' => '389.09'
    ],
    [
        'currency_code' => 'CAD',
        'currency_name' => 'Canadian Dollar',
        'flag_emoji' => '🇨🇦',
        'rate_rs' => '207.05'
    ],
    [
        'currency_code' => 'JPY',
        'currency_name' => 'Japanese Yen',
        'flag_emoji' => '🇯🇵',
        'rate_rs' => '1.92'
    ],
    [
        'currency_code' => 'CNY',
        'currency_name' => 'Chinese Yuan',
        'flag_emoji' => '🇨🇳',
        'rate_rs' => '39.20'
    ],
    [
        'currency_code' => 'BHD',
        'currency_name' => 'Bahraini Dinar',
        'flag_emoji' => '🇧🇭',
        'rate_rs' => '753.80'
    ],
    [
        'currency_code' => 'KWD',
        'currency_name' => 'Kuwaiti Dinar',
        'flag_emoji' => '🇰🇼',
        'rate_rs' => '924.50'
    ],
    [
        'currency_code' => 'INR',
        'currency_name' => 'Indian Rupee',
        'flag_emoji' => '🇮🇳',
        'rate_rs' => '3.40'
    ],
    [
        'currency_code' => 'QAR',
        'currency_name' => 'Qatari Riyal',
        'flag_emoji' => '🇶🇦',
        'rate_rs' => '77.90'
    ],
];

// Optional: Fetch general nav categories
$nav_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dollar and Other Currency Rates Today in Pakistan - NewsHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #333; }

        /* General NewsHub Styles */
        .newshub-bg-dark { background-color: #1a1a1a; }
        .newshub-red-accent { background-color: #ef4444; }
        .newshub-red-text { color: #ef4444; }
        .newshub-border-red { border-bottom-color: #ef4444; }

        .header-top-bar {
            background-color: #0d121c;
            color: #a0a0a0;
            font-size: 0.75rem;
            padding: 0.25rem 0;
        }
        .header-top-bar a {
            color: #a0a0a0;
            padding: 0 0.5rem;
            border-right: 1px solid #333;
        }
        .header-top-bar a:last-child {
            border-right: none;
        }
        .header-top-bar .social-icon {
            color: #a0a0a0;
            transition: color 0.2s ease;
        }
        .header-top-bar .social-icon:hover {
            color: #fff;
        }

        .main-header {
            background-color: #1a1a1a;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .main-nav-link {
            padding: 0.75rem 1rem;
            color: #f9fafb;
            font-weight: 700;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        .main-nav-link:hover, .main-nav-link.active {
            color: #ef4444;
            border-bottom-color: #ef4444;
        }
        .logo-text-main {
            font-size: 1.875rem;
            font-weight: 900;
            color: white;
        }
        .logo-text-accent {
            background-color: #ef4444;
            color: white;
            padding: 0.1rem 0.3rem;
        }
        .search-button-header {
             color: white;
             transition: color 0.2s ease;
        }
        .search-button-header:hover {
            color: #ef4444;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 3px solid #ef4444;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            color: #1a1a1a;
        }

        .main-footer {
            background-color: #1a1a1a;
            color: #a0a0a0;
        }
        .footer-logo {
            border: 2px solid #a0a0a0;
            padding: 0.25rem 0.5rem;
            font-size: 1.5rem;
            font-weight: 900;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .footer-logo span {
            background-color: #ef4444;
            color: white;
            padding: 0.1rem 0.2rem;
        }
        .footer-link {
            color: #a0a0a0;
            transition: color 0.2s ease;
        }
        .footer-link:hover {
            color: #fff;
        }
        .footer-subscribe-input {
            background-color: #333;
            border: 1px solid #555;
            color: #fff;
        }
        .footer-subscribe-input::placeholder {
            color: #888;
        }
        .footer-subscribe-button {
            background-color: #ef4444;
            transition: background-color 0.2s ease;
        }
        .footer-subscribe-button:hover {
            background-color: #dc2626;
        }

        /* Currency Exchange Specific Styles */
        .currency-card {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s ease;
        }
        .currency-card:hover {
            transform: translateY(-2px);
        }
        .currency-card .flag {
            font-size: 2rem; /* Make flag emojis larger */
            line-height: 1; /* Adjust line height for better vertical alignment */
        }
        .currency-card .code {
            font-size: 1.125rem; /* text-lg */
            font-weight: 700;
            color: #1a1a1a;
        }
        .currency-card .name {
            font-size: 0.875rem; /* text-sm */
            color: #6b7280;
        }
        .currency-card .rate {
            font-size: 1.5rem; /* text-2xl */
            font-weight: 900;
            color: #000;
            margin-left: auto; /* Push rate to the right */
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="header-top-bar hidden lg:block">
        <div class="max-w-7xl mx-auto flex justify-between items-center px-4">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1 17h-2v-9h2v9zm3-12h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm3-12h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1zm0 2h-2v1h2v-1z"/></svg>
                <?php date_default_timezone_set('Asia/Karachi'); ?>
                <span><?php echo date('D, F j, Y'); ?> Karachi</span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="https://www.facebook.com/newshubofficial" target="_blank" class="social-icon"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v2.385z"/></svg></a>
                <a href="https://twitter.com/newshub_official" target="_blank" class="social-icon"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616v.064c0 2.298 1.634 4.218 3.791 4.66-1.05.286-2.206.34-3.268.125.658 1.946 2.564 3.328 4.816 3.362-1.794 1.407-4.062 2.242-6.52 2.242-1.018 0-1.986-.065-2.934-.173 2.32 1.503 5.078 2.382 8.046 2.382 9.648 0 14.941-8.219 14.538-15.526.982-.701 1.825-1.578 2.5-2.585z"/></svg></a>
                <a href="https://www.youtube.com/embed/" target="_blank" class="social-icon"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M21.5 8.3c0-1.1-.9-2-2-2.1-1.4-.2-6.7-.4-8.8-.4-2.2 0-7.5.2-8.8.4-1.1.1-2 .9-2.1 2-.2 1.4-.4 6.7-.4 8.8 0 2.2.2 7.5.4 8.8.1 1.1.9 2 2.1 2.1 1.4.2 6.7.4 8.8.4 2.2 0 7.5-.2 8.8-.4 1.1-.1 2-.9 2.1-2 .2-1.4.4-6.7.4-8.8 0-2.2-.2-7.5-.4-8.8zm-11.5 7.1v-6l5 3-5 3z"/></svg></a>
            </div>
        </div>
    </div>

    <header class="main-header sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <a href="index.php" class="flex-shrink-0">
                    <span class="logo-text-main">NEWS<span class="logo-text-accent">HUB</span></span>
                </a>
                <nav class="hidden lg:flex items-center space-x-2">
                    <a href="index.php" class="main-nav-link">HOME</a>
                    <?php
                    if ($nav_categories_result && $nav_categories_result->num_rows > 0) {
                        while ($cat = $nav_categories_result->fetch_assoc()) {
                            $link_href = ($cat['name'] == 'Blogs') ? 'blogs.php' : 'category.php?id=' . $cat['id'];
                            $is_active = '';
                            echo '<a href="' . htmlspecialchars($link_href) . '" class="main-nav-link ' . $is_active . '">' . strtoupper(htmlspecialchars($cat['name'])) . '</a>';
                        }
                    }
                    ?>
                    <a href="videos.php" class="main-nav-link">MULTIMEDIA</a>
                    <a href="#" class="main-nav-link">LIVE TV</a>
                </nav>
                 <div class="flex items-center">
                    <button class="search-button-header">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </div>
            <div class="hidden md:flex justify-start items-center py-1 border-t border-b border-gray-700 space-x-4">
                <a href="gold_rates.php" class="text-xs font-semibold py-1 px-2 bg-red-600 text-white rounded-sm hover:bg-red-700">GOLD RATES</a>
                <a href="currency_exchange.php" class="text-xs font-semibold py-1 px-2 bg-red-600 text-white rounded-sm hover:bg-red-700 active">CURRENCY EXCHANGE</a>
                <a href="psx_updates.php" class="text-xs font-semibold py-1 px-2 bg-gray-700 text-white rounded-sm hover:bg-gray-600">PSX</a>
                <a href="psl_2025.php" class="text-xs font-semibold py-1 px-2 bg-gray-700 text-white rounded-sm hover:bg-gray-600">PSL 2025</a>
            </div>
        </div>
    </header>
    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">DOLLAR AND OTHER CURRENCY RATES TODAY IN PAKISTAN</h1>
            <p class="text-gray-700 mb-8 leading-relaxed">
                The US Dollar (USD) rate is crucial in international trade as it determines the value of goods and services traded between countries.
                A fluctuation in the exchange rate can significantly impact the cost of imports and exports, affecting a country's trade balance and economy.
                For instance, a strong dollar can make imports more expensive for countries like Pakistan, leading to higher prices for consumers and increased costs for businesses.
            </p>
            <p class="text-gray-700 mb-8 leading-relaxed">
                Pakistanis frequently search for dollar, UAE Dirham (AED), and Saudi Riyal (SAR) rates due to the country's significant reliance on remittances from overseas workers,
                particularly in the Middle East. Many Pakistanis have family members or friends working abroad, and they need to track exchange rates to know the value of incoming remittances.
                Additionally, fluctuations in currency rates can impact import costs, travel expenses, and investment decisions, making it essential for individuals and businesses
                to stay informed about exchange rates.
            </p>

            <h2 class="section-title">Exchange Rates</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($currency_rates as $rate): ?>
                    <div class="currency-card">
                        <span class="flag"><?php echo htmlspecialchars($rate['flag_emoji']); ?></span>
                        <div>
                            <p class="code"><?php echo htmlspecialchars($rate['currency_code']); ?></p>
                            <p class="name"><?php echo htmlspecialchars($rate['currency_name']); ?></p>
                        </div>
                        <p class="rate">Rs. <?php echo htmlspecialchars($rate['rate_rs']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="text-gray-700 mt-8 leading-relaxed">
                Stay informed about the latest currency exchange rates to make timely financial decisions.
                Whether you're sending remittances, planning international travel, or involved in import/export business,
                accurate exchange rate information is vital. NewsHub provides up-to-date currency rates to help you navigate
                the financial landscape effectively.
                </p>
        </div>
    </main>

    <footer class="main-footer mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
            <div class="col-span-full md:col-span-1 lg:col-span-1">
                <a href="index.php" class="footer-logo">NEWS<span class="bg-red-600 text-white px-1">HUB</span></a>
                <p class="text-sm mt-2">Your ultimate source for reliable and timely news from around the world.</p>
            </div>
            <div>
                <h3 class="font-bold text-lg mb-2">SECTIONS</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="index.php" class="footer-link">Home</a></li>
                    <?php
                        $footer_categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 4");
                        if ($footer_categories_result && $footer_categories_result->num_rows > 0) {
                            while ($cat = $footer_categories_result->fetch_assoc()) {
                                $link_href = ($cat['name'] == 'Blogs') ? 'blogs.php' : 'category.php?id=' . $cat['id'];
                                echo '<li><a href="' . htmlspecialchars($link_href) . '" class="footer-link">' . htmlspecialchars($cat['name']) . '</a></li>';
                            }
                        }
                    ?>
                </ul>
            </div>
             <div>
                <h3 class="font-bold text-lg mb-2">ABOUT</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="about.php" class="footer-link">About Us</a></li>
                    <li><a href="privacy.php" class="footer-link">Privacy Policy</a></li>
                    <li><a href="contact.php" class="footer-link">Contact Us</a></li>
                </ul>
            </div>
             <div>
                <h3 class="font-bold text-lg mb-2">MORE</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="footer-link">Sitemap</a></li>
                    <li><a href="#" class="footer-link">Careers</a></li>
                    <li><a href="#" class="footer-link">Advertise</a></li>
                </ul>
            </div>
            <div class="col-span-full md:col-span-2 lg:col-span-1">
                 <h3 class="font-bold text-lg mb-2">SUBSCRIBE</h3>
                 <p class="text-sm mb-3">Get the latest news and updates delivered straight to your inbox.</p>
                 <form action="save_subscriber.php" method="POST" class="flex">
                    <input type="email" name="email" placeholder="Your email" class="w-full rounded-l-md p-2 footer-subscribe-input focus:ring-red-500 focus:border-red-500">
                    <button type="submit" class="p-2 rounded-r-md text-white font-bold footer-subscribe-button">Go</button>
                 </form>
            </div>
        </div>
        <div class="bg-gray-900 py-4 text-center text-sm mt-8">
            <p>© <?php echo date('Y'); ?> NewsHub. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
