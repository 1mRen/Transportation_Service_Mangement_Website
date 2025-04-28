<?php
// src/views/user/create_applicant.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /src/views/auth/signin.php");
    exit;
}

require_once '../../controllers/ReservationController.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ReservationController();
    
    // Validate inputs
    $errors = [];
    
    if (empty($_POST['full_name'])) {
        $errors[] = "Full name is required";
    }
    
    if (empty($_POST['organization_department'])) {
        $errors[] = "Organization/Department is required";
    }
    
    if (empty($_POST['position'])) {
        $errors[] = "Position is required";
    }
    
    if (empty($_POST['contact_no'])) {
        $errors[] = "Contact number is required";
    }
    
    if (empty($_POST['email'])) {
        $errors[] = "Email is required";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // If no errors, create applicant
    if (empty($errors)) {
        try {
            $result = $controller->createApplicant(
                $_POST['full_name'],
                $_POST['organization_department'],
                $_POST['position'],
                $_POST['contact_no'],
                $_POST['email']
            );
            
            if ($result) {
                $_SESSION['message'] = "Applicant profile created successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: /src/views/user/applicant_profiles.php");
                exit;
            } else {
                $errors[] = "Failed to create applicant profile. Please try again.";
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

include_once '../layout/layout.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3>Create New Applicant Profile</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="organization_department" class="form-label">Organization/Department</label>
                            <input type="text" class="form-control" id="organization_department" name="organization_department" 
                                value="<?= htmlspecialchars($_POST['organization_department'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="position" name="position" 
                                value="<?= htmlspecialchars($_POST['position'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_no" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact_no" name="contact_no" 
                                value="<?= htmlspecialchars($_POST['contact_no'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/src/views/user/applicant_profiles.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Applicant Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>