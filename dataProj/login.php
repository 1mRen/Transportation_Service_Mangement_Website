<?php 
session_start();

// Include database connection correctly
$conn = include "database.php"; 

if (!$conn) {
    die("Database connection failed.");
}

if(isset($_POST['email']) && isset($_POST['password'])){

  function validate($data){
    return htmlspecialchars(stripslashes(trim($data)));
  }

  $uname = validate($_POST['email']);
  $pass = validate($_POST['password']);

  if(empty($uname)){
    header("Location: signin.php?error=User Name is required");
    exit();
  } 

  if(empty($pass)){
    header("Location: signin.php?error=Password is required");
    exit();
  }

  // Prepare SQL statement to prevent SQL injection
  $sql = "SELECT * FROM users WHERE username=? OR email=?";
  $stmt = mysqli_prepare($conn, $sql);

  if (!$stmt) {
      die("SQL error: " . mysqli_error($conn));
  }

  mysqli_stmt_bind_param($stmt, "ss", $uname, $uname);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if(mysqli_num_rows($result) === 1){
    $row = mysqli_fetch_assoc($result);
    
    if(password_verify($pass, $row['password_hash'])){
      $_SESSION['id'] = $row['user_id'];
      $_SESSION['username'] = $row['username'];
      $_SESSION['name'] = $row['full_name'];
      $_SESSION['role'] = $row['role'];

      header("Location: home.php");
      exit();
    } else {
      header("Location: signin.php.php?error=Incorrect password");
      exit();
    }
  } else {
    header("Location: signin.php?error=User not found");
    exit();
  }
} else {
  header("Location: signin.php");
  exit();
}
