<?php
ob_start();
session_start();
require_once "config.php";
require_once "lib/hotel-data.php";

if (!isset($_SESSION['userID'])) {
    header("Location: sign-in.php");
    exit();
}

if (empty($_SESSION['isAdmin'])) {
    header("Location: user.php");
    exit();
}

ensureHotelPresentationSchema($pdo);
ensureRoomBookingSchema($pdo);

function adminSlugify($value)
{
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    $value = trim((string) $value, '-');

    return $value !== '' ? $value : 'hotel-' . time();
}

function adminSetFlash($type, $message)
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function adminPopFlash()
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);

    return $flash;
}

function adminRedirect($tab)
{
    header("Location: admin.php?tab=" . urlencode($tab));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_hotel') {
            $hotelId = (int) ($_POST['hotel_id'] ?? 0);
            $hotelName = trim((string) ($_POST['hotel_name'] ?? ''));
            $hotelKey = adminSlugify($_POST['hotel_key'] ?? $hotelName);
            $description = trim((string) ($_POST['description'] ?? ''));
            $location = trim((string) ($_POST['location'] ?? 'Kathmandu, Nepal'));
            $imagePath = trim((string) ($_POST['image_path'] ?? 'image/hero-banner.jpg'));
            $rating = (float) ($_POST['rating'] ?? 4.5);
            $reviewsCount = (int) ($_POST['reviews_count'] ?? 0);
            $priceFrom = (float) ($_POST['price_from'] ?? 0);
            $badgeLabel = trim((string) ($_POST['badge_label'] ?? 'Featured Stay'));
            $features = serializeHotelFeatures(preg_split('/[\r\n,]+/', (string) ($_POST['features'] ?? '')));

            if ($hotelName === '' || $description === '') {
                throw new RuntimeException('Hotel name and description are required.');
            }

            if ($hotelId > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE hotels
                     SET hotel_key = :hotel_key,
                         hotel_name = :hotel_name,
                         description = :description,
                         location = :location,
                         image_path = :image_path,
                         rating = :rating,
                         reviews_count = :reviews_count,
                         price_from = :price_from,
                         badge_label = :badge_label,
                         features = :features
                     WHERE hotel_id = :hotel_id"
                );
                $stmt->execute([
                    'hotel_key' => $hotelKey,
                    'hotel_name' => $hotelName,
                    'description' => $description,
                    'location' => $location,
                    'image_path' => $imagePath,
                    'rating' => $rating,
                    'reviews_count' => $reviewsCount,
                    'price_from' => $priceFrom,
                    'badge_label' => $badgeLabel,
                    'features' => $features,
                    'hotel_id' => $hotelId,
                ]);
                adminSetFlash('success', 'Hotel updated successfully.');
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO hotels (
                        hotel_key, hotel_name, location, description, image_path,
                        rating, reviews_count, price_from, badge_label, features
                    ) VALUES (
                        :hotel_key, :hotel_name, :location, :description, :image_path,
                        :rating, :reviews_count, :price_from, :badge_label, :features
                    )"
                );
                $stmt->execute([
                    'hotel_key' => $hotelKey,
                    'hotel_name' => $hotelName,
                    'location' => $location,
                    'description' => $description,
                    'image_path' => $imagePath,
                    'rating' => $rating,
                    'reviews_count' => $reviewsCount,
                    'price_from' => $priceFrom,
                    'badge_label' => $badgeLabel,
                    'features' => $features,
                ]);
                adminSetFlash('success', 'Hotel added successfully.');
            }

            adminRedirect('hotels');
        }

        if ($action === 'delete_hotel') {
            $hotelId = (int) ($_POST['hotel_id'] ?? 0);

            if ($hotelId <= 0) {
                throw new RuntimeException('Invalid hotel selected.');
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM room_bookings WHERE hotel_id = :hotel_id");
            $stmt->execute(['hotel_id' => $hotelId]);
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE hotel_id = :hotel_id");
            $stmt->execute(['hotel_id' => $hotelId]);
            $stmt = $pdo->prepare("DELETE FROM hotels WHERE hotel_id = :hotel_id");
            $stmt->execute(['hotel_id' => $hotelId]);
            $pdo->commit();

            adminSetFlash('success', 'Hotel and its rooms were removed.');
            adminRedirect('hotels');
        }

        if ($action === 'save_room') {
            $roomId = (int) ($_POST['room_id'] ?? 0);
            $hotelId = (int) ($_POST['hotel_id'] ?? 0);
            $roomName = trim((string) ($_POST['room_name'] ?? ''));
            $price = (float) ($_POST['price'] ?? 0);
            $isBooked = !empty($_POST['is_booked']) ? 1 : 0;

            if ($hotelId <= 0 || $roomName === '' || $price <= 0) {
                throw new RuntimeException('Hotel, room name, and price are required.');
            }

            if ($roomId > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE rooms
                     SET hotel_id = :hotel_id, room_name = :room_name, price = :price, is_booked = :is_booked
                     WHERE room_id = :room_id"
                );
                $stmt->execute([
                    'hotel_id' => $hotelId,
                    'room_name' => $roomName,
                    'price' => $price,
                    'is_booked' => $isBooked,
                    'room_id' => $roomId,
                ]);
                adminSetFlash('success', 'Room updated successfully.');
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO rooms (hotel_id, room_name, price, is_booked)
                     VALUES (:hotel_id, :room_name, :price, :is_booked)"
                );
                $stmt->execute([
                    'hotel_id' => $hotelId,
                    'room_name' => $roomName,
                    'price' => $price,
                    'is_booked' => $isBooked,
                ]);
                adminSetFlash('success', 'Room added successfully.');
            }

            adminRedirect('rooms');
        }

        if ($action === 'delete_room') {
            $roomId = (int) ($_POST['room_id'] ?? 0);

            if ($roomId <= 0) {
                throw new RuntimeException('Invalid room selected.');
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM room_bookings WHERE room_id = :room_id");
            $stmt->execute(['room_id' => $roomId]);
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = :room_id");
            $stmt->execute(['room_id' => $roomId]);
            $pdo->commit();

            adminSetFlash('success', 'Room removed successfully.');
            adminRedirect('rooms');
        }

        if ($action === 'toggle_admin') {
            $customerId = (int) ($_POST['customer_id'] ?? 0);
            $makeAdmin = (int) ($_POST['make_admin'] ?? 0);

            if ($customerId <= 0) {
                throw new RuntimeException('Invalid customer selected.');
            }

            $stmt = $pdo->prepare("UPDATE customer SET isadmin = :isadmin WHERE cid = :cid");
            $stmt->execute([
                'isadmin' => $makeAdmin ? 1 : 0,
                'cid' => $customerId,
            ]);

            adminSetFlash('success', $makeAdmin ? 'Customer promoted to admin.' : 'Admin access removed.');
            adminRedirect('customers');
        }

        if ($action === 'update_booking_status') {
            $bookingId = (int) ($_POST['booking_id'] ?? 0);
            $bookingStatus = trim((string) ($_POST['booking_status'] ?? 'confirmed'));
            $allowedStatuses = ['confirmed', 'cancelled', 'completed'];

            if ($bookingId <= 0 || !in_array($bookingStatus, $allowedStatuses, true)) {
                throw new RuntimeException('Invalid booking update.');
            }

            $stmt = $pdo->prepare(
                "UPDATE room_bookings
                 SET booking_status = :booking_status, updated_at = CURRENT_TIMESTAMP
                 WHERE booking_id = :booking_id"
            );
            $stmt->execute([
                'booking_status' => $bookingStatus,
                'booking_id' => $bookingId,
            ]);

            adminSetFlash('success', 'Booking status updated.');
            adminRedirect('bookings');
        }
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        adminSetFlash('danger', $exception->getMessage());
        adminRedirect($_POST['redirect_tab'] ?? 'hotels');
    }
}

