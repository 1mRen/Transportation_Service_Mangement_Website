<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/signin.php");
    exit;
}

// Include the ApplicantController
require_once __DIR__ . '/../../../controllers/ApplicantController.php';

// Create a new ApplicantController instance
$applicantController = new ApplicantController();

// Check if an ID was provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: applicant-profiles.php");
    exit;
}

$applicantId = (int)$_GET['id'];

// Check if the applicant exists and belongs to the current user
$applicant = $applicantController->getApplicantById($applicantId);
if (!$applicant || !$applicantController->userOwnsApplicant($applicantId)) {
    $_SESSION['error_message'] = "Applicant profile not found or you do not have permission to edit it.";
    header("Location: applicant-profiles.php");
    exit;
}

// Include layout header
include_once '../../layout/layout.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Edit Applicant Profile</h2>
                        <a href="applicant-profiles.php" class="btn btn-secondary">Back to Profiles</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <form action="../../../src/controllers/applicant_process.php" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="applicant_id" value="<?= $applicantId; ?>">
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?= htmlspecialchars($applicant['full_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="organization_department" class="form-label">Organization/Department <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="organization_department" name="organization_department" 
                                   value="<?= htmlspecialchars($applicant['organization_department']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="position" name="position" 
                                   value="<?= htmlspecialchars($applicant['position']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_no" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_no" name="contact_no" 
                                   value="<?= htmlspecialchars($applicant['contact_no']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($applicant['email']); ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include layout footer if you have one
// include_once '../../layout/footer.php';
?>