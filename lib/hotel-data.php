<?php

function getDefaultHotelCatalog()
{
    return [
        'everest' => [
            'name' => 'Hotel Everest',
            'location' => 'Lazimpat, Kathmandu',
            'image' => 'image/everest.jpg',
            'rating' => 4.8,
            'reviews' => 324,
            'price_from' => 14500,
            'label' => 'Business Favorite',
            'description' => 'Luxury hotel with modern amenities in central Kathmandu and polished city views.',
            'features' => ['Free breakfast', 'Airport pickup', 'Conference hall'],
        ],
        'grand' => [
            'name' => 'Kathmandu Grand Hotel',
            'location' => 'Thamel, Kathmandu',
            'image' => 'image/grand-hotel-kathmandu.jpg',
            'rating' => 4.9,
            'reviews' => 412,
            'price_from' => 16800,
            'label' => 'Top Rated',
            'description' => 'Elegant rooms with premium services, curated dining, and warm hospitality.',
            'features' => ['Rooftop dining', 'City transfer', 'Family rooms'],
        ],
        'pashupati' => [
            'name' => 'Pashupati Palace',
            'location' => 'Gaushala, Kathmandu',
            'image' => 'image/p.jpg',
            'rating' => 4.4,
            'reviews' => 198,
            'price_from' => 9600,
            'label' => 'Cultural Escape',
            'description' => 'Boutique comfort near heritage landmarks with intimate interiors and attentive service.',
            'features' => ['Temple access', 'Quiet courtyard', 'Local cuisine'],
        ],
        'annapurna' => [
            'name' => 'Hotel Annapurna',
            'location' => 'Durbar Marg, Kathmandu',
            'image' => 'image/an.jpg',
            'rating' => 4.6,
            'reviews' => 286,
            'price_from' => 13200,
            'label' => 'Classic Luxury',
            'description' => 'Classic hotel with rooftop views, refined lounges, and cozy rooms for every stay.',
            'features' => ['Pool access', 'Central location', 'Event spaces'],
        ],
        'hyatt' => [
            'name' => 'Hyatt Regency Kathmandu',
            'location' => 'Boudha, Kathmandu',
            'image' => 'image/hyat.jpg',
            'rating' => 5.0,
            'reviews' => 516,
            'price_from' => 21900,
            'label' => 'Luxury Resort',
            'description' => 'Resort-style luxury with spa, landscaped pools, and premium five-star service.',
            'features' => ['Full spa', 'Garden resort', 'Fine dining'],
        ],
        'shanker' => [
            'name' => 'Hotel Shanker',
            'location' => 'Lazimpat, Kathmandu',
            'image' => 'image/shanker.jpg',
            'rating' => 4.7,
            'reviews' => 301,
            'price_from' => 12400,
            'label' => 'Heritage Stay',
            'description' => 'Historical palace hotel with colonial charm, grand halls, and elegant rooms.',
            'features' => ['Palace architecture', 'Outdoor pool', 'Garden dining'],
        ],
        'yak' => [
            'name' => 'Hotel Yak & Yeti',
            'location' => 'Durbar Marg, Kathmandu',
            'image' => 'image/yak.jpg',
            'rating' => 4.9,
            'reviews' => 437,
            'price_from' => 18900,
            'label' => 'Signature Stay',
            'description' => 'One of Kathmandu\'s iconic luxury stays with timeless interiors and premium suites.',
            'features' => ['Casino', 'Luxury suites', 'Executive lounge'],
        ],
        'radisson' => [
            'name' => 'Radisson Hotel Kathmandu',
            'location' => 'Lazimpat, Kathmandu',
            'image' => 'image/rad.jpg',
            'rating' => 4.6,
            'reviews' => 255,
            'price_from' => 15400,
            'label' => 'Business Ready',
            'description' => 'Modern hotel with flexible meeting rooms, fitness facilities, and sleek interiors.',
            'features' => ['Meeting rooms', 'Fitness club', 'Airport transfer'],
        ],
        'malla' => [
            'name' => 'Hotel Malla',
            'location' => 'Lainchaur, Kathmandu',
            'image' => 'image/malla.jpg',
            'rating' => 4.2,
            'reviews' => 164,
            'price_from' => 8700,
            'label' => 'Value Choice',
            'description' => 'Friendly full-service hotel with practical comfort and a convenient city location.',
            'features' => ['Budget friendly', 'Garden area', 'Quick check-in'],
        ],
        'vaishali' => [
            'name' => 'Hotel Vaishali',
            'location' => 'Thamel, Kathmandu',
            'image' => 'image/vai.jpg',
            'rating' => 4.5,
            'reviews' => 208,
            'price_from' => 10400,
            'label' => 'City Center',
            'description' => 'Comfortable stay with dependable service, vibrant surroundings, and great food.',
            'features' => ['Rooftop pool', 'Walkable nightlife', 'Restaurant'],
        ],
        'tibet' => [
            'name' => 'Hotel Tibet International',
            'location' => 'Boudha, Kathmandu',
            'image' => 'image/tibet.jpeg',
            'rating' => 4.3,
            'reviews' => 149,
            'price_from' => 9200,
            'label' => 'Boutique Calm',
            'description' => 'Quiet boutique hotel with Tibetan-inspired design and easy access to sightseeing.',
            'features' => ['Spa ritual', 'Spiritual district', 'Warm interiors'],
        ],
    ];
}

