<?php
// Import the layout class
require_once '../../views/layout/layout.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['id']) || strtolower($_SESSION['role']) !== 'admin') {
    // If not logged in or not admin, redirect to appropriate page
    if (!isset($_SESSION['id'])) {
        header("Location: /src/views/auth/signin.php");
    } else {
        header("Location: /src/views/user/dashboard.php");
    }
    exit();
}

// Create a new Layout instance
$layout = new Layout('Admin Dashboard', 'dashboard');

// Get admin data from session
$adminId = $_SESSION['id'];
$adminName = $_SESSION['name'];

// Database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Count totals for dashboard
$countQueries = [
    'users' => "SELECT COUNT(*) FROM users WHERE role = 'Applicant'",
    'vehicles' => "SELECT COUNT(*) FROM vehicle",
    'bookings' => "SELECT COUNT(*) FROM reservation",
    'pending' => "SELECT COUNT(*) FROM reservation WHERE status_id = 1", // Assuming 1 is pending status
];

$counts = [];
foreach ($countQueries as $key => $query) {
    $result = mysqli_query($conn, $query);
    $counts[$key] = mysqli_fetch_array($result)[0];
}

// Get recent bookings
$recentBookingsQuery = "SELECT r.reservation_id, r.date_of_use, r.departure_area, r.destination, 
                    r.departure_time, r.return_time,
                    a.full_name as applicant_name,
                    rs.status_name as reservation_status,
                    v.plate_no, v.type_of_vehicle
                    FROM reservation r
                    JOIN reservation_status rs ON r.status_id = rs.status_id
                    JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                    JOIN applicant a ON r.applicant_id = a.applicant_id
                    ORDER BY r.reservation_id DESC
                    LIMIT 5";

$recentBookingsResult = mysqli_query($conn, $recentBookingsQuery);
$recentBookings = [];

while ($row = mysqli_fetch_assoc($recentBookingsResult)) {
    $recentBookings[] = $row;
}

// Get vehicles and their status
$vehiclesQuery = "SELECT v.vehicle_id, v.plate_no, v.type_of_vehicle, v.capacity, 
                vs.status_name as vehicle_status,
                d.full_name as driver_name
                FROM vehicle v
                LEFT JOIN vehicle_status vs ON v.status_id = vs.status_id
                LEFT JOIN vehicle_assignment va ON v.vehicle_id = va.vehicle_id AND va.end_date IS NULL
                LEFT JOIN driver d ON va.driver_id = d.driver_id
                LIMIT 5";

$vehiclesResult = mysqli_query($conn, $vehiclesQuery);
$vehicles = [];

while ($row = mysqli_fetch_assoc($vehiclesResult)) {
    $vehicles[] = $row;
}

// Render the header
$layout->renderHeader();
?>

<!-- Dashboard Stats -->
<div class="row stats-cards mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fa fa-users fa-3x"></i>
                    </div>
                    <div class="stat-card-content">
                        <h5 class="stat-card-title">Total Users</h5>
                        <h2 class="stat-card-value"><?php echo $counts['users']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="/src/views/usermanagement/listUsers.php" class="text-white">View Details</a>
                <span class="text-white"><i class="fa fa-angle-right"></i></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fa fa-bus fa-3x"></i>
                    </div>
                    <div class="stat-card-content">
                        <h5 class="stat-card-title">Total Vehicles</h5>
                        <h2 class="stat-card-value"><?php echo $counts['vehicles']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="/vehicles.php" class="text-white">View Details</a>
                <span class="text-white"><i class="fa fa-angle-right"></i></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fa fa-calendar fa-3x"></i>
                    </div>
                    <div class="stat-card-content">
                        <h5 class="stat-card-title">Total Bookings</h5>
                        <h2 class="stat-card-value"><?php echo $counts['bookings']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="/bookings.php" class="text-white">View Details</a>
                <span class="text-white"><i class="fa fa-angle-right"></i></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="stat-card-body">
                    <div class="stat-card-icon">
                        <i class="fa fa-clock fa-3x"></i>
                    </div>
                    <div class="stat-card-content">
                        <h5 class="stat-card-title">Pending Requests</h5>
                        <h2 class="stat-card-value"><?php echo $counts['pending']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="/pending-bookings.php" class="text-white">View Details</a>
                <span class="text-white"><i class="fa fa-angle-right"></i></span>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Buttons -->
