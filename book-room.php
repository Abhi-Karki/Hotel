<?php
session_start();
require_once "config.php";
require_once "lib/hotel-data.php";

// Check if user is logged in
if(!isset($_SESSION['userID'])){
    header("Location: sign-in.php");
    exit;
}

require_once 'stripe-php-master/init.php';

\Stripe\Stripe::setApiKey('sk_test_51SleDKFNyLJtESumGlFWdNqpFAMSi6ViCogAhf2JoSKZNde4eeUVPtSXMfH4fOWAvjjbxgW8iJSwPrmEQJ13KqHn00Y508Afc2');


// Replace with your Stripe Secret Key

if(!isset($_GET['room_id'])){
    header("Location: hotel.php");
    exit;
}

$roomID = $_GET['room_id'];
$bookingDates = getBookingDateRangeFromRequest();
$specialRequest = trim((string) ($_GET['note'] ?? ''));

// Fetch room info
$stmt = $pdo->prepare("SELECT r.*, h.hotel_name, h.hotel_key FROM rooms r JOIN hotels h ON r.hotel_id = h.hotel_id WHERE r.room_id = :rid");
$stmt->execute(['rid' => $roomID]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$room){
    echo "Room not found!";
    exit;
}

if (!empty($room['is_booked']) || !isRoomAvailableForDates($pdo, $room['room_id'], $room['hotel_id'], $bookingDates['check_in'], $bookingDates['check_out'])) {
    header("Location: rooms.php?hotel=" . urlencode($room['hotel_key']) . "&check_in=" . urlencode($bookingDates['check_in']) . "&check_out=" . urlencode($bookingDates['check_out']) . "&guests=" . urlencode((string) $bookingDates['guests']));
    exit;
}

$totalAmount = (float) $room['price'] * $bookingDates['nights'];

// Create Stripe Checkout Session
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$YOUR_DOMAIN = $scheme . "://" . $host . "/hotel";

$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'npr',
            'product_data' => [
                'name' => $room['room_name'] . " - " . $room['hotel_name'],
                'description' => $bookingDates['check_in'] . " to " . $bookingDates['check_out'] . " for " . $bookingDates['guests'] . " guest(s)",
            ],
            'unit_amount' => (int) round($totalAmount * 100), // amount in paisa
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => $YOUR_DOMAIN . "/payment-success.php?room_id=" . $roomID
        . "&session_id={CHECKOUT_SESSION_ID}"
        . "&check_in=" . urlencode($bookingDates['check_in'])
        . "&check_out=" . urlencode($bookingDates['check_out'])
        . "&guests=" . urlencode((string) $bookingDates['guests'])
        . "&note=" . urlencode($specialRequest),
    'cancel_url' => $YOUR_DOMAIN . "/rooms.php?hotel=" . urlencode($room['hotel_key'])
        . "&check_in=" . urlencode($bookingDates['check_in'])
        . "&check_out=" . urlencode($bookingDates['check_out'])
        . "&guests=" . urlencode((string) $bookingDates['guests']),
]);

header("Location: " . $session->url);
exit;
