<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMS - Create Account | Transport Management System</title>
    <link rel="icon" type="image/png" href="/public/assets/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../../../public/assets/css/stylesr.css">
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <a href="/">
                <img src="../../../public/assets/img/logo.png" alt="TMS Logo">
            </a>
        </div>
    </div>
    
    <div class="main-container">
        <div class="inner-container">
            <div class="form-section">
                <div class="logo">
                    <img src="../../../public/assets/img/logo.png" alt="TMS Logo">
                </div>
                <h3>Create Your Account</h3>
                
                <?php if(isset($_GET['error'])) { ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php } ?>
                
                  <form action="/src/process-signup.php" method="post" id="signup-form">
                      <input type="hidden" name="action" value="signup">
                      
                      <div class="input-group">
                          <label for="fullname">Full Name</label>
                          <input type="text" id="fullname" name="name" placeholder="Enter your full name" required>
                          <i class="fas fa-user"></i>
                      </div>
                    
                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                        <i class="fas fa-envelope"></i>
                    </div>
                    
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Choose a username" required>
                        <i class="fas fa-at"></i>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                        <i class="fas fa-lock"></i>
                        <i class="fas fa-eye password-toggle"></i>
                    </div>
                    
                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="password_confirmation" placeholder="Confirm your password" required>
                        <i class="fas fa-lock"></i>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the <a href="/terms.html">Terms of Service</a> and <a href="/privacy.html">Privacy Policy</a></label>
                    </div>
                    
                    <button type="submit">Create Account</button>
                </form>
                
                <div class="social-login">
                    <p>Or sign up with</p>
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
                <h1>Welcome Back!</h1>
                <p>Already have an account? Sign in to continue your logistics journey with us</p>
                <button onclick="location.href='signin.php'">Sign In</button>
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
        
        // Password match validation
        const password = document.querySelector('#password');
        const confirmPassword = document.querySelector('#confirm_password');
        const form = document.querySelector('#signup-form');
        
        form.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                
                // Create or update error message
                let errorDiv = document.querySelector('.error-message');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-exclamation-circle';
                    errorDiv.appendChild(icon);
                    errorDiv.appendChild(document.createTextNode(' Passwords do not match'));
                    
                    const heading = document.querySelector('h3');
                    form.insertBefore(errorDiv, heading.nextSibling);
                } else {
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Passwords do not match';
                }
                
                // Highlight password fields
                document.querySelector('#password').parentElement.classList.add('error');
                document.querySelector('#confirm_password').parentElement.classList.add('error');
            }
        });
        
        // Password strength checker
        const passwordStrengthBar = document.createElement('div');
        passwordStrengthBar.className = 'password-strength';
        passwordStrengthBar.innerHTML = `
            <div class="strength-bar">
                <div class="bar-progress"></div>
            </div>
            <span class="strength-text">Password Strength</span>
        `;
        
        password.parentElement.appendChild(passwordStrengthBar);
        
        password.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            const progress = passwordStrengthBar.querySelector('.bar-progress');
            const text = passwordStrengthBar.querySelector('.strength-text');
            
            // Remove all classes
            progress.className = 'bar-progress';
            
            if (strength === 0) {
                progress.style.width = '0%';
                text.textContent = 'Password Strength';
            } else if (strength < 2) {
                progress.style.width = '25%';
                progress.classList.add('weak');
                text.textContent = 'Weak';
            } else if (strength < 3) {
                progress.style.width = '50%';
                progress.classList.add('medium');
                text.textContent = 'Medium';
            } else if (strength < 4) {
                progress.style.width = '75%';
                progress.classList.add('good');
                text.textContent = 'Good';
            } else {
                progress.style.width = '100%';
                progress.classList.add('strong');
                text.textContent = 'Strong';
            }
        });
        
        function checkPasswordStrength(password) {
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            
            // Contains lowercase
            if (password.match(/[a-z]/)) strength += 1;
            
            // Contains uppercase
            if (password.match(/[A-Z]/)) strength += 1;
            
            // Contains number
            if (password.match(/\d/)) strength += 1;
            
            // Contains special char
            if (password.match(/[^a-zA-Z\d]/)) strength += 1;
            
            return strength;
        }
        
        // Username availability checker
        const usernameInput = document.querySelector('#username');
        
        usernameInput.addEventListener('blur', function() {
            if (this.value.length > 0) {
                // Simulate checking username availability
                // In a real implementation, this would make an AJAX call to check
                const usernameStatus = document.createElement('div');
                usernameStatus.className = 'username-status available';
                usernameStatus.innerHTML = '<i class="fas fa-check-circle"></i> Username available';
                
                // Remove any existing status
                const existingStatus = this.parentElement.querySelector('.username-status');
                if (existingStatus) {
                    existingStatus.remove();
                }
                
                this.parentElement.appendChild(usernameStatus);
            }
        });
        
        // Email validation
        const emailInput = document.querySelector('#email');
        
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.parentElement.classList.add('error');
                
                let errorMsg = this.parentElement.querySelector('.input-error');
                if (!errorMsg) {
                    errorMsg = document.createElement('span');
                    errorMsg.className = 'input-error';
                    errorMsg.textContent = 'Please enter a valid email address';
                    this.parentElement.appendChild(errorMsg);
                }
            } else {
                this.parentElement.classList.remove('error');
                const errorMsg = this.parentElement.querySelector('.input-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });
    </script>
</body>
</html>