
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TITLE NAME - Sign Up</title>
    <link rel="icon" type="img/icon" href="img/iconr.png">
    <link rel="stylesheet" href="css/stylesr.css">
  </head>
  <body>
    <div class="navbar">
      <div class="logo">
        <a href="index.php">
          <img src="img/logor.png" alt="Logo">
        </a>
      </div>
      <div class="nav-links">
        <a href="feature.html">Features</a>
        <a href="library-hours.html">Driving Hours</a>
        <a href="help.html">Help/FAQ</a>
        <a href="ask-technician.html">Ask a Technician</a>
      </div>
    </div>
    <div class="main-container">
      <div class="inner-container">
        <div class="welcome-section">
          <h1>Welcome Back!</h1>
          <p>To keep connected with us please log in with your personal info</p>
          <button onclick="location.href='signin.php'">SIGN IN</button>
        </div>
        <div class="form-section">
          <div class="logo">
            <img src="img/logor.png" alt="Logo">
          </div>
          <h3>Sign up to continue to TITLE NAME</h3>
          <form action="process-signup.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br><br>
    
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>
    
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" minlength="5" required><br><br>
    
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" minlength="8" required><br><br>
    
            <label for="password_confirmation">Confirm Password:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" required><br><br>
    
            <button type="submit">Submit</button>
        </form>
        </div>
      </div>
    </div>
  </body>
</html>