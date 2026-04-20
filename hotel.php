<?php
session_start();
require_once "config.php";
require_once "lib/hotel-data.php";

$city = $_GET['city'] ?? '';
$minRating = isset($_GET['min_rating']) ? (float) $_GET['min_rating'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : 0;

if ($city !== 'kathmandu') {
    header("Location: index.php");
    exit();
}
//filtering algorithm is intentionally basic to keep the focus on the overall listing page experience rather than complex search features

$hotelCatalog = getHotelCatalog($pdo);
$hotelCatalog = array_filter($hotelCatalog, function ($hotel) use ($minRating, $maxPrice) {
    if ($minRating > 0 && (float) $hotel['rating'] < $minRating) {
        return false;
    }

    if ($maxPrice > 0 && (float) $hotel['price_from'] > $maxPrice) {
        return false;
    }

    return true;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hotels in Kathmandu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/site-theme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>
<body class="site-theme listing-page">

<header class="header" data-header>
    <div class="overlay" data-overlay></div>
    <div class="header-top">
        <div class="container">
            <a href="tel:+01123456790" class="helpline-box">
                <div class="icon-box"><ion-icon name="call-outline"></ion-icon></div>
                <div class="wrapper">
                    <p class="helpline-title">For Further Inquires :</p>
                    <p class="helpline-number">9843922230</p>
                </div>
            </a>

            <a href="index.php" class="logo"><img src="image/logo.svg" alt="Tourly logo"></a>

            <div class="header-btn-group">
                <button class="search-btn" aria-label="Search" type="button">
                    <ion-icon name="search"></ion-icon>
                </button>
                <button class="nav-open-btn" aria-label="Open Menu" data-nav-open-btn type="button">
                    <ion-icon name="menu-outline"></ion-icon>
                </button>
            </div>
        </div>
    </div>

    <div class="header-bottom">
        <div class="container">
            <ul class="social-list">
                <li><a href="#" class="social-link"><ion-icon name="logo-facebook"></ion-icon></a></li>
                <li><a href="#" class="social-link"><ion-icon name="logo-twitter"></ion-icon></a></li>
                <li><a href="#" class="social-link"><ion-icon name="logo-youtube"></ion-icon></a></li>
            </ul>

            <nav class="navbar" data-navbar>
                <div class="navbar-top">
                    <a href="index.php" class="logo"><img src="image/logo-blue.svg" alt="Tourly logo"></a>
                    <button class="nav-close-btn" aria-label="Close Menu" data-nav-close-btn type="button">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
                <ul class="navbar-list">
                    <li><a href="index.php#home" class="navbar-link" data-nav-link>home</a></li>
                    <li><a href="index.php#hotels" class="navbar-link" data-nav-link>hotels</a></li>
                    <li><a href="hotel.php?city=kathmandu" class="navbar-link" data-nav-link>rooms</a></li>
                    <li><a href="sign-in.php" class="navbar-link" data-nav-link>login</a></li>
                    <li><a href="register.php" class="navbar-link" data-nav-link>register</a></li>
                </ul>
            </nav>

            <?php if(isset($_SESSION['authenticated'])): ?>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            <?php else: ?>
                <div class="header-auth-cta">
                    <a href="sign-in.php" class="btn btn-primary">Sign in</a>
                    <a href="register.php" class="btn btn-secondary">Join now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<section class="listing-hero">
    <div class="container">
        <div class="hero-compact-grid">
            <div>
                <div class="hero-kicker">
                    <ion-icon name="location-outline"></ion-icon>
                    Kathmandu collection
                </div>
                <h1 class="hero-title-main">Compare Kathmandu stays with a cleaner, more professional browsing experience.</h1>
                <p class="hero-copy">This page now works like a proper hotel comparison screen, helping guests scan value, property personality, and room access before making their next move.</p>
            </div>

            <div class="hero-detail-card">
                <h3>Guest-friendly comparison</h3>
                <p>Each property now highlights location, mood, features, and price range instead of only showing a plain image and button.</p>
                <div class="hero-detail-stat">
                    <span>Properties available</span>
                    <strong><?= count($hotelCatalog) ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="page-shell page-top-spacing">
    <section class="section-shell compact hotel-list-shell">
        <div class="container">
            <div class="filter-toolbar">
                <div class="toolbar-copy">
                    <h3>Luxury, heritage, boutique, and city-center stays</h3>
                    <p>Use this listing page to compare the city's strongest options before entering the room selection flow.</p>
                </div>
                <div class="toolbar-actions">
                    <a href="index.php#hotels" class="btn btn-outline-primary">Back to featured</a>
                    <a href="register.php" class="btn btn-primary">Create guest account</a>
                </div>
            </div>

            <form class="filter-toolbar mb-4" method="get">
                <input type="hidden" name="city" value="kathmandu">
                <div class="toolbar-copy">
                    <h3>Filter stays</h3>
                    <p>Shortlisting by price and rating makes the browsing flow feel more like a real booking platform.</p>
                </div>
                <div class="toolbar-actions">
                    <select name="min_rating" class="form-control">
                        <option value="0">Any rating</option>
                        <option value="4" <?= $minRating === 4.0 ? 'selected' : '' ?>>4.0+ rating</option>
                        <option value="4.5" <?= $minRating === 4.5 ? 'selected' : '' ?>>4.5+ rating</option>
                    </select>
                    <select name="max_price" class="form-control">
                        <option value="0">Any price</option>
                        <option value="10000" <?= $maxPrice === 10000.0 ? 'selected' : '' ?>>Up to Rs 10,000</option>
                        <option value="15000" <?= $maxPrice === 15000.0 ? 'selected' : '' ?>>Up to Rs 15,000</option>
                        <option value="20000" <?= $maxPrice === 20000.0 ? 'selected' : '' ?>>Up to Rs 20,000</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Apply filters</button>
                    <a href="hotel.php?city=kathmandu" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <?php if ($hotelCatalog): ?>
                <div class="hotel-grid">
                    <?php foreach ($hotelCatalog as $hotelKey => $hotel): ?>
                        <article class="hotel-grid-card">
                            <div class="hotel-grid-media">
                                <img src="<?= htmlspecialchars($hotel['image']) ?>" alt="<?= htmlspecialchars($hotel['name']) ?>">
                                <div class="card-label"><?= htmlspecialchars($hotel['label']) ?></div>
                                <div class="card-rating-chip">
                                    <ion-icon name="star"></ion-icon>
                                    <?= htmlspecialchars(number_format($hotel['rating'], 1)) ?> / 5
                                </div>
                            </div>

                            <div class="hotel-grid-body">
                                <div class="card-location"><?= htmlspecialchars($hotel['location']) ?></div>
                                <h3 class="card-title-main"><?= htmlspecialchars($hotel['name']) ?></h3>
                                <p class="card-description"><?= htmlspecialchars($hotel['description']) ?></p>

                                <div class="feature-chip-row">
                                    <?php foreach ($hotel['features'] as $feature): ?>
                                        <span class="feature-chip"><?= htmlspecialchars($feature) ?></span>
                                    <?php endforeach; ?>
                                </div>

                                <div class="card-footer-row">
                                    <div class="price-block">
                                        <span class="price-caption">From</span>
                                        <div class="price-value">Rs <?= number_format($hotel['price_from']) ?> <span>/ night</span></div>
                                    </div>
                                    <a href="rooms.php?hotel=<?= urlencode($hotelKey) ?>" class="btn btn-primary">View rooms</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">No hotels match the selected filters right now.</div>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container">
        <div>
            <a href="index.php" class="logo mb-3"><img src="image/logo.svg" alt="Tourly logo"></a>
            <p class="footer-brand-copy">A cleaner hotel-listing experience for Kathmandu, designed to feel more credible and closer to real booking products.</p>
        </div>

        <div>
            <h4>Guest flow</h4>
            <ul class="footer-list">
                <li><a href="index.php">Landing page</a></li>
                <li><a href="hotel.php?city=kathmandu">Hotels</a></li>
                <li><a href="sign-in.php">Sign in</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </div>

        <div>
            <h4>Need help?</h4>
            <ul class="footer-list">
                <li><a href="tel:+01123456790">9843922230</a></li>
                <li><a href="mailto:info@tourlyhotel.com">info@tourlyhotel.com</a></li>
                <li>Durbar Marg, Kathmandu</li>
            </ul>
        </div>
    </div>

    <div class="container footer-bottom-bar">
        <span>&copy; <?= date('Y'); ?> Tourly Hotel Booking.</span>
        <div class="footer-mini-links">
            <a href="index.php#hotels">Featured hotels</a>
            <a href="hotel.php?city=kathmandu">All stays</a>
            <a href="sign-in.php">Guest login</a>
        </div>
    </div>
</footer>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>
