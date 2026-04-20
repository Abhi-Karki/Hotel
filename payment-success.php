<?php
session_start();
require_once "config.php";
require_once "lib/hotel-data.php";

if (!isset($_SESSION['userID'])) {
    header("Location: sign-in.php");
    exit();
}

if(!isset($_GET['room_id'])){
    header("Location: hotel.php");
    exit;
}

$roomID = $_GET['room_id'];
$paymentReference = trim((string) ($_GET['session_id'] ?? ''));
$bookingDates = getBookingDateRangeFromRequest();
$specialRequest = trim((string) ($_GET['note'] ?? ''));

ensureRoomBookingSchema($pdo);

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare(
        "SELECT r.room_id, r.hotel_id, r.room_name, r.price, r.is_booked, h.hotel_name, h.hotel_key
         FROM rooms r
         JOIN hotels h ON r.hotel_id = h.hotel_id
         WHERE r.room_id = :rid
         LIMIT 1"
    );
    $stmt->execute(['rid' => $roomID]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        throw new RuntimeException("Room not found.");
    }

    if (!empty($room['is_booked']) || !isRoomAvailableForDates($pdo, $room['room_id'], $room['hotel_id'], $bookingDates['check_in'], $bookingDates['check_out'])) {
        throw new RuntimeException("Room is no longer available for the selected dates.");
    }

    $bookingId = recordRoomBooking($pdo, [
        'room_id' => $room['room_id'],
        'hotel_id' => $room['hotel_id'],
        'customer_id' => $_SESSION['userID'],
        'customer_name' => $_SESSION['userName'] ?? $_SESSION['username'] ?? 'Guest',
        'customer_email' => $_SESSION['userEmail'] ?? $_SESSION['accountEmail'] ?? '',
        'room_name' => $room['room_name'],
        'hotel_name' => $room['hotel_name'],
        'check_in_date' => $bookingDates['check_in'],
        'check_out_date' => $bookingDates['check_out'],
        'nights' => $bookingDates['nights'],
        'guests_count' => $bookingDates['guests'],
        'special_request' => $specialRequest,
        'amount_paid' => (float) $room['price'] * $bookingDates['nights'],
        'booking_status' => 'confirmed',
        'payment_reference' => $paymentReference,
    ]);

    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo "Unable to confirm booking right now.";
    exit();
}

$booking = getBookingByIdForCustomer($pdo, $bookingId, $_SESSION['userID']);
$hotelMeta = getHotelMeta($room['hotel_key'] ?? null);
$backToHotel = !empty($room['hotel_key']) ? "rooms.php?hotel=" . urlencode($room['hotel_key']) : "hotel.php?city=kathmandu";
$invoiceLink = $booking ? "invoice.php?booking_id=" . urlencode((string) $booking['booking_id']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed | Tourly</title>
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/site-theme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="site-theme">

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

            <a href="hotel.php?city=kathmandu" class="btn btn-primary">Browse more stays</a>
        </div>
    </div>
</header>

<main class="success-shell">
    <div class="success-card">
        <div class="success-icon">
            <ion-icon name="checkmark-outline"></ion-icon>
        </div>
        <p class="section-kicker">Booking confirmed</p>
        <h1 class="section-heading">Your room has been booked successfully.</h1>
        <p class="section-copy">
            <?= htmlspecialchars($room['room_name'] ?? 'Selected room') ?> at
            <?= htmlspecialchars($room['hotel_name'] ?? 'your chosen hotel') ?>
            is now confirmed.
        </p>

        <div class="feature-chip-row justify-content-center mt-4">
            <?php if ($hotelMeta): ?>
                <span class="feature-chip"><?= htmlspecialchars($hotelMeta['location']) ?></span>
            <?php endif; ?>
            <?php if (!empty($booking['amount_paid'])): ?>
                <span class="feature-chip">Rs <?= number_format((float) $booking['amount_paid'], 2) ?> paid</span>
            <?php endif; ?>
            <span class="feature-chip"><?= htmlspecialchars(date('d M Y', strtotime($bookingDates['check_in']))) ?> to <?= htmlspecialchars(date('d M Y', strtotime($bookingDates['check_out']))) ?></span>
            <span class="feature-chip"><?= htmlspecialchars((string) $bookingDates['guests']) ?> guest(s)</span>
            <span class="feature-chip">Status: Confirmed</span>
            <?php if ($paymentReference !== ''): ?>
                <span class="feature-chip">Ref: <?= htmlspecialchars($paymentReference) ?></span>
            <?php endif; ?>
            <?php if (!empty($booking['invoice_number'])): ?>
                <span class="feature-chip">Invoice: <?= htmlspecialchars($booking['invoice_number']) ?></span>
            <?php endif; ?>
        </div>

        <div class="hero-actions justify-content-center mt-4">
            <a href="<?= htmlspecialchars($backToHotel) ?>" class="btn btn-primary">Back to this hotel</a>
            <?php if ($invoiceLink): ?>
                <a href="<?= htmlspecialchars($invoiceLink) ?>" class="btn btn-outline-primary">View invoice</a>
            <?php endif; ?>
            <a href="hotel.php?city=kathmandu" class="btn btn-outline-secondary">Browse all hotels</a>
        </div>
    </div>
</main>

<script src="js/script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
