<?php
session_start();
require_once "config.php"; // Database connection
require_once "lib/hotel-data.php";

// Fetch user details if logged in
if(!isset($_SESSION['userID'])){
    header("Location: sign-in.php");
    exit();
}

if (!empty($_SESSION['isAdmin'])) {
    header("Location: admin.php");
    exit();
}

$user = null;
if(isset($_SESSION['userID'])){
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE cid = :cid LIMIT 1");
    $stmt->bindParam(':cid', $_SESSION['userID']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$featuredHotels = array_slice(getHotelCatalog(), 0, 3, true);
$customerBookings = getCustomerBookings($pdo, $_SESSION['userID']);
$reviewedBookingIds = [];

ensureHotelReviewSchema($pdo);
$reviewedStmt = $pdo->prepare("SELECT booking_id FROM hotel_reviews WHERE customer_id = :customer_id");
$reviewedStmt->execute(['customer_id' => (int) $_SESSION['userID']]);
$reviewedBookingIds = array_map('intval', $reviewedStmt->fetchAll(PDO::FETCH_COLUMN));
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tourly - Travel Agency</title>

<!-- favicon -->
<link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">

<!-- custom css link -->
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/site-theme.css">

<!-- google font link -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>

<body id="top" class="site-theme dashboard-page">

<!-- HEADER -->
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
                <button class="search-btn" aria-label="Search">
                    <ion-icon name="search"></ion-icon>
                </button>
                <button class="nav-open-btn" aria-label="Open Menu" data-nav-open-btn>
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
                    <button class="nav-close-btn" aria-label="Close Menu" data-nav-close-btn>
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
                <ul class="navbar-list">
                    <li><a href="index.php#home" class="navbar-link" data-nav-link>home</a></li>
                    <li><a href="index.php#hotels" class="navbar-link" data-nav-link>hotels</a></li>
                    <li><a href="hotel.php?city=kathmandu" class="navbar-link" data-nav-link>rooms</a></li>
                    <li><a href="user.php#destination" class="navbar-link" data-nav-link>explore</a></li>
                    <li><a href="register.php" class="navbar-link" data-nav-link>register</a></li>
                </ul>
            </nav>

            <!-- USER ICON -->
            <?php if($user): ?>
            <button class="user-btn" id="userBtn">
                <ion-icon name="person-circle-outline"></ion-icon>
            </button>
            <?php else: ?>
            <a href="sign-in.php" class="btn btn-primary">Book Now</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- USER SLIDER -->
<?php if($user): ?>
<div class="user-slider" id="userSlider">
    <button class="close-btn" id="closeSlider" type="button" aria-label="Close account panel">&times;</button>
    <div class="user-slider-head">
        <div class="user-slider-avatar">
            <ion-icon name="person-outline"></ion-icon>
        </div>
        <div>
            <p class="user-slider-kicker">Signed in account</p>
            <h3><?= htmlspecialchars($user['fullname']); ?></h3>
        </div>
    </div>

    <div class="user-slider-meta">
        <div class="user-meta-card">
            <span>Email</span>
            <strong><?= htmlspecialchars($user['email']); ?></strong>
        </div>
        <div class="user-meta-card">
            <span>Phone</span>
            <strong><?= htmlspecialchars($user['phone'] ?? 'N/A'); ?></strong>
        </div>
        <div class="user-meta-card">
            <span>Access</span>
            <strong>Guest account</strong>
        </div>
    </div>

    <div class="user-slider-actions">
        <a href="hotel.php?city=kathmandu" class="btn btn-outline-primary">Browse stays</a>
        <a href="logout.php" class="btn btn-primary">Logout</a>
    </div>
</div>
<div class="slider-overlay" id="sliderOverlay"></div>
<?php endif; ?>

<!-- MAIN -->
<section class="dashboard-hero">
    <div class="container">
        <div class="hero-compact-grid">
            <div>
                <div class="hero-kicker">
                    <ion-icon name="sparkles-outline"></ion-icon>
                    Guest dashboard
                </div>
                <h1 class="hero-title-main">Welcome back, <?= htmlspecialchars($user['fullname'] ?? 'Guest') ?>.</h1>
                <p class="hero-copy">Your member area now feels like part of the same hotel product, with clearer actions, featured stays, and a more intentional guest experience after login.</p>
                <div class="hero-actions">
                    <a href="hotel.php?city=kathmandu" class="btn btn-primary">Browse hotels</a>
                    <a href="index.php#hotels" class="btn btn-secondary">See featured stays</a>
                </div>
            </div>

            <div class="hero-detail-card">
                <h3>Account snapshot</h3>
                <p>Use this dashboard to jump back into hotel discovery, room selection, and the improved booking flow.</p>
                <div class="hero-detail-list">
                    <div class="hero-detail-stat">
                        <span>Guest email</span>
                        <strong><?= htmlspecialchars($user['email'] ?? 'Not available') ?></strong>
                    </div>
                    <div class="hero-detail-stat">
                        <span>Phone number</span>
                        <strong><?= htmlspecialchars($user['phone'] ?? 'Not available') ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="page-top-spacing">
    <section class="section-shell compact">
        <div class="container">
            <div class="dashboard-grid">
                <article class="dashboard-card">
                    <div class="dashboard-card-body">
                        <h3>Explore Kathmandu stays</h3>
                        <p>Browse a cleaner hotel listing with better card hierarchy and clearer room-entry actions.</p>
                        <a href="hotel.php?city=kathmandu" class="btn btn-primary">Open hotel list</a>
                    </div>
                </article>

                <article class="dashboard-card">
                    <div class="dashboard-card-body">
                        <h3>Continue from featured picks</h3>
                        <p>Jump straight back to the polished homepage section built to compare your best hotel options faster.</p>
                        <a href="index.php#hotels" class="btn btn-outline-primary">See featured hotels</a>
                    </div>
                </article>

                <article class="dashboard-card">
                    <div class="dashboard-card-body">
                        <h3>Manage your account</h3>
                        <p>Access your profile details from the top-right account panel and continue browsing with a more premium flow.</p>
                        <button class="btn btn-outline-secondary" type="button" id="dashboardOpenUser">Open account panel</button>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="section-shell" id="destination">
        <div class="container">
            <p class="section-kicker">Recommended stays</p>
            <h2 class="section-heading">Featured Kathmandu hotels for your next booking.</h2>
            <p class="section-copy">Instead of unrelated travel content, your dashboard now suggests real properties from the site so guests can return directly to meaningful actions.</p>

            <div class="hotel-grid mt-4">
                <?php foreach ($featuredHotels as $hotelKey => $hotel): ?>
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
        </div>
    </section>

    <section class="section-shell" id="booking-history">
        <div class="container">
            <p class="section-kicker">Booking history</p>
            <h2 class="section-heading">Track reservations, print invoices, and leave reviews.</h2>
            <p class="section-copy">This is the kind of complete user workflow that makes the project feel more professional: customers can see past bookings, open receipts, and share feedback.</p>

            <?php if ($customerBookings): ?>
                <div class="dashboard-grid mt-4">
                    <?php foreach ($customerBookings as $booking): ?>
                        <?php
                        $canReview = in_array($booking['booking_status'], ['confirmed', 'completed'], true) && !in_array((int) $booking['booking_id'], $reviewedBookingIds, true);
                        $invoiceUrl = "invoice.php?booking_id=" . urlencode((string) $booking['booking_id']);
                        ?>
                        <article class="dashboard-card">
                            <div class="dashboard-card-body">
                                <div class="feature-chip-row">
                                    <span class="feature-chip"><?= htmlspecialchars($booking['invoice_number'] ?: 'Invoice pending') ?></span>
                                    <span class="feature-chip"><?= htmlspecialchars(ucfirst($booking['booking_status'])) ?></span>
                                </div>
                                <h3><?= htmlspecialchars($booking['hotel_name']) ?></h3>
                                <p><?= htmlspecialchars($booking['room_name']) ?> from <?= htmlspecialchars(date('d M Y', strtotime($booking['check_in_date'] ?: $booking['booked_at']))) ?> to <?= htmlspecialchars(date('d M Y', strtotime($booking['check_out_date'] ?: $booking['booked_at']))) ?>.</p>
                                <div class="feature-chip-row">
                                    <span class="feature-chip">Rs <?= number_format((float) $booking['amount_paid'], 2) ?></span>
                                    <span class="feature-chip"><?= htmlspecialchars((string) ($booking['guests_count'] ?? 1)) ?> guest(s)</span>
                                    <span class="feature-chip"><?= htmlspecialchars((string) ($booking['nights'] ?? 1)) ?> night(s)</span>
                                </div>
                                <div class="hero-actions mt-3">
                                    <a href="<?= htmlspecialchars($invoiceUrl) ?>" class="btn btn-outline-primary">View invoice</a>
                                    <?php if ($canReview): ?>
                                        <a href="#review-<?= htmlspecialchars((string) $booking['booking_id']) ?>" class="btn btn-outline-secondary">Leave review</a>
                                    <?php endif; ?>
                                </div>

                                <?php if ($canReview): ?>
                                    <form method="post" action="submit-review.php" class="mt-4" id="review-<?= htmlspecialchars((string) $booking['booking_id']) ?>">
                                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars((string) $booking['booking_id']) ?>">
                                        <div class="form-group">
                                            <label>Rating</label>
                                            <select name="rating" class="form-control" required>
                                                <option value="">Select rating</option>
                                                <option value="5">5 - Excellent</option>
                                                <option value="4">4 - Very good</option>
                                                <option value="3">3 - Good</option>
                                                <option value="2">2 - Fair</option>
                                                <option value="1">1 - Poor</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Review title</label>
                                            <input type="text" name="review_title" class="form-control" placeholder="Comfortable stay, great location">
                                        </div>
                                        <div class="form-group">
                                            <label>Your review</label>
                                            <textarea name="review_text" class="form-control" rows="4" placeholder="Share your stay experience" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit review</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state mt-4">No bookings yet. Once you reserve a room, your history and printable invoices will show here.</div>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container">
        <div>
            <a href="index.php" class="logo mb-3">
                <img src="image/logo.svg" alt="Tourly logo">
            </a>
            <p class="footer-brand-copy">Your logged-in experience now matches the rest of the redesigned hotel site with a dashboard that feels purposeful instead of generic.</p>
        </div>

        <div>
            <h4>Dashboard links</h4>
            <ul class="footer-list">
                <li><a href="index.php">Homepage</a></li>
                <li><a href="hotel.php?city=kathmandu">Hotel listing</a></li>
                <li><a href="index.php#hotels">Featured hotels</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div>
            <h4>Contact</h4>
            <ul class="footer-list">
                <li><a href="tel:+01123456790">9843922230</a></li>
                <li><a href="mailto:info@tourlyhotel.com">info@tourlyhotel.com</a></li>
                <li>Kathmandu, Nepal</li>
            </ul>
        </div>
    </div>

    <div class="container footer-bottom-bar">
        <span>&copy; <?= date('Y') ?> Tourly Hotel Booking.</span>
        <div class="footer-mini-links">
            <a href="hotel.php?city=kathmandu">All hotels</a>
            <a href="index.php#hotels">Featured stays</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</footer>

<a href="#top" class="go-top" data-go-top><ion-icon name="chevron-up-outline"></ion-icon></a>

<!-- JS -->
<script src="js/script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<script>
// User slider JS
<?php if($user): ?>
const userBtn = document.getElementById('userBtn');
const userSlider = document.getElementById('userSlider');
const sliderOverlay = document.getElementById('sliderOverlay');
const closeSlider = document.getElementById('closeSlider');

userBtn.addEventListener('click', () => {
    userSlider.classList.add('active');
    sliderOverlay.classList.add('active');
});
closeSlider.addEventListener('click', () => {
    userSlider.classList.remove('active');
    sliderOverlay.classList.remove('active');
});
sliderOverlay.addEventListener('click', () => {
    userSlider.classList.remove('active');
    sliderOverlay.classList.remove('active');
});

const dashboardOpenUser = document.getElementById('dashboardOpenUser');
if (dashboardOpenUser) {
    dashboardOpenUser.addEventListener('click', () => {
        userSlider.classList.add('active');
        sliderOverlay.classList.add('active');
    });
}
<?php endif; ?>



</script>

</body>
</html>