function resolveHotelPdo($pdo = null)
{
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    return isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO ? $GLOBALS['pdo'] : null;
}

function parseHotelFeatures($features)
{
    if (is_array($features)) {
        return array_values(array_filter(array_map('trim', $features)));
    }

    if (!is_string($features) || trim($features) === '') {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $features))));
}

function serializeHotelFeatures(array $features)
{
    return implode(', ', array_values(array_filter(array_map('trim', $features))));
}

function ensureHotelPresentationSchema(PDO $pdo)
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $columns = $pdo->query("SHOW COLUMNS FROM hotels")->fetchAll(PDO::FETCH_COLUMN);
    $definitions = [
        'location' => "ALTER TABLE hotels ADD COLUMN location VARCHAR(120) NULL AFTER hotel_name",
        'image_path' => "ALTER TABLE hotels ADD COLUMN image_path VARCHAR(255) NULL AFTER description",
        'rating' => "ALTER TABLE hotels ADD COLUMN rating DECIMAL(3,1) NULL AFTER image_path",
        'reviews_count' => "ALTER TABLE hotels ADD COLUMN reviews_count INT NULL AFTER rating",
        'price_from' => "ALTER TABLE hotels ADD COLUMN price_from DECIMAL(10,2) NULL AFTER reviews_count",
        'badge_label' => "ALTER TABLE hotels ADD COLUMN badge_label VARCHAR(80) NULL AFTER price_from",
        'features' => "ALTER TABLE hotels ADD COLUMN features TEXT NULL AFTER badge_label",
    ];

    foreach ($definitions as $column => $sql) {
        if (!in_array($column, $columns, true)) {
            $pdo->exec($sql);
        }
    }

    $defaults = getDefaultHotelCatalog();
    $stmt = $pdo->prepare(
        "UPDATE hotels
         SET location = COALESCE(NULLIF(location, ''), :location),
             image_path = COALESCE(NULLIF(image_path, ''), :image_path),
             rating = COALESCE(rating, :rating),
             reviews_count = COALESCE(reviews_count, :reviews_count),
             price_from = COALESCE(price_from, :price_from),
             badge_label = COALESCE(NULLIF(badge_label, ''), :badge_label),
             features = COALESCE(NULLIF(features, ''), :features)
         WHERE hotel_key = :hotel_key"
    );

    foreach ($defaults as $hotelKey => $hotel) {
        $stmt->execute([
            'location' => $hotel['location'],
            'image_path' => $hotel['image'],
            'rating' => $hotel['rating'],
            'reviews_count' => $hotel['reviews'],
            'price_from' => $hotel['price_from'],
            'badge_label' => $hotel['label'],
            'features' => serializeHotelFeatures($hotel['features']),
            'hotel_key' => $hotelKey,
        ]);
    }

    $checked = true;
}

