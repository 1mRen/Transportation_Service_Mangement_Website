<?php
// Import the layout class
require_once '../../views/layout/layout.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: /src/views/auth/signin.php");
    exit();
}

// Create a new Layout instance
$layout = new Layout('Create Applicant Profile', 'profile');

// Database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Check if user already has an applicant profile
$userId = $_SESSION['id'];
$checkQuery = "SELECT applicant_id FROM applicant WHERE user_id = ?";
$checkStmt = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($checkStmt, "i", $userId);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if (mysqli_fetch_assoc($checkResult)) {
    // User already has a profile, redirect to edit page
    header("Location: /src/views/applicant/edit.php");
    exit();
}

// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $contactNo = $_POST['contact_no'] ?? '';
    $email = $_POST['email'] ?? '';
    $organizationDepartment = $_POST['organization_department'] ?? '';
    $position = $_POST['position'] ?? '';

    // Validate inputs
    if (empty($fullName) || empty($contactNo) || empty($email) || empty($organizationDepartment) || empty($position)) {
        $error = "All fields are required.";
    } else {
        // Insert the applicant profile
        $insertQuery = "INSERT INTO applicant (user_id, full_name, contact_no, email, organization_department, position) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, "isssss", 
            $userId, $fullName, $contactNo, $email, $organizationDepartment, $position
        );

        if (mysqli_stmt_execute($insertStmt)) {
            $success = true;
            // Redirect to reservations page after 2 seconds
            header("refresh:2;url=/src/views/reservations/create.php");
        } else {
            $error = "Failed to create profile. Please try again.";
        }
    }
}

// Render the header
$layout->renderHeader();
?>

<!-- Page Header -->
<div class="page-header">
    <h1>Create Applicant Profile</h1>
    <div class="header-actions">
        <a href="/src/views/reservations/my-reservations.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Reservations
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Profile created successfully! Redirecting to create reservation...
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Profile Form -->
<div class="card">
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <div class="row g-3">
                <!-- Full Name -->
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" name="full_name" id="full_name" class="form-control" required>
                    <div class="invalid-feedback">Please enter your full name.</div>
                </div>

                <!-- Contact Number -->
                <div class="col-md-6">
                    <label for="contact_no" class="form-label">Contact Number</label>
                    <input type="tel" name="contact_no" id="contact_no" class="form-control" required>
                    <div class="invalid-feedback">Please enter your contact number.</div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>

                <!-- Organization/Department -->
                <div class="col-md-6">
                    <label for="organization_department" class="form-label">Organization/Department</label>
                    <input type="text" name="organization_department" id="organization_department" class="form-control" required>
                    <div class="invalid-feedback">Please enter your organization or department.</div>
                </div>

                <!-- Position -->
                <div class="col-md-6">
                    <label for="position" class="form-label">Position</label>
                    <input type="text" name="position" id="position" class="form-control" required>
                    <div class="invalid-feedback">Please enter your position.</div>
                </div>

                <!-- Submit Button -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Create Profile</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.form-label {
    font-weight: 500;
}

.invalid-feedback {
    font-size: 0.875em;
}
</style>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Email validation
document.getElementById('email').addEventListener('input', function() {
    const email = this.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!emailRegex.test(email)) {
        this.setCustomValidity('Please enter a valid email address');
    } else {
        this.setCustomValidity('');
    }
});

// Phone number validation
document.getElementById('contact_no').addEventListener('input', function() {
    const phone = this.value;
    const phoneRegex = /^[0-9+\-\s()]{10,}$/;
    
    if (!phoneRegex.test(phone)) {
        this.setCustomValidity('Please enter a valid phone number');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php
// Render the footer
$layout->renderFooter();
?> 