<div class="row quick-actions mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="btn-group" role="group">
                    <a href="/src/views/usermanagement/createUser.php" class="btn btn-primary"><i class="fa fa-user-plus"></i> Add User</a>
                    <a href="/src/views/vehicles/create.php" class="btn btn-success"><i class="fa fa-plus-circle"></i> Add Vehicle</a>
                    <a href="/src/views/drivers/create.php" class="btn btn-info"><i class="fa fa-id-card"></i> Add Driver</a>
                    <a href="/announcements.php" class="btn btn-warning"><i class="fa fa-bullhorn"></i> Post Announcement</a>
                    <a href="/reports.php" class="btn btn-secondary"><i class="fa fa-chart-bar"></i> Generate Reports</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Booking Requests -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recent Booking Requests</h5>
        <a href="/bookings.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Applicant</th>
                        <th>Vehicle</th>
                        <th>Date</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentBookings)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No booking requests found.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $booking): ?>
                        <tr>
                            <td><?php echo $booking['reservation_id']; ?></td>
                            <td><?php echo $booking['applicant_name']; ?></td>
                            <td><?php echo $booking['type_of_vehicle'] . ' (' . $booking['plate_no'] . ')'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['date_of_use'])); ?></td>
                            <td><?php echo $booking['destination']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo strtolower($booking['reservation_status']) === 'pending' ? 'warning' : (strtolower($booking['reservation_status']) === 'approved' ? 'success' : 'danger'); ?>">
                                    <?php echo $booking['reservation_status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="/view-booking.php?id=<?php echo $booking['reservation_id']; ?>" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                    <?php if ($booking['reservation_status'] == 'Pending'): ?>
                                    <a href="/approve-booking.php?id=<?php echo $booking['reservation_id']; ?>" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                    <a href="/decline-booking.php?id=<?php echo $booking['reservation_id']; ?>" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
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

<!-- Vehicle Fleet Status -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Vehicle Fleet Status</h5>
        <a href="/src/views/vehicles/vehicles.php" class="btn btn-sm btn-primary">Manage Vehicles</a>
    </div>
    <div class="card-body"> 
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Plate No.</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Assigned Driver</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vehicles)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No vehicles found.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><?php echo $vehicle['vehicle_id']; ?></td>
                            <td><?php echo $vehicle['type_of_vehicle']; ?></td>
                            <td><?php echo $vehicle['plate_no']; ?></td>
                            <td><?php echo $vehicle['capacity']; ?> passengers</td>
                            <td>
                                <span class="badge bg-<?php echo strtolower($vehicle['vehicle_status']) === 'available' ? 'success' : (strtolower($vehicle['vehicle_status']) === 'maintenance' ? 'warning' : 'secondary'); ?>">
                                    <?php echo $vehicle['vehicle_status']; ?>
                                </span>
                            </td>
                            <td><?php echo $vehicle['driver_name'] ?? 'Not Assigned'; ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="/src/views/vehicles/edit.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
                                    <a href="/src/views/vehicles/assign.php?vehicle_id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-info"><i class="fa fa-user-plus"></i></a>
                                    <a href="/src/views/vehicles/status.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-warning"><i class="fa fa-cog"></i></a>
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

<!-- Custom Script for Admin Dashboard -->
<script>
    $(document).ready(function() {
        // Initialize any admin-specific JavaScript here
        
        // Example: Refresh data every 30 seconds
        setInterval(function() {
            // This could be replaced with AJAX calls to refresh data
            // without reloading the page
        }, 30000);
    });
</script>

<?php
// Render the footer
$layout->renderFooter();
?>