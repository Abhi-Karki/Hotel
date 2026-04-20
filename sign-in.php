<?php
ob_start();
session_start();
require_once "config.php"; // Database connection

$loginError = "";
$loginEmail = "";

function passwordMatchesInput($plainPassword, $storedPassword)
{
    if (!is_string($storedPassword) || $storedPassword === '') {
        return false;
    }

    return password_verify($plainPassword, $storedPassword) || hash_equals($storedPassword, $plainPassword);
}

// Check if login form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['loginSubmitBtn'])) {
    $email = strtolower(trim($_POST['loginEmail']));
    $password = trim($_POST['loginPassword']);
    $loginEmail = $email;

    $stmt = $pdo->prepare("SELECT * FROM customer WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer && passwordMatchesInput($password, $customer['password'])) {
        $_SESSION["authenticated"] = "1";
        $_SESSION["userEmail"] = $customer['email'];
        $_SESSION["userName"] = $customer['fullname'];
        $_SESSION["userID"] = $customer['cid'];
        $_SESSION["username"] = $customer['fullname'];
        $_SESSION["accountEmail"] = $customer['email'];
        $_SESSION["isAdmin"] = !empty($customer['isadmin']) ? 1 : 0;
        $_SESSION["authSource"] = "customer";

        header("Location: " . (!empty($customer['isadmin']) ? "admin.php" : "user.php"));
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM administrator WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $administrator = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($administrator && passwordMatchesInput($password, $administrator['password'])) {
        $_SESSION["authenticated"] = "1";
        $_SESSION["userEmail"] = $administrator['email'];
        $_SESSION["userName"] = $administrator['fullname'];
        $_SESSION["userID"] = $administrator['adminId'];
        $_SESSION["username"] = $administrator['fullname'];
        $_SESSION["accountEmail"] = $administrator['email'];
        $_SESSION["isAdmin"] = 1;
        $_SESSION["authSource"] = "administrator";

        header("Location: admin.php");
        exit();
    }

    $loginError = "Invalid email or password.";
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sign in</title>
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/site-theme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

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

            <a href="register.php" class="btn btn-primary">Create Account</a>
        </div>
    </div>
</header>

<section class="auth-hero">
    <div class="container">
        <div class="hero-compact-grid">
            <div>
                <div class="hero-kicker">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    Guest access
                </div>
                <h1 class="hero-title-main">Sign in to continue your booking with a more polished guest experience.</h1>
                <p class="hero-copy">Your redesigned hotel platform now uses a cleaner booking flow. Sign in to view rooms, continue reservations, and manage future stays.</p>
            </div>

            <div class="hero-detail-card">
                <h3>What you can do after login</h3>
                <p>Browse featured hotels, open room-level pricing, and continue toward the booking flow in a more professional interface.</p>
                <div class="hero-detail-stat">
                    <span>Booking access</span>
                    <strong>Ready</strong>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="auth-shell">
    <div class="container">
        <div class="auth-layout">
            <div class="auth-side-card">
                <h2>Welcome back to Tourly.</h2>
                <p>We reshaped the guest area to feel more like a hotel product: quieter layout, clearer actions, and a better sense of trust across the booking journey.</p>

                <div class="auth-points">
                    <div class="auth-point">
                        <div class="auth-point-icon"><ion-icon name="bed-outline"></ion-icon></div>
                        <div>
                            <strong>Browse premium stays</strong>
                            <p>Move from the homepage into hotel and room selection more naturally.</p>
                        </div>
                    </div>
                    <div class="auth-point">
                        <div class="auth-point-icon"><ion-icon name="pricetag-outline"></ion-icon></div>
                        <div>
                            <strong>See clearer pricing</strong>
                            <p>Room cards now show cleaner pricing and status cues before booking.</p>
                        </div>
                    </div>
                    <div class="auth-point">
                        <div class="auth-point-icon"><ion-icon name="shield-checkmark-outline"></ion-icon></div>
                        <div>
                            <strong>Use a more credible flow</strong>
                            <p>From login to booking, the interface now feels more unified and product-ready.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-card-shell">
                <?php if ($loginError): ?>
                    <div class="alert alert-danger"><?= $loginError ?></div>
                <?php endif; ?>

                <div class="card border-secondary">
                    <div class="card-header text-center">
                        <h3 class="mb-0 my-2">Sign In</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" id="login-form" class="form">
                            <div class="form-group">
                                <label for="loginEmail">Email <span class="red-asterisk">*</span></label>
                                <input type="email" class="form-control" id="loginEmail" name="loginEmail" placeholder="Email address" value="<?= htmlspecialchars($loginEmail) ?>" autocomplete="email" required>
                            </div>
                            <div class="form-group">
                                <label for="loginPassword">Password <span class="red-asterisk">*</span></label>
                                <input type="password" class="form-control" id="loginPassword" name="loginPassword" placeholder="Password" autocomplete="current-password" required>
                            </div>
                            <div class="form-group">
                                <p>Not registered yet? <a href="register.php" class="text-primary">Create an account here.</a></p>
                            </div>
                            <div class="form-group auth-actions">
                                <a href="index.php" class="btn btn-outline-secondary">Back home</a>
                                <input type="submit" class="btn btn-primary" value="Sign in" name="loginSubmitBtn">
                            </div>
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
            <p class="footer-brand-copy">The sign-in experience now matches the rest of the redesigned booking site with cleaner trust cues and calmer layout decisions.</p>
        </div>

        <div>
            <h4>Guest access</h4>
            <ul class="footer-list">
                <li><a href="index.php">Homepage</a></li>
                <li><a href="hotel.php?city=kathmandu">Browse hotels</a></li>
                <li><a href="register.php">Create account</a></li>
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
            <a href="register.php">Create account</a>
            <a href="hotel.php?city=kathmandu">Hotels</a>
            <a href="index.php#hotels">Featured stays</a>
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