function getHotelCatalog($pdo = null)
{
    $defaults = getDefaultHotelCatalog();
    $pdo = resolveHotelPdo($pdo);

    if (!$pdo) {
        return $defaults;
    }

    ensureHotelPresentationSchema($pdo);

    $stmt = $pdo->query(
        "SELECT h.hotel_id, h.hotel_key, h.hotel_name, h.description, h.location, h.image_path, h.rating,
                h.reviews_count, h.price_from, h.badge_label, h.features,
                MIN(r.price) AS minimum_room_price,
                COUNT(r.room_id) AS room_count,
                SUM(CASE WHEN r.is_booked = 0 THEN 1 ELSE 0 END) AS available_room_count
         FROM hotels h
         LEFT JOIN rooms r ON r.hotel_id = h.hotel_id
         GROUP BY h.hotel_id, h.hotel_key, h.hotel_name, h.description, h.location, h.image_path, h.rating,
                  h.reviews_count, h.price_from, h.badge_label, h.features
         ORDER BY h.hotel_name ASC"
    );

    $catalog = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $fallback = $defaults[$row['hotel_key']] ?? [];
        $features = parseHotelFeatures($row['features'] ?? '');

        if (!$features && isset($fallback['features'])) {
            $features = $fallback['features'];
        }

        $catalog[$row['hotel_key']] = [
            'hotel_id' => (int) $row['hotel_id'],
            'hotel_key' => $row['hotel_key'],
            'name' => $row['hotel_name'] ?: ($fallback['name'] ?? 'Kathmandu Stay'),
            'location' => $row['location'] ?: ($fallback['location'] ?? 'Kathmandu, Nepal'),
            'image' => $row['image_path'] ?: ($fallback['image'] ?? 'image/hero-banner.jpg'),
            'rating' => (float) ($row['rating'] ?? ($fallback['rating'] ?? 4.5)),
            'reviews' => (int) ($row['reviews_count'] ?? ($fallback['reviews'] ?? 0)),
            'price_from' => (float) ($row['price_from'] ?? $row['minimum_room_price'] ?? ($fallback['price_from'] ?? 0)),
            'label' => $row['badge_label'] ?: ($fallback['label'] ?? 'Kathmandu Stay'),
            'description' => $row['description'] ?: ($fallback['description'] ?? 'Comfortable accommodation in Kathmandu.'),
            'features' => $features,
            'room_count' => (int) ($row['room_count'] ?? 0),
            'available_room_count' => (int) ($row['available_room_count'] ?? 0),
        ];
    }

    return $catalog ?: $defaults;
}

function getHotelMeta($hotelKey, $pdo = null)
{
    $catalog = getHotelCatalog($pdo);

    return $hotelKey && isset($catalog[$hotelKey]) ? $catalog[$hotelKey] : null;
}

function ensureRoomBookingSchema(PDO $pdo)
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS room_bookings (
            booking_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            hotel_id INT NOT NULL,
            customer_id INT NULL,
            customer_name VARCHAR(120) NULL,
            customer_email VARCHAR(150) NULL,
            room_name VARCHAR(120) NOT NULL,
            hotel_name VARCHAR(150) NOT NULL,
            amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            booking_status VARCHAR(30) NOT NULL DEFAULT 'confirmed',
            payment_reference VARCHAR(120) NULL,
            booked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            KEY idx_room_bookings_status (booking_status),
            KEY idx_room_bookings_room (room_id),
            KEY idx_room_bookings_customer (customer_id),
            UNIQUE KEY uniq_room_bookings_payment_reference (payment_reference)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $columns = $pdo->query("SHOW COLUMNS FROM room_bookings")->fetchAll(PDO::FETCH_COLUMN);
    $definitions = [
        'check_in_date' => "ALTER TABLE room_bookings ADD COLUMN check_in_date DATE NULL AFTER hotel_name",
        'check_out_date' => "ALTER TABLE room_bookings ADD COLUMN check_out_date DATE NULL AFTER check_in_date",
        'nights' => "ALTER TABLE room_bookings ADD COLUMN nights INT NOT NULL DEFAULT 1 AFTER check_out_date",
        'guests_count' => "ALTER TABLE room_bookings ADD COLUMN guests_count INT NOT NULL DEFAULT 1 AFTER nights",
        'special_request' => "ALTER TABLE room_bookings ADD COLUMN special_request TEXT NULL AFTER guests_count",
        'invoice_number' => "ALTER TABLE room_bookings ADD COLUMN invoice_number VARCHAR(50) NULL AFTER payment_reference",
    ];

    foreach ($definitions as $column => $sql) {
        if (!in_array($column, $columns, true)) {
            $pdo->exec($sql);
        }
    }

    $checked = true;
}