$activeTab = $_GET['tab'] ?? 'hotels';
if (!in_array($activeTab, ['hotels', 'rooms', 'customers', 'bookings'], true)) {
    $activeTab = 'hotels';
}

$hotelCatalog = getHotelCatalog($pdo);
$hotels = array_values($hotelCatalog);
$hotelMapById = [];
foreach ($hotels as $hotel) {
    if (!empty($hotel['hotel_id'])) {
        $hotelMapById[(int) $hotel['hotel_id']] = $hotel;
    }
}

$roomsStmt = $pdo->query(
    "SELECT r.room_id, r.hotel_id, r.room_name, r.price, r.is_booked, h.hotel_name
     FROM rooms r
     JOIN hotels h ON h.hotel_id = r.hotel_id
     ORDER BY h.hotel_name ASC, r.room_name ASC"
);
$rooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);

$customersStmt = $pdo->query(
    "SELECT cid, fullname, email, phone, isadmin
     FROM customer
     ORDER BY fullname ASC"
);
$customers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);

$bookingsStmt = $pdo->query(
    "SELECT booking_id, room_id, hotel_name, room_name, customer_name, customer_email,
            amount_paid, booking_status, payment_reference, invoice_number,
            check_in_date, check_out_date, guests_count, nights, booked_at
     FROM room_bookings
     ORDER BY booked_at DESC, booking_id DESC"
);
$bookings = $bookingsStmt->fetchAll(PDO::FETCH_ASSOC);

