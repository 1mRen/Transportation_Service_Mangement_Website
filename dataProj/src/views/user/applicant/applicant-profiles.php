<?php
// src/views/user/applicant_profiles.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /src/views/auth/signin.php");
    exit;
}

require_once '../../controllers/ReservationController.php';
$controller = new ReservationController();

// Get all applicant profiles for this user
$applicantProfiles = $controller->listApplicantProfiles();

// Check if there's a success/error message
$message = '';
$messageType = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'] ?? 'success';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

include_once '../layout/layout.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>My Applicant Profiles</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="/src/views/user/create_applicant.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Applicant Profile
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($applicantProfiles)): ?>
        <div class="alert alert-info">
            You don't have any applicant profiles yet. Create one to start making reservations.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($applicantProfiles as $profile): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><?= htmlspecialchars($profile['full_name']) ?></h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Department:</strong> <?= htmlspecialchars($profile['organization_department']) ?></p>
                            <p><strong>Position:</strong> <?= htmlspecialchars($profile['position']) ?></p>
                            <p><strong>Contact:</strong> <?= htmlspecialchars($profile['contact_no']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($profile['email']) ?></p>
                            <p><strong>Created:</strong> <?= date('M d, Y', strtotime($profile['created_at'])) ?></p>
                        </div>
                        <div class="card-footer">
                            <a href="/src/views/user/edit_applicant.php?id=<?= $profile['applicant_id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="/src/views/user/create_reservation.php?applicant_id=<?= $profile['applicant_id'] ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-calendar-plus"></i> Create Reservation
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>