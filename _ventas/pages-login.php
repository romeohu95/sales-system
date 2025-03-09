<?php
include('partials/html.php');
include 'includes/config/settings.php'; 

function decryptCookie($data, $key) {
    $encryption_key = base64_encode(hash('sha256', $key, true));
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}

$remember_me_checked = false;
$remember_me_email = '';
$remember_me_password = '';

if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
    $remember_me_checked = true;
    $remember_me_email = decryptCookie($_COOKIE['email'], $publicKeyToken);
    $remember_me_password = '********************';
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : $remember_me_email;
?>

<head>
    <?php 
    $title = "Log In | Hyper - Responsive Bootstrap 5 Admin Dashboard";
    include('partials/title-meta.php'); 
    ?>

    <!-- Theme Config Js -->
    <script src="assets/js/hyper-config.js"></script>

    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="assets/css/app-saas.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
</head>

<body class="authentication-bg pb-0">
    <div class="auth-fluid">
        <!--Auth fluid left content -->
        <div class="auth-fluid-form-box">
            <div class="card-body d-flex flex-column h-100 gap-3">

                <!-- Logo -->
                <div class="auth-brand text-center text-lg-start">
                    <a href="index.php" class="logo-dark">
                        <span><img src="assets/images/logo-dark.png" alt="dark logo" height="22"></span>
                    </a>
                    <a href="index.php" class="logo-light">
                        <span><img src="assets/images/logo.png" alt="logo" height="22"></span>
                    </a>
                </div>

                <div class="my-auto">
                    <!-- title-->
                    <h4 class="mt-0">Sign In</h4>
                    <p class="text-muted mb-4">Enter your email address and password to access account.</p>

                    <!-- display error message -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php if ($error == 'User does not exist'): ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Clear the fields if the user does not exist
                                    document.getElementById('emailaddress').value = '';
                                    document.getElementById('password').value = '';
                                    // Set focus on the email input
                                    document.getElementById('emailaddress').focus();
                                });
                            </script>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- form -->
                    <form action="login-handler.php" method="POST" id="login-form">
                        <div class="mb-3">
                            <label for="emailaddress" class="form-label">Email address</label>
                            <input class="form-control" type="email" id="emailaddress" name="email" required="" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" tabindex="1">
                        </div>
                        <div class="mb-3">
                            <a href="pages-recoverpw-2.html" class="text-muted float-end"><small>Forgot your password?</small></a>
                            <label for="password" class="form-label">Password</label>
                            <input class="form-control" type="password" id="password" name="password" required="" placeholder="Enter your password" value="<?php echo $remember_me_password; ?>" <?php echo $remember_me_checked ? 'disabled' : ''; ?> tabindex="2">
                        </div>
                        <input type="hidden" name="use_cookie" id="use_cookie" value="<?php echo $remember_me_checked ? '1' : '0'; ?>">
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkbox-signin" name="remember_me" <?php echo $remember_me_checked ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="checkbox-signin">Remember me</label>
                            </div>
                        </div>
                        <div class="d-grid mb-0 text-center">
                            <button class="btn btn-primary" type="submit" tabindex="3" id="login-button"><i class="mdi mdi-login"></i> Log In </button>
                        </div>
                        <!-- social-->
                        <div class="text-center mt-4">
                            <p class="text-muted font-16">Sign in with</p>
                            <ul class="social-list list-inline mt-3">
                                <li class="list-inline-item">
                                    <a href="javascript: void(0);" class="social-list-item border-primary text-primary"><i class="mdi mdi-facebook"></i></a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="javascript: void(0);" class="social-list-item border-danger text-danger"><i class="mdi mdi-google"></i></a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="javascript: void(0);" class="social-list-item border-info text-info"><i class="mdi mdi-twitter"></i></a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="javascript: void(0);" class="social-list-item border-secondary text-secondary"><i class="mdi mdi-github"></i></a>
                                </li>
                            </ul>
                        </div>
                    </form>
                    <!-- end form-->
                </div>

                <!-- Footer-->
                <footer class="footer footer-alt">
                    <p class="text-muted">Don't have an account? <a href="pages-register-2.html" class="text-muted ms-1"><b>Sign Up</b></a></p>
                </footer>

            </div> <!-- end .card-body -->
        </div>
        <!-- end auth-fluid-form-box-->

        <!-- Auth fluid right content -->
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <h2 class="mb-3">I love the color!</h2>
                <p class="lead"><i class="mdi mdi-format-quote-open"></i> It's a elegant template. I love it very much! <i class="mdi mdi-format-quote-close"></i>
                </p>
                <p>
                    - Hyper Admin User
                </p>
            </div> <!-- end auth-user-testimonial-->
        </div>
        <!-- end Auth fluid right content -->
    </div>
    <!-- end auth-fluid-->
    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('emailaddress');
            const passwordInput = document.getElementById('password');
            const rememberMeCheckbox = document.getElementById('checkbox-signin');
            const useCookieInput = document.getElementById('use_cookie');
            const loginButton = document.getElementById('login-button');

            if (rememberMeCheckbox.checked) {
                passwordInput.disabled = true;
            }

            emailInput.addEventListener('input', function() {
                passwordInput.disabled = false;
                passwordInput.value = '';
                useCookieInput.value = '0';
            });

            <?php if ($error == 'Incorrect password'): ?>
                passwordInput.disabled = false;
                passwordInput.focus();
                useCookieInput.value = '0';
            <?php elseif ($error == 'User does not exist'): ?>
                emailInput.focus();
            <?php elseif ($remember_me_checked): ?>
                loginButton.focus();
            <?php else: ?>
                emailInput.focus();
            <?php endif; ?>

            const loginForm = document.getElementById('login-form');
            loginForm.addEventListener('submit', function(event) {
                if (passwordInput.disabled) {
                    passwordInput.value = '********************';
                }
            });
        });
    </script>
</body>

</html>