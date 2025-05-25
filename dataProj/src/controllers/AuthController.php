<?php
class AuthController {
    private $conn;
    
    public function __construct() {
        // Initialize database connection
        $this->conn = require __DIR__ . "/../config/database.php";
        if (!$this->conn) {
            die("Database connection failed.");
        }
    }

    private function validate($data) {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
  } 
    /**
     * Handle user login
     */
    public function login() {
      session_start();
      
      if(isset($_POST['email']) && isset($_POST['password'])) {
          $uname = $this->validate($_POST['email']);
          $pass = $this->validate($_POST['password']);
          
          if(empty($uname)) {
              header("Location: /src/views/auth/signin.php?error=User Name is required");
              exit();
          }
          if(empty($pass)) {
              header("Location: /src/views/auth/signin.php?error=Password is required");
              exit();
          }
          
          // Prepare SQL statement to prevent SQL injection
          $sql = "SELECT * FROM users WHERE username=? OR email=?";
          $stmt = mysqli_prepare($this->conn, $sql);
          
          if (!$stmt) {
              die("SQL error: " . mysqli_error($this->conn));
          }
          
          mysqli_stmt_bind_param($stmt, "ss", $uname, $uname);
          mysqli_stmt_execute($stmt);
          $result = mysqli_stmt_get_result($stmt);
          
          if(mysqli_num_rows($result) === 1) {
              $row = mysqli_fetch_assoc($result);
              
              if(password_verify($pass, $row['password_hash'])) {
                  // Store user information in session
                  $_SESSION['id'] = $row['user_id'];
                  $_SESSION['username'] = $row['username'];
                  $_SESSION['name'] = $row['name'];
                  $_SESSION['role'] = $row['role'];
                  
                  // Check user role and redirect accordingly
                  if($_SESSION['role'] === 'admin') {
                      // Redirect to admin dashboard
                      header("Location: /src/views/admin/dashboard.php");
                      exit();
                  } else {
                      // Redirect to user dashboard
                      header("Location: /src/views/user/dashboard.php");
                      exit();
                  }
              } else {
                  header("Location: /src/views/auth/signin.php?error=Incorrect password");
                  exit();
              }
          } else {
              header("Location: /src/views/auth/signin.php?error=User not found");
              exit();
          }
      } else {
          header("Location: /src/views/auth/signin.php");
          exit();
      }
  }
    
    /**
     * Handle user logout
     */
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: /");
        exit();
    }
    
   /**
 * Process user signup
 */
public function processSignup() {
  if (empty($_POST["name"])) {
      die("Full Name is required");
  }
  
  if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
      die("Valid email is required");
  }
  
  if (empty($_POST["username"]) || strlen($_POST["username"]) < 5) {
      die("Username is required and must be at least 5 characters");
  }
  
  $email = trim($_POST["email"]);
  
  // Check if the email already exists
  $email_check_stmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = ?");
  $email_check_stmt->bind_param("s", $email);
  $email_check_stmt->execute();
  $email_check_stmt->store_result();
  
  if ($email_check_stmt->num_rows > 0) {
      die("Email already taken");
  }
  $email_check_stmt->close();
  
  // Continue with password validation after confirming email is unique
  if (strlen($_POST["password"]) < 8) {
      die("Password must be at least 8 characters");
  }
  
  if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $_POST["password"])) {
      die("Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.");
  }
  
  if ($_POST["password"] !== $_POST["password_confirmation"]) {
      die("Passwords must match");
  }
  
  $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
  
  // No need to explicitly set created_at as it will be set automatically
  $sql = "INSERT INTO users (name, username, email, password_hash, role)
  VALUES (?, ?, ?, ?, ?)";
          
  $stmt = $this->conn->stmt_init();
  if (!$stmt->prepare($sql)) {
      die("SQL error: " . $this->conn->error);
  }
  
  // Trim inputs to prevent accidental whitespace issues
  $name = trim($_POST["name"]);
  $username = trim($_POST["username"]);
  
  // Assign role based on username or email
  $admin_email_domains = ["company.com", "admin.org"]; // Add allowed admin domains
  $email_domain = substr(strrchr($email, "@"), 1); // Extract domain from email
  
  if (str_ends_with($username, '-admin') || in_array($email_domain, $admin_email_domains) || str_starts_with($email, 'admin@')) {
      $role = "Admin"; // Must match ENUM('Admin', 'Applicant') exactly
  } else {
      $role = "Applicant"; // Default role for regular users
  }
  
  // Bind parameters
  $stmt->bind_param("sssss", $name, $username, $email, $password_hash, $role);
  
  try {
      if ($stmt->execute()) {
          // Get the new user's ID
          $newUserId = $this->conn->insert_id;
          
          // Notify admins about the new user
          require_once __DIR__ . '/NotificationController.php';
          $notificationController = new NotificationController();
          
          $title = "New User Registration";
          $message = "A new user has registered: {$name} ({$username})";
          $notificationController->notifyAdmins($title, $message, 'info');
          
          echo "Signup successful. Please log in.";
          // Redirect to login page
          header("Location: /src/views/auth/signin.php");
          exit();
      }
  } catch (mysqli_sql_exception $e) {
      if ($this->conn->errno === 1062) {
          die("Email already taken.");
      } else {
          die("An unexpected error occurred. Please try again later.");
      }
  }
}
}
?>