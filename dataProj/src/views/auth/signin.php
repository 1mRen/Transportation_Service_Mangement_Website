<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMS - Sign In | Transport Management System</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="/public/assets/css/stylesr.css">
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <a href="/">
                <img src="/public/assets/img/logo.jpg" alt="TMS Logo">
            </a>
        </div>
        <div class="nav-links">
            <a href="/feature.html"><i class="fas fa-star"></i> <span>Features</span></a>
            <a href="/Driving-hours.html"><i class="fas fa-clock"></i> <span>Driving Hours</span></a>
            <a href="/help.html"><i class="fas fa-question-circle"></i> <span>Help/FAQ</span></a>
            <a href="/ask-technician.html"><i class="fas fa-headset"></i> <span>Support</span></a>
        </div>
    </div>
    
    <div class="main-container">
        <div class="inner-container">
            <div class="form-section">
              <div class="back-button">
                  <a href="../../../index.php">
                      <i class="fas fa-arrow-left"></i>
                      <span>Back to Home</span>
                  </a>
              </div>
                <div class="logo">
                    <img src="/assets/img/logo.jpg" alt="TMS Logo">
                </div>
                <h3>Welcome Back</h3>
                
                <?php if(isset($_GET['error'])) { ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php } ?>
                
                <form action="/src/login.php" method="post" id="signin-form">
                    <input type="hidden" name="action" value="signin">
                    
                    <div class="input-group">
                        <label for="email">Username or Email</label>
                        <input type="text" id="email" name="email" placeholder="Enter your username or email" required>
                        <i class="fas fa-user"></i>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-lock"></i>
                        <i class="fas fa-eye password-toggle"></i>
                    </div>
                    
                    <div class="form-footer">
                        <div class="checkbox-group">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="/forgot-password.php">Forgot Password?</a>
                    </div>
                    
                    <button type="submit">Sign In</button>
                </form>
                
                <div class="social-login">
                    <p>Or continue with</p>
                    <div class="social-icons">
                        <div class="social-icon google">
                            <i class="fab fa-google"></i>
                        </div>
                        <div class="social-icon facebook">
                            <i class="fab fa-facebook-f"></i>
                        </div>
                        <div class="social-icon apple">
                            <i class="fab fa-apple"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="welcome-section">
                <h1>Hello, Friends!</h1>
                <p>Enter your personal details and start optimizing your logistics with us</p>
                <button onclick="location.href='signup.php'">Create Account</button>
            </div>
        </div>
    </div>
    
    <script>
        // Add focus effects to input fields
        const inputGroups = document.querySelectorAll('.input-group');
        inputGroups.forEach(group => {
            const input = group.querySelector('input');
            input.addEventListener('focus', () => {
                group.classList.add('focused');
            });
            input.addEventListener('blur', () => {
                if (!input.value) {
                    group.classList.remove('focused');
                }
            });
        });
        
        // Password visibility toggle
        const passwordToggle = document.querySelector('.password-toggle');
        const passwordInput = document.querySelector('#password');
        
        passwordToggle.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            passwordToggle.classList.toggle('fa-eye');
            passwordToggle.classList.toggle('fa-eye-slash');
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>