function recordRoomBooking(PDO $pdo, array $bookingData)
{
    ensureRoomBookingSchema($pdo);

    $paymentReference = trim((string) ($bookingData['payment_reference'] ?? ''));

    if ($paymentReference !== '') {
        $stmt = $pdo->prepare(
            "SELECT booking_id
             FROM room_bookings
             WHERE payment_reference = :payment_reference
             LIMIT 1"
        );
        $stmt->execute(['payment_reference' => $paymentReference]);
        $existingBookingId = $stmt->fetchColumn();

        if ($existingBookingId) {
            return (int) $existingBookingId;
        }
    }

    $invoiceNumber = trim((string) ($bookingData['invoice_number'] ?? ''));
    if ($invoiceNumber === '') {
        $invoiceNumber = generateInvoiceNumber();
    }

    $stmt = $pdo->prepare(
        "INSERT INTO room_bookings (
            room_id,
            hotel_id,
            customer_id,
            customer_name,
            customer_email,
            room_name,
            hotel_name,
            check_in_date,
            check_out_date,
            nights,
            guests_count,
            special_request,
            amount_paid,
            booking_status,
            payment_reference,
            invoice_number
        ) VALUES (
            :room_id,
            :hotel_id,
            :customer_id,
            :customer_name,
            :customer_email,
            :room_name,
            :hotel_name,
            :check_in_date,
            :check_out_date,
            :nights,
            :guests_count,
            :special_request,
            :amount_paid,
            :booking_status,
            :payment_reference,
            :invoice_number
        )"
    );
    $stmt->execute([
        'room_id' => (int) ($bookingData['room_id'] ?? 0),
        'hotel_id' => (int) ($bookingData['hotel_id'] ?? 0),
        'customer_id' => isset($bookingData['customer_id']) ? (int) $bookingData['customer_id'] : null,
        'customer_name' => trim((string) ($bookingData['customer_name'] ?? 'Guest')),
        'customer_email' => trim((string) ($bookingData['customer_email'] ?? '')),
        'room_name' => trim((string) ($bookingData['room_name'] ?? 'Room')),
        'hotel_name' => trim((string) ($bookingData['hotel_name'] ?? 'Hotel')),
        'check_in_date' => normalizeBookingDate($bookingData['check_in_date'] ?? null),
        'check_out_date' => normalizeBookingDate($bookingData['check_out_date'] ?? null),
        'nights' => max(1, (int) ($bookingData['nights'] ?? 1)),
        'guests_count' => max(1, (int) ($bookingData['guests_count'] ?? 1)),
        'special_request' => trim((string) ($bookingData['special_request'] ?? '')),
        'amount_paid' => (float) ($bookingData['amount_paid'] ?? 0),
        'booking_status' => trim((string) ($bookingData['booking_status'] ?? 'confirmed')),
        'payment_reference' => $paymentReference !== '' ? $paymentReference : null,
        'invoice_number' => $invoiceNumber,
    ]);

    return (int) $pdo->lastInsertId();
}

