<?php
ob_start();
session_start();
require_once "config.php"; // Include your PDO database connection

// Redirect if already logged in
//if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"] == "1") {
  //  header("Location: index.php");
    //exit();
//}

// Initialize error messages
$errors = [
    "name" => "",
    "phone" => "",
    "email" => "",
    "password" => "",
    "confirm_password" => "",
    "general" => ""
];

// Initialize old values
$old = [
    "name" => "",
    "phone" => "",
    "email" => ""
];

$successMessage = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registerSubmitBtn'])) {
    $name = trim($_POST['registrationFullName']);
    $phone = trim($_POST['registrationPhoneNumber']);
    $email = strtolower(trim($_POST['registrationEmail']));
    $password = $_POST['registrationPassword'];
    $confirm_password = $_POST['registrationPassword2'];

    $old['name'] = $name;
    $old['phone'] = $phone;
    $old['email'] = $email;

    // Name validation
    if (empty($name)) {
        $errors['name'] = "Name is required";
    }

    // Phone validation: allow international and local formats
    if (empty($phone)) {
        $errors['phone'] = "Phone number is required";
    } elseif (!preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) {
        $errors['phone'] = "Enter a valid phone number";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address";
    }

    // Password validation: min 6 chars, letters & numbers
    if (strlen($password) < 6 || !preg_match("/[A-Za-z]/", $password) || !preg_match("/[0-9]/", $password)) {
        $errors['password'] = "Password must be at least 6 characters and contain letters and numbers";
    }

    // Confirm password
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    // Check if email already exists in the database
    if (empty($errors['email'])) {
        $stmt = $pdo->prepare("SELECT * FROM customer WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $errors['email'] = "This email is already registered";
        }
    }

    // If no errors, insert into database
    $hasErrors = false;
    foreach ($errors as $err) {
        if (!empty($err)) {
            $hasErrors = true;
            break;
        }
    }

    if (!$hasErrors) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash password
            $stmt = $pdo->prepare("INSERT INTO customer (fullname, email, password, phone) VALUES (:fullname, :email, :password, :phone)");
            $stmt->bindParam(':fullname', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();

            $successMessage = "Registration successful! You can now <a href='sign-in.php'>login</a>.";
            $old = ["name"=>"","phone"=>"","email"=>""];
        } catch (PDOException $e) {
            $errors['general'] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register</title>
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/site-theme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .red-asterisk { color: red; }
        .error-text { color: red; font-size: 0.9rem; margin-top: 5px; }
    </style>
</head>
<body class="site-theme auth-page">

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

            <a href="sign-in.php" class="btn btn-primary">Sign In</a>
        </div>
    </div>
</header>

<section class="auth-hero">
    <div class="container">
        <div class="hero-compact-grid">
            <div>
                <div class="hero-kicker">
                    <ion-icon name="person-add-outline"></ion-icon>
                    Guest registration
                </div>
                <h1 class="hero-title-main">Create your Tourly guest account in a cleaner, more premium booking experience.</h1>
                <p class="hero-copy">Registration now feels part of the same professional hotel journey, with a more confident layout and a stronger product feel across the site.</p>
            </div>

            <div class="hero-detail-card">
                <h3>Why create an account</h3>
                <p>Accounts make it easier to move through the redesigned flow, view room options, and continue future bookings with less friction.</p>
                <div class="hero-detail-stat">
                    <span>Guest setup</span>
                    <strong>Fast</strong>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="auth-shell">
    <div class="container">
        <div class="auth-layout">
            <div class="auth-side-card">
                <h2>Join a more polished hotel platform.</h2>
                <p>The redesigned site now feels more like a real hospitality product, and account creation is part of that cleaner guest journey.</p>

                <div class="auth-points">
                    <div class="auth-point">
                        <div class="auth-point-icon"><ion-icon name="flash-outline"></ion-icon></div>
                        <div>
                            <strong>Quick access to rooms</strong>
                            <p>Go from homepage discovery to hotel and room browsing in a much smoother flow.</p>
                        </div>
                    </div>
                    <div class="auth-point">
                        <div class="auth-point-icon"><ion-icon name="heart-outline"></ion-icon></div>
                        <div>
                            <strong>Guest-friendly design</strong>
                            <p>Less clutter, stronger hierarchy, and more confidence in every step of the experience.</p>
                        </div>
                    </div>
                    <div class="auth-point">
                        <div class="auth-point-icon"><ion-icon name="home-outline"></ion-icon></div>
                        <div>
                            <strong>Better hotel browsing</strong>
                            <p>Discover Kathmandu properties through a layout that now feels more premium and intentional.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-card-shell">
                <?php if(!empty($successMessage)) { ?>
                    <div class="alert alert-success"><?= $successMessage ?></div>
                <?php } ?>

                <div class="card border-secondary">
                    <div class="card-header text-center">
                        <h3 class="mb-0 my-2">Create Account</h3>
                    </div>
                    <div class="card-body">
                        <form class="form" role="form" autocomplete="off" method="post">
                            <div class="form-group">
                                <label for="registrationFullName">Name</label>
                                <input type="text" class="form-control" id="registrationFullName" name="registrationFullName" placeholder="Full name" value="<?= htmlspecialchars($old['name']) ?>" autocomplete="name" required>
                                <?php if($errors['name']) echo "<div class='error-text'>{$errors['name']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <label for="registrationPhoneNumber">Phone Number <span class="red-asterisk">*</span></label>
                                <input type="text" class="form-control" id="registrationPhoneNumber" name="registrationPhoneNumber" placeholder="+977 98XXXXXXXX" value="<?= htmlspecialchars($old['phone']) ?>" autocomplete="tel" required>
                                <?php if($errors['phone']) echo "<div class='error-text'>{$errors['phone']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <label for="registrationEmail">Email <span class="red-asterisk">*</span></label>
                                <input type="email" class="form-control" id="registrationEmail" name="registrationEmail" placeholder="email@domain.com" value="<?= htmlspecialchars($old['email']) ?>" autocomplete="email" required>
                                <?php if($errors['email']) echo "<div class='error-text'>{$errors['email']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <label for="registrationPassword">Password <span class="red-asterisk">*</span></label>
                                <input type="password" class="form-control" id="registrationPassword" name="registrationPassword" placeholder="Password" autocomplete="new-password" required>
                                <?php if($errors['password']) echo "<div class='error-text'>{$errors['password']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <label for="registrationPassword2">Confirm Password <span class="red-asterisk">*</span></label>
                                <input type="password" class="form-control" id="registrationPassword2" name="registrationPassword2" placeholder="Retype password" autocomplete="new-password" required>
                                <?php if($errors['confirm_password']) echo "<div class='error-text'>{$errors['confirm_password']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <p>Already registered? <a href="sign-in.php">Sign in here.</a></p>
                            </div>

                            <div class="form-group auth-actions">
                                <a href="index.php" class="btn btn-outline-secondary">Back home</a>
                                <input type="submit" class="btn btn-primary" name="registerSubmitBtn" value="Create account">
                            </div>

                            <?php if($errors['general']) echo "<div class='error-text'>{$errors['general']}</div>"; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <div>
            <a href="index.php" class="logo mb-3"><img src="image/logo.svg" alt="Tourly logo"></a>
            <p class="footer-brand-copy">The registration experience now fits the rest of the redesigned hotel site with more confidence, cleaner spacing, and clearer guest actions.</p>
        </div>

        <div>
            <h4>Start here</h4>
            <ul class="footer-list">
                <li><a href="index.php">Homepage</a></li>
                <li><a href="hotel.php?city=kathmandu">Browse hotels</a></li>
                <li><a href="sign-in.php">Sign in</a></li>
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
            <a href="sign-in.php">Guest login</a>
            <a href="hotel.php?city=kathmandu">Hotel listing</a>
            <a href="index.php#hotels">Featured stays</a>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.3.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="js/script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
