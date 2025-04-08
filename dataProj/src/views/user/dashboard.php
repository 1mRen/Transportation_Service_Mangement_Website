<?php
// Import the layout class
require_once '../../views/layout/layout.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is not an admin
if (!isset($_SESSION['id']) || strtolower($_SESSION['role']) === 'admin') {
    // If not logged in or is admin, redirect to appropriate page
    if (!isset($_SESSION['id'])) {
        header("Location: /src/views/auth/signin.php");
    } else {
        header("Location: /src/views/admin/dashboard.php");
    }
    exit();
}

// Create a new Layout instance
$layout = new Layout('User Dashboard', 'dashboard');

// Get user data from session
$userId = $_SESSION['id'];
$userName = $_SESSION['name'];

// Database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}
// Get user's bookings (reservations)
$bookingsQuery = "SELECT r.reservation_id, r.date_of_use, r.departure_area, r.destination, 
                r.departure_time, r.return_time, r.purpose, 
                rs.status_name as reservation_status,
                v.plate_no, v.type_of_vehicle
                FROM reservation r
                JOIN reservation_status rs ON r.status_id = rs.status_id
                JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                JOIN applicant a ON r.applicant_id = a.applicant_id
                WHERE a.user_id = ?
                ORDER BY r.date_of_use DESC
                LIMIT 5";

$bookingsStmt = mysqli_prepare($conn, $bookingsQuery);
mysqli_stmt_bind_param($bookingsStmt, "i", $userId);
mysqli_stmt_execute($bookingsStmt);
$bookingsResult = mysqli_stmt_get_result($bookingsStmt);
$bookings = [];

while ($row = mysqli_fetch_assoc($bookingsResult)) {
    $bookings[] = $row;
}

// Get available vehicles
$vehiclesQuery = "SELECT v.vehicle_id, v.plate_no, v.type_of_vehicle, v.capacity, 
                vs.status_name as vehicle_status
                FROM vehicle v
                JOIN vehicle_status vs ON v.status_id = vs.status_id
                WHERE vs.status_name = 'Available'
                LIMIT 5";

$vehiclesStmt = mysqli_prepare($conn, $vehiclesQuery);
mysqli_stmt_execute($vehiclesStmt);
$vehiclesResult = mysqli_stmt_get_result($vehiclesStmt);
$vehicles = [];

while ($row = mysqli_fetch_assoc($vehiclesResult)) {
    $vehicles[] = $row;
}

// Render the header
$layout->renderHeader();
?>

<!-- Action Cards -->
<div class="row action-cards mb-4">
    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="action-card book-vehicle">
            <div class="card-icon">
                <i class="fa fa-bus"></i>
            </div>
            <h4 class="card-title">Book Vehicle</h4>
            <p class="card-text">Reserve a vehicle for your campus transportation needs.</p>
            <a href="/book-vehicle.php" class="card-btn">View Options</a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="action-card my-bookings">
            <div class="card-icon">
                <i class="fa fa-clipboard-list"></i>
            </div>
            <h4 class="card-title">My Bookings</h4>
            <p class="card-text">Check the status of your current bookings.</p>
            <a href="/my-bookings.php" class="card-btn">View Bookings</a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="action-card cancel-booking">
            <div class="card-icon">
                <i class="fa fa-times"></i>
            </div>
            <h4 class="card-title">Cancel Booking</h4>
            <p class="card-text">Need to cancel? Manage your reservations here.</p>
            <a href="/manage-bookings.php" class="card-btn">Cancel</a>
        </div>
    </div>
</div>

<!-- My Recent Bookings Section -->
<div class="table-section mb-4">
    <div class="section-header">
        <h2 class="section-title">My Recent Bookings</h2>
        <div class="section-actions">
            <a href="/my-bookings.php" class="btn btn-sm btn-primary">View All</a>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table booking-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Vehicle</th>
                    <th>Destination</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="7" class="text-center">No bookings found.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['reservation_id']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($booking['date_of_use'])); ?></td>
                        <td><?php echo $booking['type_of_vehicle'] . ' (' . $booking['plate_no'] . ')'; ?></td>
                        <td><?php echo $booking['destination']; ?></td>
                        <td><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></td>
                        <td>
                            <span class="status <?php echo strtolower($booking['reservation_status']); ?>">
                                <?php echo $booking['reservation_status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="/view-booking.php?id=<?php echo $booking['reservation_id']; ?>" class="btn btn-sm btn-info">View</a>
                            <?php if ($booking['reservation_status'] == 'Pending'): ?>
                            <a href="/cancel-booking.php?id=<?php echo $booking['reservation_id']; ?>" class="btn btn-sm btn-danger">Cancel</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Available Vehicles Table Section -->
<div class="table-section">
    <div class="section-header">
        <h2 class="section-title">Available Vehicles</h2>
        <div class="section-actions">
            <input type="text" class="search-input" placeholder="Search vehicles..." id="vehicleSearch">
            <button class="btn refresh-btn" id="refreshVehicles"><i class="fa fa-sync-alt"></i></button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table vehicle-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vehicle Type</th>
                    <th>Plate No.</th> 
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="vehicleTableBody">
                <?php if (empty($vehicles)): ?>
                <tr>
                    <td colspan="6" class="text-center">No available vehicles found.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?php echo $vehicle['vehicle_id']; ?></td>
                        <td><?php echo $vehicle['type_of_vehicle']; ?></td>
                        <td><?php echo $vehicle['plate_no']; ?></td>
                        <td><?php echo $vehicle['capacity']; ?> passengers</td>
                        <td><span class="status available">Available</span></td>
                        <td>
                            <a href="/book-vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-primary">Book</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination-container">
        <ul class="pagination">
            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
        </ul>
    </div>
</div>

<!-- Custom Script for User Dashboard -->
<script>
    $(document).ready(function() {
        // Vehicle search functionality
        $("#vehicleSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#vehicleTableBody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Refresh button functionality - could be replaced with AJAX to refresh data
        $("#refreshVehicles").click(function() {
            location.reload();
        });
    });
</script>

<?php
// Render the footer
$layout->renderFooter();
?>