ensureHotelReviewSchema($pdo);
$reviewStats = $pdo->query(
    "SELECT COUNT(*) AS total_reviews, AVG(rating) AS average_rating
     FROM hotel_reviews
     WHERE review_status = 'published'"
)->fetch(PDO::FETCH_ASSOC) ?: [];

$bookingStats = $pdo->query(
    "SELECT COUNT(*) AS total_bookings,
            SUM(CASE WHEN booking_status IN ('confirmed', 'completed') THEN amount_paid ELSE 0 END) AS total_revenue,
            SUM(CASE WHEN YEAR(booked_at) = YEAR(CURDATE()) AND MONTH(booked_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) AS monthly_bookings
     FROM room_bookings"
)->fetch(PDO::FETCH_ASSOC) ?: [];

$popularHotelStats = $pdo->query(
    "SELECT hotel_name, COUNT(*) AS booking_count
     FROM room_bookings
     GROUP BY hotel_id, hotel_name
     ORDER BY booking_count DESC, hotel_name ASC
     LIMIT 1"
)->fetch(PDO::FETCH_ASSOC) ?: [];

$editHotelId = isset($_GET['edit_hotel']) ? (int) $_GET['edit_hotel'] : 0;
$editRoomId = isset($_GET['edit_room']) ? (int) $_GET['edit_room'] : 0;
$editHotel = $editHotelId > 0 && isset($hotelMapById[$editHotelId]) ? $hotelMapById[$editHotelId] : null;
$editRoom = null;
foreach ($rooms as $room) {
    if ((int) $room['room_id'] === $editRoomId) {
        $editRoom = $room;
        break;
    }
}

$flash = adminPopFlash();
$currentAdminName = $_SESSION['userName'] ?? $_SESSION['username'] ?? 'Administrator';
$totalHotels = count($hotels);
$totalRooms = count($rooms);
$availableRooms = 0;
foreach ($rooms as $room) {
    if (empty($room['is_booked'])) {
        $availableRooms++;
    }
}
$adminCount = 0;
foreach ($customers as $customer) {
    if (!empty($customer['isadmin'])) {
        $adminCount++;
    }
}
$totalBookings = count($bookings);
$activeBookings = 0;
foreach ($bookings as $booking) {
    if (($booking['booking_status'] ?? '') === 'confirmed') {
        $activeBookings++;
    }
}
$totalRevenue = (float) ($bookingStats['total_revenue'] ?? 0);
$monthlyBookings = (int) ($bookingStats['monthly_bookings'] ?? 0);
$totalReviews = (int) ($reviewStats['total_reviews'] ?? 0);
$averageReviewRating = $reviewStats['average_rating'] !== null ? round((float) $reviewStats['average_rating'], 1) : null;
$topHotelName = $popularHotelStats['hotel_name'] ?? 'No bookings yet';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Panel | Tourly Hotel Booking</title>
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/site-theme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-shell { padding: 3rem 0 4rem; }
        .admin-toolbar { display: flex; justify-content: space-between; gap: 1rem; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; }
        .admin-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .admin-kpi, .admin-panel, .admin-form-card { background: rgba(255,255,255,0.96); border: 1px solid rgba(15,23,42,0.08); border-radius: 22px; box-shadow: 0 20px 60px rgba(15,23,42,0.08); }
        .admin-kpi { padding: 1.35rem 1.5rem; }
        .admin-kpi span { display: block; color: #64748b; font-size: 0.86rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; }
        .admin-kpi strong { font-size: 1.9rem; color: #0f172a; display: block; }
        .admin-tabs { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
        .admin-tabs a { padding: 0.8rem 1.15rem; border-radius: 999px; background: rgba(255,255,255,0.85); border: 1px solid rgba(15,23,42,0.08); color: #0f172a; text-decoration: none; font-weight: 600; }
        .admin-tabs a.active { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }
        .admin-grid { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(320px, 0.95fr); gap: 1.5rem; align-items: start; }
        .admin-panel { padding: 1.5rem; overflow: hidden; }
        .admin-form-card { padding: 1.5rem; position: sticky; top: 120px; }
        .admin-panel table { margin-bottom: 0; }
        .admin-panel .table thead th { border-top: 0; color: #475569; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.06em; }
        .admin-badge { display: inline-flex; align-items: center; padding: 0.38rem 0.75rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700; }
        .admin-badge.confirmed { background: rgba(16,185,129,0.12); color: #047857; }
        .admin-badge.cancelled { background: rgba(239,68,68,0.12); color: #b91c1c; }
        .admin-badge.completed { background: rgba(59,130,246,0.12); color: #1d4ed8; }
        .admin-mini { color: #64748b; font-size: 0.9rem; }
        .admin-form-card .form-control, .admin-form-card textarea, .admin-form-card select { border-radius: 14px; min-height: 48px; }
        .admin-form-card textarea { min-height: 120px; }
        .admin-empty { padding: 2rem; text-align: center; color: #64748b; }
        @media (max-width: 991px) {
            .admin-grid { grid-template-columns: 1fr; }
            .admin-form-card { position: static; }
        }
    </style>
</head>
<body class="site-theme admin-page">
<header class="header" data-header>
    <div class="overlay" data-overlay></div>
    <div class="header-top">
        <div class="container">
            <a href="tel:+01123456790" class="helpline-box">
                <div class="icon-box"><ion-icon name="call-outline"></ion-icon></div>
                <div class="wrapper">
                    <p class="helpline-title">Admin support</p>
                    <p class="helpline-number">9843922230</p>
                </div>
            </a>
            <a href="index.php" class="logo"><img src="image/logo.svg" alt="Tourly logo"></a>
            <div class="header-btn-group">
                <button class="search-btn" aria-label="Search" type="button">
                    <ion-icon name="settings-outline"></ion-icon>
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
                <li><a href="index.php" class="social-link"><ion-icon name="home-outline"></ion-icon></a></li>
                <li><a href="hotel.php?city=kathmandu" class="social-link"><ion-icon name="bed-outline"></ion-icon></a></li>
                <li><a href="logout.php" class="social-link"><ion-icon name="log-out-outline"></ion-icon></a></li>
            </ul>
            <nav class="navbar" data-navbar>
                <div class="navbar-top">
                    <a href="index.php" class="logo"><img src="image/logo-blue.svg" alt="Tourly logo"></a>
                    <button class="nav-close-btn" aria-label="Close Menu" data-nav-close-btn type="button">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
                <ul class="navbar-list">
                    <li><a href="admin.php?tab=hotels" class="navbar-link" data-nav-link>hotels</a></li>
                    <li><a href="admin.php?tab=rooms" class="navbar-link" data-nav-link>rooms</a></li>
                    <li><a href="admin.php?tab=customers" class="navbar-link" data-nav-link>customers</a></li>
                    <li><a href="admin.php?tab=bookings" class="navbar-link" data-nav-link>bookings</a></li>
                    <li><a href="index.php" class="navbar-link" data-nav-link>site</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="btn btn-primary">Logout</a>
        </div>
    </div>
</header>

<section class="dashboard-hero">
    <div class="container">
        <div class="hero-compact-grid">
            <div>
                <div class="hero-kicker">
                    <ion-icon name="shield-checkmark-outline"></ion-icon>
                    Admin dashboard
                </div>
                <h1 class="hero-title-main">Manage hotels, rooms, customers, and bookings from one professional control panel.</h1>
                <p class="hero-copy">Signed in as <?= htmlspecialchars($currentAdminName) ?>. This panel writes directly to your hotel database so you can add new properties, update rooms, and review confirmed reservations.</p>
            </div>
            <div class="hero-detail-card">
                <h3>Today at a glance</h3>
                <p>Track your inventory and customer activity without leaving the same branded site experience.</p>
                <div class="hero-detail-stat">
                    <span>Confirmed bookings</span>
                    <strong><?= htmlspecialchars((string) $activeBookings) ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="admin-shell">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <div class="admin-toolbar">
            <div>
                <p class="section-kicker mb-2">Control center</p>
                <h2 class="section-heading mb-2">Operations overview</h2>
                <p class="section-copy mb-0">Use the tabs below to manage the live data your hotel site depends on.</p>
            </div>
            <div class="hero-actions">
                <a href="index.php" class="btn btn-outline-secondary">View public site</a>
                <a href="hotel.php?city=kathmandu" class="btn btn-primary">Browse hotel pages</a>
            </div>
        </div>

        <div class="admin-kpis">
            <div class="admin-kpi"><span>Total hotels</span><strong><?= htmlspecialchars((string) $totalHotels) ?></strong></div>
            <div class="admin-kpi"><span>Total rooms</span><strong><?= htmlspecialchars((string) $totalRooms) ?></strong></div>
            <div class="admin-kpi"><span>Available rooms</span><strong><?= htmlspecialchars((string) $availableRooms) ?></strong></div>
            <div class="admin-kpi"><span>Admin users</span><strong><?= htmlspecialchars((string) $adminCount) ?></strong></div>
            <div class="admin-kpi"><span>Booking records</span><strong><?= htmlspecialchars((string) $totalBookings) ?></strong></div>
            <div class="admin-kpi"><span>Total revenue</span><strong>Rs <?= number_format($totalRevenue, 2) ?></strong></div>
            <div class="admin-kpi"><span>Monthly bookings</span><strong><?= htmlspecialchars((string) $monthlyBookings) ?></strong></div>
            <div class="admin-kpi"><span>Published reviews</span><strong><?= htmlspecialchars((string) $totalReviews) ?></strong></div>
        </div>

        <div class="admin-panel mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h3 class="mb-2">Analytics summary</h3>
                    <p class="admin-mini mb-0">These quick metrics make the project feel more like a business dashboard instead of a simple CRUD panel.</p>
                </div>
                <div class="feature-chip-row mb-0">
                    <span class="feature-chip">Top hotel: <?= htmlspecialchars($topHotelName) ?></span>
                    <span class="feature-chip">Avg review: <?= htmlspecialchars($averageReviewRating !== null ? number_format($averageReviewRating, 1) . ' / 5' : 'No reviews') ?></span>
                </div>
            </div>
        </div>

        <div class="admin-tabs">
            <a href="admin.php?tab=hotels" class="<?= $activeTab === 'hotels' ? 'active' : '' ?>">Hotels</a>
            <a href="admin.php?tab=rooms" class="<?= $activeTab === 'rooms' ? 'active' : '' ?>">Rooms</a>
            <a href="admin.php?tab=customers" class="<?= $activeTab === 'customers' ? 'active' : '' ?>">Customers</a>
            <a href="admin.php?tab=bookings" class="<?= $activeTab === 'bookings' ? 'active' : '' ?>">Bookings</a>
        </div>

        <?php if ($activeTab === 'hotels'): ?>
            <div class="admin-grid">
                <section class="admin-panel">
                    <h3 class="mb-2">Hotel inventory</h3>
                    <p class="admin-mini mb-4">Edit what appears on the public hotel listing and room pages.</p>
                    <?php if ($hotels): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Hotel</th>
                                        <th>Location</th>
                                        <th>Pricing</th>
                                        <th>Rooms</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hotels as $hotel): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($hotel['name']) ?></strong>
                                                <div class="admin-mini"><?= htmlspecialchars($hotel['label']) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($hotel['location']) ?></td>
                                            <td>Rs <?= number_format((float) $hotel['price_from'], 2) ?></td>
                                            <td><?= htmlspecialchars((string) ($hotel['available_room_count'] ?? 0)) ?> / <?= htmlspecialchars((string) ($hotel['room_count'] ?? 0)) ?> available</td>
                                            <td class="text-right">
                                                <a href="admin.php?tab=hotels&edit_hotel=<?= urlencode((string) $hotel['hotel_id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_hotel">
                                                    <input type="hidden" name="redirect_tab" value="hotels">
                                                    <input type="hidden" name="hotel_id" value="<?= htmlspecialchars((string) $hotel['hotel_id']) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this hotel and its rooms?');">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty">No hotels found yet.</div>
                    <?php endif; ?>
                </section>

                <aside class="admin-form-card">
                    <h3 class="mb-2"><?= $editHotel ? 'Edit hotel' : 'Add hotel' ?></h3>
                    <p class="admin-mini mb-4">Fill the property details used across the homepage, listing, and room screens.</p>
                    <form method="post">
                        <input type="hidden" name="action" value="save_hotel">
                        <input type="hidden" name="redirect_tab" value="hotels">
                        <input type="hidden" name="hotel_id" value="<?= htmlspecialchars((string) ($editHotel['hotel_id'] ?? '0')) ?>">
                        <div class="form-group">
                            <label>Hotel name</label>
                            <input type="text" class="form-control" name="hotel_name" value="<?= htmlspecialchars($editHotel['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Hotel key</label>
                            <input type="text" class="form-control" name="hotel_key" value="<?= htmlspecialchars($editHotel['hotel_key'] ?? '') ?>" placeholder="generated automatically if blank">
                        </div>
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($editHotel['location'] ?? 'Kathmandu, Nepal') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Image path</label>
                            <input type="text" class="form-control" name="image_path" value="<?= htmlspecialchars($editHotel['image'] ?? 'image/hero-banner.jpg') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" required><?= htmlspecialchars($editHotel['description'] ?? '') ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Rating</label>
                                <input type="number" step="0.1" min="0" max="5" class="form-control" name="rating" value="<?= htmlspecialchars((string) ($editHotel['rating'] ?? '4.5')) ?>">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Reviews</label>
                                <input type="number" min="0" class="form-control" name="reviews_count" value="<?= htmlspecialchars((string) ($editHotel['reviews'] ?? '0')) ?>">
                            </div>
                            <div class="form-group col-md-4">
                                <label>From price</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="price_from" value="<?= htmlspecialchars((string) ($editHotel['price_from'] ?? '0')) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Badge label</label>
                            <input type="text" class="form-control" name="badge_label" value="<?= htmlspecialchars($editHotel['label'] ?? 'Featured Stay') ?>">
                        </div>
                        <div class="form-group">
                            <label>Features</label>
                            <textarea class="form-control" name="features" placeholder="Free breakfast, Airport pickup, City view"><?= htmlspecialchars(serializeHotelFeatures($editHotel['features'] ?? [])) ?></textarea>
                        </div>
                        <div class="hero-actions">
                            <button type="submit" class="btn btn-primary"><?= $editHotel ? 'Update hotel' : 'Add hotel' ?></button>
                            <a href="admin.php?tab=hotels" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </aside>
            </div>
        <?php elseif ($activeTab === 'rooms'): ?>
            <div class="admin-grid">
                <section class="admin-panel">
                    <h3 class="mb-2">Room inventory</h3>
                    <p class="admin-mini mb-4">Control pricing and availability for each property.</p>
                    <?php if ($rooms): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Room</th>
                                        <th>Hotel</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rooms as $room): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($room['room_name']) ?></td>
                                            <td><?= htmlspecialchars($room['hotel_name']) ?></td>
                                            <td>Rs <?= number_format((float) $room['price'], 2) ?></td>
                                            <td><span class="admin-badge <?= !empty($room['is_booked']) ? 'cancelled' : 'confirmed' ?>"><?= !empty($room['is_booked']) ? 'Booked' : 'Available' ?></span></td>
                                            <td class="text-right">
                                                <a href="admin.php?tab=rooms&edit_room=<?= urlencode((string) $room['room_id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_room">
                                                    <input type="hidden" name="redirect_tab" value="rooms">
                                                    <input type="hidden" name="room_id" value="<?= htmlspecialchars((string) $room['room_id']) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this room?');">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty">No rooms added yet.</div>
                    <?php endif; ?>
                </section>

                <aside class="admin-form-card">
                    <h3 class="mb-2"><?= $editRoom ? 'Edit room' : 'Add room' ?></h3>
                    <p class="admin-mini mb-4">Assign a room to a hotel and set its current availability.</p>
                    <form method="post">
                        <input type="hidden" name="action" value="save_room">
                        <input type="hidden" name="redirect_tab" value="rooms">
                        <input type="hidden" name="room_id" value="<?= htmlspecialchars((string) ($editRoom['room_id'] ?? '0')) ?>">
                        <div class="form-group">
                            <label>Hotel</label>
                            <select class="form-control" name="hotel_id" required>
                                <option value="">Select hotel</option>
                                <?php foreach ($hotels as $hotel): ?>
                                    <option value="<?= htmlspecialchars((string) $hotel['hotel_id']) ?>" <?= (string) ($editRoom['hotel_id'] ?? '') === (string) $hotel['hotel_id'] ? 'selected' : '' ?>><?= htmlspecialchars($hotel['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Room name</label>
                            <input type="text" class="form-control" name="room_name" value="<?= htmlspecialchars($editRoom['room_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="price" value="<?= htmlspecialchars((string) ($editRoom['price'] ?? '0')) ?>" required>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="isBooked" name="is_booked" value="1" <?= !empty($editRoom['is_booked']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isBooked">Mark as currently booked</label>
                        </div>
                        <div class="hero-actions">
                            <button type="submit" class="btn btn-primary"><?= $editRoom ? 'Update room' : 'Add room' ?></button>
                            <a href="admin.php?tab=rooms" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </aside>
            </div>
        <?php elseif ($activeTab === 'customers'): ?>
            <section class="admin-panel">
                <h3 class="mb-2">Customer accounts</h3>
                <p class="admin-mini mb-4">Promote trusted users to admin or return them to normal guest access.</p>
                <?php if ($customers): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Access</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($customer['fullname']) ?></td>
                                        <td><?= htmlspecialchars($customer['email']) ?></td>
                                        <td><?= htmlspecialchars($customer['phone']) ?></td>
                                        <td><span class="admin-badge <?= !empty($customer['isadmin']) ? 'completed' : 'confirmed' ?>"><?= !empty($customer['isadmin']) ? 'Admin' : 'Guest' ?></span></td>
                                        <td class="text-right">
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_admin">
                                                <input type="hidden" name="redirect_tab" value="customers">
                                                <input type="hidden" name="customer_id" value="<?= htmlspecialchars((string) $customer['cid']) ?>">
                                                <input type="hidden" name="make_admin" value="<?= !empty($customer['isadmin']) ? '0' : '1' ?>">
                                                <button type="submit" class="btn btn-sm <?= !empty($customer['isadmin']) ? 'btn-outline-danger' : 'btn-outline-primary' ?>"><?= !empty($customer['isadmin']) ? 'Make guest' : 'Make admin' ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="admin-empty">No customer records found.</div>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <div class="admin-grid">
                <section class="admin-panel">
                    <h3 class="mb-2">Booking records</h3>
                    <p class="admin-mini mb-4">Monitor successful payments and update each booking lifecycle status.</p>
                    <?php if ($bookings): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Guest</th>
                                        <th>Hotel / Room</th>
                                        <th>Stay dates</th>
                                        <th>Amount</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($booking['customer_name'] ?: 'Guest') ?></strong>
                                                <div class="admin-mini"><?= htmlspecialchars($booking['customer_email'] ?: 'No email') ?></div>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($booking['hotel_name']) ?>
                                                <div class="admin-mini"><?= htmlspecialchars($booking['room_name']) ?> - <?= htmlspecialchars((string) $booking['booked_at']) ?></div>
                                            </td>
                                            <td>
                                                <?php if (!empty($booking['check_in_date']) && !empty($booking['check_out_date'])): ?>
                                                    <?= htmlspecialchars(date('d M Y', strtotime($booking['check_in_date']))) ?>
                                                    <div class="admin-mini">to <?= htmlspecialchars(date('d M Y', strtotime($booking['check_out_date']))) ?> • <?= htmlspecialchars((string) ($booking['nights'] ?? 1)) ?> night(s), <?= htmlspecialchars((string) ($booking['guests_count'] ?? 1)) ?> guest(s)</div>
                                                <?php else: ?>
                                                    <span class="admin-mini">Legacy booking record</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>Rs <?= number_format((float) $booking['amount_paid'], 2) ?></td>
                                            <td>
                                                <?= htmlspecialchars($booking['payment_reference'] ?: 'Manual / old booking') ?>
                                                <div class="admin-mini"><?= htmlspecialchars($booking['invoice_number'] ?: 'No invoice') ?></div>
                                            </td>
                                            <td><span class="admin-badge <?= htmlspecialchars($booking['booking_status']) ?>"><?= htmlspecialchars(ucfirst($booking['booking_status'])) ?></span></td>
                                            <td class="text-right">
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="update_booking_status">
                                                    <input type="hidden" name="redirect_tab" value="bookings">
                                                    <input type="hidden" name="booking_id" value="<?= htmlspecialchars((string) $booking['booking_id']) ?>">
                                                    <input type="hidden" name="booking_status" value="confirmed">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Confirm</button>
                                                </form>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="update_booking_status">
                                                    <input type="hidden" name="redirect_tab" value="bookings">
                                                    <input type="hidden" name="booking_id" value="<?= htmlspecialchars((string) $booking['booking_id']) ?>">
                                                    <input type="hidden" name="booking_status" value="completed">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Complete</button>
                                                </form>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="update_booking_status">
                                                    <input type="hidden" name="redirect_tab" value="bookings">
                                                    <input type="hidden" name="booking_id" value="<?= htmlspecialchars((string) $booking['booking_id']) ?>">
                                                    <input type="hidden" name="booking_status" value="cancelled">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty">No booking records yet. Once a guest completes payment, bookings will appear here automatically.</div>
                    <?php endif; ?>
                </section>

                <aside class="admin-form-card">
                    <h3 class="mb-2">Booking notes</h3>
                    <p class="admin-mini mb-3">Confirmed bookings are created automatically from the payment success page. Use this screen to track operations after the booking is placed.</p>
                    <ul class="footer-list mb-0">
                        <li>Use <strong>Confirm</strong> for fresh paid bookings.</li>
                        <li>Use <strong>Complete</strong> after checkout or fulfillment.</li>
                        <li>Use <strong>Cancel</strong> for refunds or invalid reservations.</li>
                    </ul>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <div>
            <a href="index.php" class="logo mb-3"><img src="image/logo.svg" alt="Tourly logo"></a>
            <p class="footer-brand-copy">The admin panel is now part of the same branded booking system, with direct database control for hotels, rooms, customers, and bookings.</p>
        </div>
        <div>
            <h4>Admin tools</h4>
            <ul class="footer-list">
                <li><a href="admin.php?tab=hotels">Manage hotels</a></li>
                <li><a href="admin.php?tab=rooms">Manage rooms</a></li>
                <li><a href="admin.php?tab=customers">Manage customers</a></li>
                <li><a href="admin.php?tab=bookings">Manage bookings</a></li>
            </ul>
        </div>
        <div>
            <h4>Quick links</h4>
            <ul class="footer-list">
                <li><a href="index.php">Public homepage</a></li>
                <li><a href="hotel.php?city=kathmandu">Hotel listing</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom-bar">
        <span>&copy; <?= date('Y') ?> Tourly Hotel Booking.</span>
        <div class="footer-mini-links">
            <a href="admin.php?tab=hotels">Hotels</a>
            <a href="admin.php?tab=bookings">Bookings</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="js/script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