function ensureHotelReviewSchema(PDO $pdo)
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS hotel_reviews (
            review_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            hotel_id INT NOT NULL,
            booking_id INT NOT NULL,
            customer_id INT NOT NULL,
            customer_name VARCHAR(120) NOT NULL,
            rating INT NOT NULL,
            review_title VARCHAR(150) NULL,
            review_text TEXT NOT NULL,
            review_status VARCHAR(20) NOT NULL DEFAULT 'published',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_review_booking (booking_id),
            KEY idx_reviews_hotel (hotel_id),
            KEY idx_reviews_customer (customer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $checked = true;
}

function normalizeBookingDate($value)
{
    if (!$value) {
        return null;
    }

    $timestamp = strtotime((string) $value);

    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

function calculateStayNights($checkInDate, $checkOutDate)
{
    $checkIn = normalizeBookingDate($checkInDate);
    $checkOut = normalizeBookingDate($checkOutDate);

    if (!$checkIn || !$checkOut) {
        return 1;
    }

    $diff = (strtotime($checkOut) - strtotime($checkIn)) / 86400;

    return max(1, (int) $diff);
}

function generateInvoiceNumber()
{
    return 'TR-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
}

function getBookingDateRangeFromRequest()
{
    $checkInDate = normalizeBookingDate($_GET['check_in'] ?? $_POST['check_in'] ?? date('Y-m-d'));
    $checkOutDate = normalizeBookingDate($_GET['check_out'] ?? $_POST['check_out'] ?? date('Y-m-d', strtotime('+1 day')));

    if (!$checkInDate) {
        $checkInDate = date('Y-m-d');
    }

    if (!$checkOutDate || strtotime($checkOutDate) <= strtotime($checkInDate)) {
        $checkOutDate = date('Y-m-d', strtotime($checkInDate . ' +1 day'));
    }

    $guestsCount = max(1, (int) ($_GET['guests'] ?? $_POST['guests'] ?? 1));

    return [
        'check_in' => $checkInDate,
        'check_out' => $checkOutDate,
        'guests' => $guestsCount,
        'nights' => calculateStayNights($checkInDate, $checkOutDate),
    ];
}
//date overlap check algorithm 

function getUnavailableRoomIds(PDO $pdo, $hotelId, $checkInDate, $checkOutDate)
{
    ensureRoomBookingSchema($pdo);

    $checkInDate = normalizeBookingDate($checkInDate);
    $checkOutDate = normalizeBookingDate($checkOutDate);

    if (!$checkInDate || !$checkOutDate) {
        return [];
    }

    $stmt = $pdo->prepare(
        "SELECT DISTINCT room_id
         FROM room_bookings
         WHERE hotel_id = :hotel_id
           AND booking_status IN ('confirmed', 'completed')
           AND check_in_date IS NOT NULL
           AND check_out_date IS NOT NULL
           AND check_in_date < :check_out_date
           AND check_out_date > :check_in_date"
    );
    $stmt->execute([
        'hotel_id' => (int) $hotelId,
        'check_in_date' => $checkInDate,
        'check_out_date' => $checkOutDate,
    ]);

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function isRoomAvailableForDates(PDO $pdo, $roomId, $hotelId, $checkInDate, $checkOutDate)
{
    $unavailableRoomIds = getUnavailableRoomIds($pdo, $hotelId, $checkInDate, $checkOutDate);

    return !in_array((int) $roomId, $unavailableRoomIds, true);
}

function getBookingById(PDO $pdo, $bookingId)
{
    ensureRoomBookingSchema($pdo);

    $stmt = $pdo->prepare(
        "SELECT *
         FROM room_bookings
         WHERE booking_id = :booking_id
         LIMIT 1"
    );
    $stmt->execute(['booking_id' => (int) $bookingId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getBookingByIdForCustomer(PDO $pdo, $bookingId, $customerId)
{
    ensureRoomBookingSchema($pdo);

    $stmt = $pdo->prepare(
        "SELECT *
         FROM room_bookings
         WHERE booking_id = :booking_id
           AND customer_id = :customer_id
         LIMIT 1"
    );
    $stmt->execute([
        'booking_id' => (int) $bookingId,
        'customer_id' => (int) $customerId,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getCustomerBookings(PDO $pdo, $customerId)
{
    ensureRoomBookingSchema($pdo);

    $stmt = $pdo->prepare(
        "SELECT *
         FROM room_bookings
         WHERE customer_id = :customer_id
         ORDER BY booked_at DESC, booking_id DESC"
    );
    $stmt->execute(['customer_id' => (int) $customerId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createHotelReview(PDO $pdo, array $reviewData)
{
    ensureHotelReviewSchema($pdo);

    $stmt = $pdo->prepare(
        "INSERT INTO hotel_reviews (
            hotel_id, booking_id, customer_id, customer_name, rating, review_title, review_text, review_status
        ) VALUES (
            :hotel_id, :booking_id, :customer_id, :customer_name, :rating, :review_title, :review_text, :review_status
        )"
    );
    $stmt->execute([
        'hotel_id' => (int) $reviewData['hotel_id'],
        'booking_id' => (int) $reviewData['booking_id'],
        'customer_id' => (int) $reviewData['customer_id'],
        'customer_name' => trim((string) $reviewData['customer_name']),
        'rating' => max(1, min(5, (int) $reviewData['rating'])),
        'review_title' => trim((string) ($reviewData['review_title'] ?? '')),
        'review_text' => trim((string) $reviewData['review_text']),
        'review_status' => trim((string) ($reviewData['review_status'] ?? 'published')),
    ]);

    return (int) $pdo->lastInsertId();
}

function getHotelReviews(PDO $pdo, $hotelId, $limit = 6)
{
    ensureHotelReviewSchema($pdo);

    $stmt = $pdo->prepare(
        "SELECT *
         FROM hotel_reviews
         WHERE hotel_id = :hotel_id
           AND review_status = 'published'
         ORDER BY created_at DESC, review_id DESC
         LIMIT " . (int) $limit
    );
    $stmt->execute(['hotel_id' => (int) $hotelId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHotelReviewSummary(PDO $pdo, $hotelId)
{
    ensureHotelReviewSchema($pdo);

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS review_count, AVG(rating) AS average_rating
         FROM hotel_reviews
         WHERE hotel_id = :hotel_id
           AND review_status = 'published'"
    );
    $stmt->execute(['hotel_id' => (int) $hotelId]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'review_count' => (int) ($summary['review_count'] ?? 0),
        'average_rating' => $summary['average_rating'] !== null ? round((float) $summary['average_rating'], 1) : null,
    ];
}
