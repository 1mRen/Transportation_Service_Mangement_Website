<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMS - Transport Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/assets/css/index.css">
</head>
<body class="lazy-background">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="/api/placeholder/150/50" alt="TMS Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#features">
                            <i class="fas fa-star"></i> Features
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#hours">
                            <i class="fas fa-clock"></i> Driving Hours
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">
                            <i class="fas fa-question-circle"></i> Help/FAQ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#support">
                            <i class="fas fa-headset"></i> Ask a Technician
                        </a>
                    </li>
                </ul>
                <div class="ms-auto">
                    <button class="btn btn-outline-light" onclick="onclick=location.href='src/views/auth/signin.php'">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="content-container">
        <div class="hero-container">
            <h1>Optimize Your Journey and Maximize Your Profits with TMS</h1>
            <h2>The Game-Changer for Modern Logistics</h2>
            
            <div class="cta-section">
                <button class="cta-button" onclick="location.href='src/views/auth/signup.php'">Get Started <i class="fas fa-arrow-right"></i></button>
                <button class="cta-button secondary-cta">See Demo <i class="fas fa-play"></i></button>
            </div>
            
            <div class="features-preview">
                <div class="feature-item">
                    <i class="fas fa-route"></i>
                    <h3>Route Optimization</h3>
                    <p>Save up to 30% on fuel costs with AI-powered route planning</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-line"></i>
                    <h3>Analytics Dashboard</h3>
                    <p>Real-time insights to make data-driven decisions</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Mobile Access</h3>
                    <p>Manage your fleet anytime, anywhere from any device</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Lazy load background
        window.addEventListener('load', function() {
            document.body.classList.add('loaded');
        });
        
        // Active link handling
        document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.navbar-nav .nav-link').forEach(l => {
                    l.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>