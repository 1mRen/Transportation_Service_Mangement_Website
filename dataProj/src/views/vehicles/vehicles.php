<?php
// Import the layout class and controller
require_once __DIR__ . '/../../views/layout/layout.php';
require_once __DIR__ . '/../../controllers/VehicleController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: /src/views/auth/signin.php");
    exit();
}

// Create database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Initialize VehicleController
$vehicleController = new \Controllers\VehicleController($conn);

// Handle search parameters
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;

// Get vehicles based on search criteria
$vehicles = $vehicleController->searchVehicles($keyword, $statusFilter);

// Get all vehicle statuses for filter dropdown
$vehicleStatuses = $vehicleController->getAllVehicleStatuses();

// Handle pagination
$itemsPerPage = 10;
$totalItems = count($vehicles);
$totalPages = ceil($totalItems / $itemsPerPage);
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Slice the vehicles array for current page
$currentPageVehicles = array_slice($vehicles, $offset, $itemsPerPage);

// Create a new Layout instance
$layout = new Layout('Vehicle Management', 'vehicles');

// Render the header
$layout->renderHeader();
?>

<div class="container-fluid">

    <!-- Display success/error messages if they exist -->
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'true'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> Vehicle successfully deleted.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Vehicle Management</h1>
        <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
        <a href="/src/views/vehicles/create.php" class="btn btn-primary">
            <i class="fa fa-plus-circle"></i> Add New Vehicle
        </a>
        <?php endif; ?>
    </div>

    <!-- Search and Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search Vehicles</h6>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="keyword" placeholder="Search by plate number or type..." value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">  
                    <option value="" <?php echo $statusFilter === null ? 'selected' : ''; ?>>All Status</option>
                        <?php foreach ($vehicleStatuses as $status): ?>
                        <option value="<?php echo $status['status_id']; ?>" <?php echo $statusFilter === (int)$status['status_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['status_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-search"></i> Search
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="/src/views/vehicles/vehicles.php" class="btn btn-secondary w-100">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Vehicles Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Vehicles List</h6>
            <span class="badge bg-primary"><?php echo $totalItems; ?> vehicles found</span>
        </div>
        <div class="card-body">
            <?php if (empty($currentPageVehicles)): ?>
                <div class="alert alert-info">
                    No vehicles found matching your criteria.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="vehiclesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Plate No.</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currentPageVehicles as $vehicle): ?>
                            <tr>
                                <td><?php echo $vehicle['vehicle_id']; ?></td>
                                <td><?php echo htmlspecialchars($vehicle['type_of_vehicle']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['plate_no']); ?></td>
                                <td><?php echo $vehicle['capacity']; ?> passengers</td>
                                <td>
                                    <span class="badge bg-<?php echo strtolower($vehicle['status_name']) === 'available' ? 'success' : (strtolower($vehicle['status_name']) === 'maintenance' ? 'warning' : 'secondary'); ?>">
                                        <?php echo htmlspecialchars($vehicle['status_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/src/views/vehicles/view.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
                                        <a href="/src/views/vehicles/edit.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="/src/views/vehicles/assign.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-success">
                                            <i class="fa fa-user-plus"></i>
                                        </a>
                                        <a href="/src/views/vehicles/status.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fa fa-cog"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete(<?php echo $vehicle['vehicle_id']; ?>, '<?php echo htmlspecialchars($vehicle['plate_no']); ?>')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Vehicles pagination">
                    <ul class="pagination justify-content-center">
                        <!-- Previous page link -->
                        <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&keyword=<?php echo urlencode($keyword); ?>&status=<?php echo $statusFilter; ?>">
                                Previous
                            </a>
                        </li>
                        
                        <!-- Page numbers -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&keyword=<?php echo urlencode($keyword); ?>&status=<?php echo $statusFilter; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <!-- Next page link -->
                        <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&keyword=<?php echo urlencode($keyword); ?>&status=<?php echo $statusFilter; ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete vehicle <span id="vehicleToDelete"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="/src/views/vehicles/delete.php" method="POST">
                    <input type="hidden" name="vehicle_id" id="vehicleIdToDelete">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Delete Confirmation -->
<script>
    function confirmDelete(id, plateNo) {
        document.getElementById('vehicleIdToDelete').value = id;
        document.getElementById('vehicleToDelete').textContent = plateNo;
        
        // Initialize and show the modal (using Bootstrap's modal)
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>

<?php
// Render the footer
$layout->renderFooter();
?>