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
// Get user's reservations
$reservationsQuery = "SELECT r.reservation_id, r.date_of_use, r.departure_area, r.destination,
                     r.departure_time, r.return_time, r.purpose, r.status_id,
                     v.plate_no, v.type_of_vehicle, rs.status_name as reservation_status
                FROM reservation r
                     LEFT JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                JOIN reservation_status rs ON r.status_id = rs.status_id
                JOIN applicant a ON r.applicant_id = a.applicant_id
                WHERE a.user_id = ?
                ORDER BY r.date_of_use DESC
                LIMIT 5";

$reservationsStmt = mysqli_prepare($conn, $reservationsQuery);
mysqli_stmt_bind_param($reservationsStmt, "i", $userId);
mysqli_stmt_execute($reservationsStmt);
$reservationsResult = mysqli_stmt_get_result($reservationsStmt);
$reservations = [];

while ($row = mysqli_fetch_assoc($reservationsResult)) {
    $reservations[] = $row;
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
        <div class="action-card my-reservations">
            <div class="card-icon">
                <i class="fa fa-calendar-check"></i>
            </div>
            <div class="card-content">
                <h4 class="card-title">My Reservations</h4>
                <p class="card-text">Check the status of your current reservations.</p>
                <a href="/src/views/reservations/my-reservations.php" class="card-btn">View Reservations</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="action-card cancel-reservation">
            <div class="card-icon">
                <i class="fa fa-calendar-times"></i>
            </div>
            <div class="card-content">
                <h4 class="card-title">Cancel Reservation</h4>
                <p class="card-text">Cancel or modify your existing reservations.</p>
                <a href="/src/views/reservations/my-reservations.php" class="card-btn">Cancel</a>
            </div>
        </div>
    </div>
</div>

<!-- My Recent Reservations Section -->
<div class="recent-reservations">
    <div class="section-header">
        <h2 class="section-title">My Recent Reservations</h2>
        <a href="/src/views/reservations/my-reservations.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table reservation-table">
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
                <?php if (empty($reservations)): ?>
                <tr>
                        <td colspan="7" class="text-center">No reservations found.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($reservations as $reservation): ?>
                    <tr>
                            <td><?php echo $reservation['reservation_id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($reservation['date_of_use'])); ?></td>
                            <td><?php echo $reservation['type_of_vehicle'] . ' (' . $reservation['plate_no'] . ')'; ?></td>
                            <td><?php echo $reservation['destination']; ?></td>
                            <td><?php echo date('h:i A', strtotime($reservation['departure_time'])); ?></td>
                        <td>
                                <span class="status <?php echo strtolower($reservation['reservation_status']); ?>">
                                    <?php echo $reservation['reservation_status']; ?>
                            </span>
                        </td>
                        <td>
                                <a href="/src/views/reservations/view.php?id=<?php echo $reservation['reservation_id']; ?>" class="btn btn-sm btn-info">View</a>
                                <?php if ($reservation['reservation_status'] == 'Pending'): ?>
                                    <a href="/src/views/reservations/cancel.php?id=<?php echo $reservation['reservation_id']; ?>" class="btn btn-sm btn-danger">Cancel</a>
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
                            <a href="/src/views/reservations/create.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-primary">Book</a>
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