<?php
session_start();
require_once "config.php";
require_once "lib/hotel-data.php";

/* Fetch logged-in user details */
$user = null;
if (isset($_SESSION['userID'])) {
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE cid = :cid LIMIT 1");
    $stmt->bindParam(':cid', $_SESSION['userID']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$hotelCatalog = getHotelCatalog();
$hotelCount = count($hotelCatalog);
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kathmandu Hotels | Tourly</title>

<link rel="shortcut icon" href="./favicon.svg">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="css/site-theme.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
.user-slider {
  position: fixed;
  top: 0;
  right: -320px;
  width: 320px;
  height: 100%;
  background: #fff;
  box-shadow: -5px 0 20px rgba(0,0,0,0.2);
  transition: 0.3s;
  z-index: 2000;
  padding: 20px;
}
.user-slider.active { right: 0; }
.slider-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: none;
  z-index: 1500;
}
.slider-overlay.active { display: block; }
</style>
</head>

<body id="top" class="site-theme home-page">

<!-- ================= HEADER ================= -->
<header class="header" data-header>
  <div class="overlay" data-overlay></div>

  <div class="header-top">
    <div class="container">
      <a href="tel:+01123456790" class="helpline-box">
        <div class="icon-box">
          <ion-icon name="call-outline"></ion-icon>
        </div>
        <div class="wrapper">
          <p class="helpline-title">For Further Inquires :</p>
          <p class="helpline-number">9843922230</p>
        </div>
      </a>

      <a href="index.php" class="logo">
        <img src="image/logo.svg" alt="Tourly logo">
      </a>

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
          <a href="index.php" class="logo">
            <img src="image/logo-blue.svg" alt="Tourly logo">
          </a>
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

      <div class="header-cta">
        <?php if($user): ?>
          <button class="user-btn" id="userBtn">
            <ion-icon name="person-circle-outline"></ion-icon>
          </button>
        <?php else: ?>
          <a href="sign-in.php" class="btn btn-primary">Sign In</a>
          <a href="register.php" class="btn btn-secondary">Create Account</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<!-- ================= USER SLIDER ================= -->
<?php if($user): ?>
<div class="user-slider" id="userSlider">
  <button id="closeSlider" class="btn btn-sm btn-light mb-3">&times;</button>
  <h4>Hello, <?= htmlspecialchars($user['fullname']); ?></h4>
  <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
  <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
  <p><strong>Profile:</strong> Ready to book your next Kathmandu stay.</p>
  <hr>
  <a href="hotel.php?city=kathmandu" class="btn btn-primary btn-block mb-2">Browse Hotels</a>
  <a href="logout.php" class="btn btn-outline-secondary btn-block">Logout</a>
</div>
<div class="slider-overlay" id="sliderOverlay"></div>
<?php endif; ?>

<!-- ================= HERO ================= -->
<main>
<section class="site-hero" id="home">
  <div class="container">
    <div class="hero-grid">
      <div>
        <div class="hero-kicker">
          <ion-icon name="sparkles-outline"></ion-icon>
          Refined stays inspired by premium booking experiences
        </div>
        <h1 class="hero-title-main">Book Kathmandu hotels with confidence, clarity, and style.</h1>
        <p class="hero-copy">
          Discover carefully presented city stays, transparent pricing, and a smoother booking flow designed to feel more like a professional hotel platform than a student prototype.
        </p>
        <div class="hero-actions">
          <a href="#hotels" class="btn btn-primary">Explore Featured Hotels</a>
          <a href="hotel.php?city=kathmandu" class="btn btn-secondary">Browse All Rooms</a>
        </div>
        <div class="hero-stats">
          <div class="stat-pill">
            <strong><?= $hotelCount ?> curated stays</strong>
            Handpicked across Kathmandu
          </div>
          <div class="stat-pill">
            <strong>Instant room access</strong>
            View live room options fast
          </div>
          <div class="stat-pill">
            <strong>Guest-first design</strong>
            Cleaner flow from search to booking
          </div>
        </div>
      </div>

      <div class="hero-panel">
        <h2 class="hero-panel-title">Plan Your Next Stay</h2>
        <p class="hero-panel-copy">Inspired by search-first booking sites, this quick panel helps guests understand the stay before they click into room options.</p>
        <div class="hero-search-grid">
          <div class="field-card">
            <label>Destination</label>
            <span>Kathmandu, Nepal</span>
          </div>
          <div class="field-card">
            <label>Stay style</label>
            <span>Luxury, heritage, boutique</span>
          </div>
          <div class="field-card">
            <label>Guest promise</label>
            <span>Trusted hospitality and clear pricing</span>
          </div>
          <div class="field-card">
            <label>Best for</label>
            <span>Business, family, and city escapes</span>
          </div>
        </div>
        <a href="hotel.php?city=kathmandu" class="btn btn-primary">See Available Hotels</a>
        <div class="hero-trust-row">
          <div class="trust-pill">
            <strong>24/7 style support</strong>
            Smooth guest experience
          </div>
          <div class="trust-pill">
            <strong>Transparent pricing</strong>
            Clear room-by-room choices
          </div>
        </div>
      </div>
    </div>
  </div>
    </section>

    <section class="info-band">
      <div class="container">
        <div class="info-card">
          <h3>Search-first browsing</h3>
          <p>Borrowing from modern booking sites, the homepage now leads with clarity, fast exploration, and stronger room discovery.</p>
        </div>
        <div class="info-card">
          <h3>Professional visual language</h3>
          <p>Glass headers, richer spacing, better card hierarchy, and consistent typography now tie the pages together like one real product.</p>
        </div>
        <div class="info-card">
          <h3>Booking-focused storytelling</h3>
          <p>Each hotel now has a clearer offer: location, identity, price cues, and features that help guests choose more confidently.</p>
        </div>
      </div>
    </section>

    <section class="section-shell">
      <div class="container">
        <p class="section-kicker">Why Tourly feels better now</p>
        <h2 class="section-heading">A more polished hospitality website from the first scroll.</h2>
        <p class="section-copy">The visual direction now feels closer to modern booking platforms: stronger hierarchy, cleaner decisions, premium surfaces, and calmer spacing across every key page.</p>

        <div class="feature-grid mt-4">
          <div class="feature-card">
            <div class="feature-icon"><ion-icon name="search-outline"></ion-icon></div>
            <h3>Clear discovery</h3>
            <p>Guests can quickly understand the destination, compare hotels, and move into room-level browsing without visual clutter.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon"><ion-icon name="shield-checkmark-outline"></ion-icon></div>
            <h3>Trust-focused styling</h3>
            <p>Pricing, ratings, and feature chips are easier to scan, helping the site feel more credible and product-ready.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon"><ion-icon name="bed-outline"></ion-icon></div>
            <h3>Professional booking flow</h3>
            <p>Homepage, hotel list, room details, and account pages now share a consistent visual system instead of competing layouts.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section-shell compact" id="hotels">
      <div class="container">
        <div class="filter-toolbar">
          <div class="toolbar-copy">
            <h3>Featured hotels in Kathmandu</h3>
            <p>Compare atmosphere, value, and hotel style before you go deeper into room details.</p>
          </div>
          <div class="toolbar-actions">
            <button class="btn btn-outline-primary" id="sortNameBtn" type="button">Sort by name</button>
            <button class="btn btn-outline-primary" id="sortRatingBtn" type="button">Sort by rating</button>
          </div>
        </div>

        <div class="hotel-grid" id="hotelsContainer">
          <?php foreach ($hotelCatalog as $hotelKey => $hotel): ?>
            <article class="hotel-grid-card hotel-card-wrapper" data-name="<?= htmlspecialchars($hotel['name']) ?>" data-rating="<?= htmlspecialchars($hotel['rating']) ?>">
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
                    <span class="price-caption">Starting from</span>
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

    <section class="section-shell">
      <div class="container split-section">
        <div class="glass-card">
          <p class="section-kicker">Guest value</p>
          <h2 class="section-heading">A calmer way to compare stays and decide faster.</h2>
          <p class="section-copy">Instead of overwhelming visitors with raw content, the new layout gives each property a cleaner story: where it is, what kind of stay it offers, and why it is worth booking.</p>
          <ul class="bullet-list">
            <li>Stronger emphasis on hotel personality, not just image and name.</li>
            <li>Cleaner room-entry flow through clear primary actions.</li>
            <li>Consistent premium styling across guest, hotel, and auth pages.</li>
          </ul>
        </div>

        <div class="insight-panel">
          <h3>Professional design cues used here</h3>
          <p>The redesign borrows from strong booking-site patterns like search-led heroes, trust-driven badges, and cards that make room selection feel straightforward.</p>
          <ul class="bullet-list">
            <li>Search-first landing structure inspired by large booking platforms.</li>
            <li>Premium surfaces and friendly trust language similar to hospitality brands.</li>
            <li>Sharper action hierarchy so guests always know the next step.</li>
          </ul>
        </div>
      </div>
    </section>
</main>

<footer class="site-footer">
  <div class="container">
    <div>
      <a href="index.php" class="logo mb-3">
        <img src="image/logo.svg" alt="Tourly logo">
      </a>
      <p class="footer-brand-copy">Tourly is now presented like a cleaner hospitality platform, helping guests compare Kathmandu stays with more confidence and less friction.</p>
    </div>

    <div>
      <h4>Explore</h4>
      <ul class="footer-list">
        <li><a href="index.php#home">Homepage</a></li>
        <li><a href="index.php#hotels">Featured hotels</a></li>
        <li><a href="hotel.php?city=kathmandu">Hotel listing</a></li>
        <li><a href="sign-in.php">Sign in</a></li>
      </ul>
    </div>

    <div>
      <h4>Contact</h4>
      <ul class="footer-list">
        <li><a href="tel:+01123456790">9843922230</a></li>
        <li><a href="mailto:info@tourlyhotel.com">info@tourlyhotel.com</a></li>
        <li>Durbar Marg, Kathmandu, Nepal</li>
        <li>Always improving the booking experience</li>
      </ul>
    </div>
  </div>

  <div class="container footer-bottom-bar">
    <span>&copy; <?= date('Y') ?> Tourly Hotel Booking. Crafted for a more professional presentation.</span>
    <div class="footer-mini-links">
      <a href="register.php">Create account</a>
      <a href="sign-in.php">Guest login</a>
      <a href="hotel.php?city=kathmandu">Browse hotels</a>
    </div>
  </div>
</footer>

<!-- ================= JS ================= -->
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="js/script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>


//sorting algorithm inspired by simple client-side sorting patterns, allowing users to reorder hotels by name or rating without a page reload. This enhances the browsing experience and helps guests find their ideal stay faster.
<script>
<?php if($user): ?>
const userBtn = document.getElementById('userBtn');
const userSlider = document.getElementById('userSlider');
const sliderOverlay = document.getElementById('sliderOverlay');
const closeBtn = document.getElementById('closeSlider');

userBtn.onclick = () => {
  userSlider.classList.add('active');
  sliderOverlay.classList.add('active');
};
closeBtn.onclick = sliderOverlay.onclick = () => {
  userSlider.classList.remove('active');
  sliderOverlay.classList.remove('active');
};
<?php endif; ?>

// ================= SORTING LOGIC =================
const sortNameBtn = document.getElementById('sortNameBtn');
const sortRatingBtn = document.getElementById('sortRatingBtn');
const hotelsContainer = document.getElementById('hotelsContainer');

function sortHotels(by) {
    const hotels = Array.from(document.querySelectorAll('.hotel-card-wrapper'));
    hotels.sort((a, b) => {
        if (by === 'name') {
            return a.dataset.name.localeCompare(b.dataset.name);
        } else if (by === 'rating') {
            return b.dataset.rating - a.dataset.rating; // descending
        }
    });
    hotels.forEach(hotel => hotelsContainer.appendChild(hotel));
}

sortNameBtn.addEventListener('click', () => sortHotels('name'));
sortRatingBtn.addEventListener('click', () => sortHotels('rating'));
</script>

</body>
</html>
