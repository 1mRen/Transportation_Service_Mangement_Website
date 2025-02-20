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
        <img div class="logo" src="img/logor.png" width="200" height="50" />
      </a>
      <div class="navbar-links">
        <a href="feature.html"> Features </a>
        <a href="Driving-hours.html"> Driving Hours </a>
        <a href="help.html"> Help/FAQ </a>
        <a href="ask-technician.html"> Ask a Technician</a>
      </div>
      <div class="button-container">
        <button class="sign-in-button">
          <a href="signin.php">Sign In</a>
        </button>
        <button class="admin-button">
          <a href="admin-signin.html">Admin</a>
        </button>
      </div>
    </nav>
    <div class="main-content">
      <h1>Optimize Your journey and Maximize Your Profits with TMS</h1>
      <h2>The Game-Changer for Modern Logistics</h2>
      <button class="cta-button" onclick="location.href='signup.php'">Get Started</button>
    </div>
    <script>
      document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
      });
      document.onkeydown = function(e) {
        if (e.keyCode === 123) {
          return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode === 'I'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode === 'J'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode === 'U'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode === 'C'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode === 'V'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.keyCode === 'S'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.keyCode === 'P'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.keyCode === 'U'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.keyCode === 'C'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.keyCode === 'V'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.keyCode === 'X'.charCodeAt(0)) {
          return false;
        }
        if (e.ctrlKey && e.keyCode === 'A'.charCodeAt(0)) {
          return false;
        }
      };
      document.addEventListener('selectstart', function(e) {
        e.preventDefault();
      });
      document.addEventListener('copy', function(e) {
        e.preventDefault();
      });
    </script>
  </body>
</html>