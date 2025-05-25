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
$layout = new Layout('My Reservations', 'reservations');

// Database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Get user's applicant ID
$userId = $_SESSION['id'];
$applicantQuery = "SELECT applicant_id FROM applicant WHERE user_id = ?";
$applicantStmt = mysqli_prepare($conn, $applicantQuery);
mysqli_stmt_bind_param($applicantStmt, "i", $userId);
mysqli_stmt_execute($applicantStmt);
$applicantResult = mysqli_stmt_get_result($applicantStmt);
$applicant = mysqli_fetch_assoc($applicantResult);

if (!$applicant) {
    $_SESSION['error'] = "Please complete your applicant profile before viewing reservations.";
    header("Location: /src/views/applicant/create.php");
    exit();
}

$applicantId = $applicant['applicant_id'];

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';

// Build the query with filters
$query = "SELECT r.reservation_id, r.date_of_use, r.departure_area, r.destination,
          r.departure_time, r.return_time, r.purpose, r.status_id,
          v.plate_no, v.type_of_vehicle, v.vehicle_id, rs.status_name as reservation_status
          FROM reservation r
          LEFT JOIN vehicle v ON r.vehicle_id = v.vehicle_id
          JOIN reservation_status rs ON r.status_id = rs.status_id
          WHERE r.applicant_id = ?";

$params = [$applicantId];
$types = "i";

if ($status) {
    $query .= " AND rs.status_name = ?";
    $params[] = $status;
    $types .= "s";
}

if ($date) {
    $query .= " AND DATE(r.date_of_use) = ?";
    $params[] = $date;
    $types .= "s";
}

$query .= " ORDER BY r.date_of_use DESC";

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$reservations = [];

while ($row = mysqli_fetch_assoc($result)) {
    $reservations[] = $row;
}

// Get all statuses for the filter
$statusQuery = "SELECT DISTINCT status_name FROM reservation_status";
$statusResult = mysqli_query($conn, $statusQuery);
$statuses = [];

while ($row = mysqli_fetch_assoc($statusResult)) {
    $statuses[] = $row['status_name'];
}

// Render the header
$layout->renderHeader();
?>

<!-- Page Header -->
<div class="page-header">
    <h1>My Reservations</h1>
    <div class="header-actions">
        <a href="/src/views/reservations/create.php" class="btn btn-primary">
            <i class="fa fa-plus"></i> New Reservation
        </a>
    </div>
</div>

<!-- Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $statusOption): ?>
                        <option value="<?php echo $statusOption; ?>" <?php echo $status === $statusOption ? 'selected' : ''; ?>>
                            <?php echo $statusOption; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" id="date" class="form-control" value="<?php echo $date; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Reservations Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vehicle</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No reservations found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo $reservation['reservation_id']; ?></td>
                                <td>
                                    <?php
                                    if (!empty($reservation['vehicle_id']) && !empty($reservation['plate_no'])) {
                                        echo '<i class="fa fa-car"></i> ' . htmlspecialchars($reservation['type_of_vehicle']) . ' (' . htmlspecialchars($reservation['plate_no']) . ')';
                                    } else {
                                        echo '<span class="text-muted">Not assigned yet</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($reservation['date_of_use'])); ?></td>
                                <td>
                                    <?php echo date('h:i A', strtotime($reservation['departure_time'])); ?> - 
                                    <?php echo date('h:i A', strtotime($reservation['return_time'])); ?>
                                </td>
                                <td><?php echo $reservation['destination']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo strtolower($reservation['reservation_status']) === 'pending' ? 'warning' : 
                                            (strtolower($reservation['reservation_status']) === 'approved' ? 'success' : 'danger'); 
                                    ?>">
                                        <?php echo $reservation['reservation_status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/src/views/reservations/view.php?id=<?php echo $reservation['reservation_id']; ?>" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <?php if ($reservation['reservation_status'] == 'Pending'): ?>
                                            <a href="/src/views/reservations/cancel.php?id=<?php echo $reservation['reservation_id']; ?>" 
                                               class="btn btn-sm btn-danger" title="Cancel Reservation">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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

.table th {
    background-color: #f8f9fa;
}

.btn-group {
    display: flex;
    gap: 0.25rem;
}

.badge {
    padding: 0.5em 0.75em;
}

.vehicle-item {
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.vehicle-item:last-child {
    margin-bottom: 0;
}

.vehicle-item i {
    margin-right: 0.5rem;
    color: #6c757d;
}

.badge {
    font-size: 0.75rem;
    margin-left: 0.5rem;
}
</style>

<?php
// Render the footer
$layout->renderFooter();
?> 