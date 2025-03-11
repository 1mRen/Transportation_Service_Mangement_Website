<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="logo/icon">
    <link rel="stylesheet" href="css/index.css">
    <title>Title</title>
  </head>
  <body>
    <nav>
        <a class="navbar-brand" href="index.php">
            <img class="logo" src="img/logor.png" alt="Logo">
        </a>
        <div class="menu-toggle" id="mobile-menu">☰</div>
        <div class="navbar-links" id="nav-links">
            <a href="feature.html">Features</a>
            <a href="Driving-hours.html">Driving Hours</a>
            <a href="help.html">Help/FAQ</a>
            <a href="ask-technician.html">Ask a Technician</a>
            <div class="auth-links">
                <a href="signin.php">Sign In</a>
                <a href="admin-signin.html">Admin</a>
            </div>
        </div>
    </nav>
    <div class="main-content">
        <h1>Optimize Your Journey and Maximize Your Profits with TMS</h1>
        <h2>The Game-Changer for Modern Logistics</h2>
        <button class="cta-button" onclick="location.href='signup.php'">Get Started</button>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const menuToggle = document.getElementById("mobile-menu");
            const navLinks = document.getElementById("nav-links");
            
            menuToggle.addEventListener("click", function () {
                navLinks.classList.toggle("active");
            });
        });
    </script>
</body>
</html>