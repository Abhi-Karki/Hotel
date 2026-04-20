<?php
session_start();
require_once "config.php";
require_once "lib/hotel-data.php";

// User info for slider
$user = null;
if(isset($_SESSION['userID'])){
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE cid = :cid LIMIT 1");
    $stmt->bindParam(':cid', $_SESSION['userID']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check hotel
if (!isset($_GET['hotel'])) {
    header("Location: hotel.php");
    exit;
}

$hotelKey = $_GET['hotel'];
$hotelMeta = getHotelMeta($hotelKey, $pdo);
$bookingDates = getBookingDateRangeFromRequest();

/* Fetch hotel info */
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE hotel_key = :key LIMIT 1");
$stmt->execute(['key' => $hotelKey]);
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hotel) {
    echo "Hotel not found!";
    exit;
}

/* Fetch rooms */
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE hotel_id = :hid");
$stmt->execute(['hid' => $hotel['hotel_id']]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unavailableRoomIds = getUnavailableRoomIds($pdo, $hotel['hotel_id'], $bookingDates['check_in'], $bookingDates['check_out']);
$availableRooms = [];

foreach ($rooms as &$room) {
    $room['date_available'] = empty($room['is_booked']) && !in_array((int) $room['room_id'], $unavailableRoomIds, true);
    if ($room['date_available']) {
        $availableRooms[] = $room;
    }
}
unset($room);

$reviewSummary = getHotelReviewSummary($pdo, $hotel['hotel_id']);
$hotelReviewsList = getHotelReviews($pdo, $hotel['hotel_id']);

$hotelVisual = $hotelMeta['image'] ?? 'image/hero-banner.jpg';
$hotelDescription = $hotel['description'] ?? ($hotelMeta['description'] ?? 'A thoughtfully selected stay in Kathmandu with a more polished booking experience.');
$hotelFeatures = $hotelMeta['features'] ?? ['Comfortable rooms', 'Prime location', 'Smooth booking'];
$hotelLocation = $hotelMeta['location'] ?? 'Kathmandu, Nepal';
$hotelRating = $reviewSummary['average_rating'] ?? ($hotelMeta['rating'] ?? 4.5);
$hotelReviews = $reviewSummary['review_count'] ?: ($hotelMeta['reviews'] ?? count($rooms) * 18);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($hotel['hotel_name']) ?> | Rooms</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="css/site-theme.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ================= USER SLIDER ================= */
.user-slider {
    position: fixed;
    top: 0;
    right: -300px;
    width: 300px;
    height: 100%;
    background: #fff;
    box-shadow: -4px 0 10px rgba(0,0,0,0.2);
    transition: right 0.3s ease;
    z-index: 1000;
    padding: 20px;
    overflow-y: auto;
}
.user-slider.active { right: 0; }
.user-slider h3 { margin-top:0; font-size:22px; }
.user-slider p { margin:5px 0; }
.user-slider .close-btn {
    position: absolute; top: 10px; left: 10px;
    background: transparent; border: none; font-size:20px; cursor:pointer;
}
.slider-overlay {
    position: fixed; top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.4);
    display: none; z-index:900;
}
.slider-overlay.active { display:block; }

/* ================= HERO / HOTEL IMAGE ================= */
.property-visual {
    background-image:
        linear-gradient(180deg, rgba(2, 8, 23, 0.1), rgba(2, 8, 23, 0.45)),
        url('<?= htmlspecialchars($hotelVisual) ?>');
}
</style>
</head>

<body id="top" class="site-theme rooms-page">

<!-- ================= HEADER ================= -->
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

            <?php if($user): ?>
                <button class="user-btn" id="userBtn">
                    <ion-icon name="person-circle-outline"></ion-icon>
                </button>
            <?php else: ?>
                <a href="sign-in.php" class="btn btn-primary">Login / Book Now</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- ================= USER SLIDER ================= -->
<?php if($user): ?>
<div class="user-slider" id="userSlider">
    <button class="close-btn" id="closeSlider">&times;</button>
    <h3>Welcome, <?= htmlspecialchars($user['fullname']); ?></h3>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
    <p><strong>Registered on:</strong> <?= date('d M, Y', strtotime($user['created_at'] ?? '')); ?></p>
    <hr>
    <a href="logout.php" class="btn btn-primary btn-block mt-2">Logout</a>
</div>
<div class="slider-overlay" id="sliderOverlay"></div>
<?php endif; ?>

<main class="page-shell page-top-spacing">
  <section class="section-shell compact room-list-shell">
    <div class="container">
      <div class="property-hero-card">
        <div class="property-visual">
          <div class="property-visual-copy">
            <div class="hero-meta-label">
              <ion-icon name="star-outline"></ion-icon>
              <?= htmlspecialchars(number_format($hotelRating, 1)) ?> rating from <?= htmlspecialchars($hotelReviews) ?> reviews
            </div>
            <h1><?= htmlspecialchars($hotel['hotel_name']) ?></h1>
            <p><?= htmlspecialchars($hotelLocation) ?></p>
          </div>
        </div>

        <div class="property-panel">
          <div class="property-panel-card">
            <h3>Property overview</h3>
            <p><?= htmlspecialchars($hotelDescription) ?></p>
          </div>

          <div class="property-panel-card">
            <h3>Why guests choose this stay</h3>
            <ul class="amenity-grid">
              <?php foreach ($hotelFeatures as $feature): ?>
                <li><?= htmlspecialchars($feature) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="property-panel-card">
            <h3>Room access</h3>
            <p><?= count($rooms) ?> room option(s) currently listed for this property. Explore each card below to see availability and move into booking.</p>
          </div>
        </div>
      </div>

      <div class="filter-toolbar">
        <div class="toolbar-copy">
          <h3>Available room categories</h3>
          <p>Choose your stay dates first so the system can show live room availability for that period.</p>
        </div>
        <div class="toolbar-actions">
          <a href="hotel.php?city=kathmandu" class="btn btn-outline-secondary">&larr; Back to hotels</a>
        </div>
      </div>

      <form class="filter-toolbar mb-4" method="get">
        <input type="hidden" name="hotel" value="<?= htmlspecialchars($hotelKey) ?>">
        <div class="toolbar-copy">
          <h3>Check date-based availability</h3>
          <p><?= count($availableRooms) ?> room(s) available from <?= htmlspecialchars(date('d M Y', strtotime($bookingDates['check_in']))) ?> to <?= htmlspecialchars(date('d M Y', strtotime($bookingDates['check_out']))) ?>.</p>
        </div>
        <div class="toolbar-actions">
          <input type="date" class="form-control" name="check_in" value="<?= htmlspecialchars($bookingDates['check_in']) ?>" min="<?= htmlspecialchars(date('Y-m-d')) ?>" required>
          <input type="date" class="form-control" name="check_out" value="<?= htmlspecialchars($bookingDates['check_out']) ?>" min="<?= htmlspecialchars(date('Y-m-d', strtotime($bookingDates['check_in'] . ' +2 day'))) ?>" required>
          <select class="form-control" name="guests">
            <?php for ($guestOption = 1; $guestOption <= 4; $guestOption++): ?>
              <option value="<?= $guestOption ?>" <?= $bookingDates['guests'] === $guestOption ? 'selected' : '' ?>><?= $guestOption ?> guest<?= $guestOption > 1 ? 's' : '' ?></option>
            <?php endfor; ?>
          </select>
          <button type="submit" class="btn btn-primary">Update search</button>
        </div>
      </form>

      <?php if(count($rooms) == 0): ?>
          <div class="empty-state">No rooms are available for this property right now.</div>
      <?php else: ?>
          <div class="room-grid">
            <?php foreach($rooms as $room): ?>
                <?php
                $roomImage = 'image/double.jpg';
                $isSuite = false;
                if (stripos($room['room_name'], 'deluxe') !== false) {
                    $roomImage = 'image/deluxe.jpg';
                } elseif (stripos($room['room_name'], 'suite') !== false) {
                    $roomImage = 'image/double.jpg';
                    $isSuite = true;
                } elseif (stripos($room['room_name'], 'single') !== false) {
                    $roomImage = 'image/single.jpg';
                }
                ?>
                <article class="room-grid-card">
                    <div class="room-grid-media">
                        <img src="<?= htmlspecialchars($roomImage) ?>" alt="<?= htmlspecialchars($room['room_name']) ?>">
                        <?php if($isSuite): ?>
                            <div class="card-label">Suite experience</div>
                        <?php endif; ?>
                    </div>
                    <div class="room-grid-body">
                        <h3 class="card-title-main"><?= htmlspecialchars($room['room_name']) ?></h3>
                        <p class="card-description">Well-presented accommodation with a cleaner booking flow and clearer pricing for guests.</p>

                        <?php if(!$room['date_available']): ?>
                            <span class="room-status booked">Unavailable for selected dates</span>
                        <?php else: ?>
                            <span class="room-status available">Available for <?= htmlspecialchars($bookingDates['nights']) ?> night(s)</span>
                        <?php endif; ?>

                        <?php if($isSuite): ?>
                            <span class="room-status suite">Premium suite</span>
                        <?php endif; ?>

                        <div class="card-footer-row">
                            <div class="price-block">
                                <span class="price-caption">Nightly rate</span>
                                <div class="price-value">Rs <?= number_format($room['price'], 2) ?></div>
                            </div>

                            <?php if(!$room['date_available']): ?>
                                <button class="btn btn-outline-secondary" type="button" disabled>Not available</button>
                            <?php else: ?>
                                <a href="book-room.php?room_id=<?= $room['room_id'] ?>&check_in=<?= urlencode($bookingDates['check_in']) ?>&check_out=<?= urlencode($bookingDates['check_out']) ?>&guests=<?= urlencode((string) $bookingDates['guests']) ?>" class="btn btn-primary">Book now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
          </div>
      <?php endif; ?>

      <section class="section-shell compact pb-0">
        <div class="split-section">
          <article class="dashboard-card">
            <div class="dashboard-card-body">
              <p class="section-kicker">Guest feedback</p>
              <h3 class="card-title-main">What guests are saying</h3>
              <p class="card-description">Real reviews make the project feel far more like a live hotel platform, so this hotel now has a review section linked to completed bookings.</p>
              <div class="feature-chip-row">
                <span class="feature-chip"><?= htmlspecialchars(number_format((float) $hotelRating, 1)) ?> / 5 average</span>
                <span class="feature-chip"><?= htmlspecialchars((string) $hotelReviews) ?> review<?= $hotelReviews === 1 ? '' : 's' ?></span>
              </div>
            </div>
          </article>

          <aside class="insight-panel">
            <h3>Review highlights</h3>
            <p>Guests can now leave a rating and comment after booking, and those reviews appear directly on the hotel page.</p>
            <ul class="bullet-list">
              <li>Ratings help future guests compare hotels.</li>
              <li>Reviews are linked to real bookings for more credibility.</li>
              <li>Admins can reference review-driven quality in the dashboard.</li>
            </ul>
          </aside>
        </div>

        <div class="dashboard-grid mt-4">
          <?php if ($hotelReviewsList): ?>
            <?php foreach ($hotelReviewsList as $review): ?>
              <article class="dashboard-card">
                <div class="dashboard-card-body">
                  <div class="feature-chip-row">
                    <span class="feature-chip"><?= str_repeat('★', max(1, (int) $review['rating'])) ?></span>
                    <span class="feature-chip"><?= htmlspecialchars(date('d M Y', strtotime($review['created_at']))) ?></span>
                  </div>
                  <h3><?= htmlspecialchars($review['review_title'] ?: 'Guest review') ?></h3>
                  <p><?= htmlspecialchars($review['review_text']) ?></p>
                  <div class="card-location"><?= htmlspecialchars($review['customer_name']) ?></div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">No guest reviews yet. Once customers complete stays, their feedback will appear here.</div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </section>
</main>

<footer class="site-footer">
    <div class="container">
        <div>
            <a href="index.php" class="logo mb-3"><img src="image/logo.svg" alt="Tourly logo"></a>
            <p class="footer-brand-copy">Tourly's room-selection flow now feels more like a polished booking platform, with clearer property context and cleaner room cards.</p>
        </div>

        <div>
            <h4>Navigate</h4>
            <ul class="footer-list">
                <li><a href="index.php">Homepage</a></li>
                <li><a href="hotel.php?city=kathmandu">Hotel listing</a></li>
                <li><a href="sign-in.php">Sign in</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </div>

        <div>
            <h4>Support</h4>
            <ul class="footer-list">
                <li><a href="tel:+01123456790">9843922230</a></li>
                <li><a href="mailto:info@tourlyhotel.com">info@tourlyhotel.com</a></li>
                <li><?= htmlspecialchars($hotelLocation) ?></li>
            </ul>
        </div>
    </div>

    <div class="container footer-bottom-bar">
        <span>&copy; <?= date('Y') ?> Tourly Hotel Booking.</span>
        <div class="footer-mini-links">
            <a href="hotel.php?city=kathmandu">All hotels</a>
            <a href="sign-in.php">Guest login</a>
            <a href="register.php">Create account</a>
        </div>
    </div>
</footer>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
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
<?php endif; ?>
</script>

</body>
</html>
