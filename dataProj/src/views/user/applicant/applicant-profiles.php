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

// Get all applicants for the current user
$applicants = $applicantController->getAllApplicantsForUser();

// Include layout header
include_once '../../layout/layout.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">My Applicant Profiles</h2>
                        <a href="create_applicant.php" class="btn btn-primary">Create New Profile</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (empty($applicants)): ?>
                        <div class="alert alert-info" role="alert">
                            You haven't created any applicant profiles yet. Click "Create New Profile" to get started.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Contact</th>
                                        <th>Email</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applicants as $applicant): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($applicant['full_name']); ?></td>
                                            <td><?= htmlspecialchars($applicant['organization_department']); ?></td>
                                            <td><?= htmlspecialchars($applicant['position']); ?></td>
                                            <td><?= htmlspecialchars($applicant['contact_no']); ?></td>
                                            <td><?= htmlspecialchars($applicant['email']); ?></td>
                                            <td><?= date('M d, Y', strtotime($applicant['created_at'])); ?></td>
                                            <td>
                                                <a href="view_applicant.php?id=<?= $applicant['applicant_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="edit_applicant.php?id=<?= $applicant['applicant_id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal<?= $applicant['applicant_id']; ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?= $applicant['applicant_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete the applicant profile for <strong><?= htmlspecialchars($applicant['full_name']); ?></strong>?</p>
                                                                <?php 
                                                                $reservationCount = $applicantController->countReservations($applicant['applicant_id']);
                                                                if ($reservationCount > 0): 
                                                                ?>
                                                                    <div class="alert alert-warning">
                                                                        <i class="fas fa-exclamation-triangle"></i> This profile has <?= $reservationCount; ?> 
                                                                        reservation<?= $reservationCount > 1 ? 's' : ''; ?> and cannot be deleted.
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <?php if ($reservationCount == 0): ?>
                                                                    <a href="delete_applicant.php?id=<?= $applicant['applicant_id']; ?>" class="btn btn-danger">Delete</a>
                                                                <?php else: ?>
                                                                    <button type="button" class="btn btn-danger" disabled>Delete</button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include layout footer if you have one
// include_once '../../layout/footer.php';
?>