<th>Contact</th>
                                <td><?= htmlspecialchars($driver['contact_no']) ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><?= htmlspecialchars($driver['status_name']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if ($hasAssignments): ?>
                    <div class="alert alert-danger mt-3">
                        <h4><i class="fas fa-exclamation-circle me-2"></i> Cannot Delete</h4>
                        <p>This driver has the following active vehicle assignments and cannot be deleted:</p>
                        <table class="table table-striped mt-3">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Plate Number</th>
                                    <th>Assigned Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($driverAssignments as $assignment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($assignment['type_of_vehicle']) ?></td>
                                        <td><?= htmlspecialchars($assignment['plate_no']) ?></td>
                                        <td><?= date('M d, Y', strtotime($assignment['assigned_date'])) ?></td>
                                        <td><?= htmlspecialchars($assignment['assignment_status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="mb-0 mt-3">Please end all assignments before deleting this driver.</p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Drivers List
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="alert alert-danger mt-3">
                            <p><strong>Warning:</strong> This action cannot be undone. All driver data will be permanently deleted.</p>
                        </div>
                        
                        <div class="d-flex mt-4">
                            <button type="submit" name="confirm_delete" class="btn btn-danger me-2">
                                <i class="fas fa-trash me-1"></i> Confirm Delete
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Render the footer
$layout->renderFooter();
?>