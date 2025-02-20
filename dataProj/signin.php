<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TITLE NAME - Sign In</title>
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
        <a href="Driving-hours.html">Driving Hours</a>
        <a href="help.html">Help/FAQ</a>
        <a href="ask-technician.html">Ask a Technician</a>
      </div>
    </div>
    <div class="main-container">
      <div class="inner-container">
        <div class="form-section">
          <div class="logo">
            <img src="img/logor.png" alt="Logo">
          </div>
          <h3>Sign in to continue to TITLE NAME</h3>

          
          <?php if(isset($_GET['error'])) { ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
          <?php } ?>
          <form action="login.php" method="post" id="signin-form">
            <input type="hidden" name="action" value="signin">
            <label for="email">UserName/Email:</label>
            <input type="text" id="email" name="email" placeholder="email@sample.com/Username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="************" required>
            <button type="submit">SIGN IN</button>
          </form>
        </div>
        <div class="welcome-section">
          <h1>Hello, Friends!</h1>
          <p>Enter your personal details and start commute with us</p>
          <button onclick="location.href='signup.php'">SIGN UP</button>
        </div>
      </div>
    </div>

  </body>
</html>