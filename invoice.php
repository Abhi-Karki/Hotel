<?php
session_start();
require_once "config.php";
require_once "lib/hotel-data.php";

if (!isset($_SESSION['userID'])) {
    header("Location: sign-in.php");
    exit();
}

$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
$booking = getBookingByIdForCustomer($pdo, $bookingId, $_SESSION['userID']);

if (!$booking) {
    header("Location: user.php");
    exit();
}

$checkInDate = !empty($booking['check_in_date']) ? date('d M Y', strtotime($booking['check_in_date'])) : 'Not set';
$checkOutDate = !empty($booking['check_out_date']) ? date('d M Y', strtotime($booking['check_out_date'])) : 'Not set';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($booking['invoice_number'] ?? 'Booking') ?></title>
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/site-theme.css">
</head>
<body class="site-theme">
<main class="success-shell">
    <div class="success-card text-left">
        <div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
            <div>
                <p class="section-kicker">Booking invoice</p>
                <h1 class="section-heading mb-2">Receipt for your hotel booking</h1>
                <p class="section-copy mb-0">This printable invoice makes the project look more like a real hotel management product.</p>
            </div>
            <div class="feature-chip-row mb-0">
                <span class="feature-chip"><?= htmlspecialchars($booking['invoice_number'] ?: 'Pending invoice') ?></span>
                <span class="feature-chip"><?= htmlspecialchars(ucfirst($booking['booking_status'])) ?></span>
            </div>
        </div>

        <div class="dashboard-grid">
            <article class="dashboard-card">
                <div class="dashboard-card-body">
                    <h3>Guest details</h3>
                    <p><strong>Name:</strong> <?= htmlspecialchars($booking['customer_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($booking['customer_email']) ?></p>
                    <p><strong>Guests:</strong> <?= htmlspecialchars((string) ($booking['guests_count'] ?? 1)) ?></p>
                </div>
            </article>

            <article class="dashboard-card">
                <div class="dashboard-card-body">
                    <h3>Stay details</h3>
                    <p><strong>Hotel:</strong> <?= htmlspecialchars($booking['hotel_name']) ?></p>
                    <p><strong>Room:</strong> <?= htmlspecialchars($booking['room_name']) ?></p>
                    <p><strong>Check-in:</strong> <?= htmlspecialchars($checkInDate) ?></p>
                    <p><strong>Check-out:</strong> <?= htmlspecialchars($checkOutDate) ?></p>
                </div>
            </article>

            <article class="dashboard-card">
                <div class="dashboard-card-body">
                    <h3>Payment details</h3>
                    <p><strong>Total paid:</strong> Rs <?= number_format((float) $booking['amount_paid'], 2) ?></p>
                    <p><strong>Nights:</strong> <?= htmlspecialchars((string) ($booking['nights'] ?? 1)) ?></p>
                    <p><strong>Payment ref:</strong> <?= htmlspecialchars($booking['payment_reference'] ?: 'Manual entry') ?></p>
                    <p><strong>Booked on:</strong> <?= htmlspecialchars(date('d M Y, h:i A', strtotime($booking['booked_at']))) ?></p>
                </div>
            </article>
        </div>

        <?php if (!empty($booking['special_request'])): ?>
            <div class="dashboard-card mt-4">
                <div class="dashboard-card-body">
                    <h3>Special request</h3>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($booking['special_request'])) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="hero-actions mt-4">
            <button type="button" class="btn btn-primary" onclick="window.print()">Print invoice</button>
            <a href="user.php" class="btn btn-outline-secondary">Back to dashboard</a>
        </div>
    </div>
</main>
</body>
</html>
