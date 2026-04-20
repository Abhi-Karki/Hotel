<?php
session_start();
require_once "config.php";
require_once "lib/hotel-data.php";

if (!isset($_SESSION['userID']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sign-in.php");
    exit();
}

ensureHotelReviewSchema($pdo);

$bookingId = (int) ($_POST['booking_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$reviewTitle = trim((string) ($_POST['review_title'] ?? ''));
$reviewText = trim((string) ($_POST['review_text'] ?? ''));

$booking = getBookingByIdForCustomer($pdo, $bookingId, $_SESSION['userID']);

if (
    !$booking ||
    $rating < 1 ||
    $rating > 5 ||
    $reviewText === '' ||
    !in_array($booking['booking_status'], ['confirmed', 'completed'], true)
) {
    header("Location: user.php");
    exit();
}

$existingReview = $pdo->prepare("SELECT review_id FROM hotel_reviews WHERE booking_id = :booking_id LIMIT 1");
$existingReview->execute(['booking_id' => $bookingId]);

if (!$existingReview->fetchColumn()) {
    createHotelReview($pdo, [
        'hotel_id' => $booking['hotel_id'],
        'booking_id' => $bookingId,
        'customer_id' => $_SESSION['userID'],
        'customer_name' => $_SESSION['userName'] ?? $_SESSION['username'] ?? 'Guest',
        'rating' => $rating,
        'review_title' => $reviewTitle,
        'review_text' => $reviewText,
    ]);
}

header("Location: user.php#booking-history");
exit();
