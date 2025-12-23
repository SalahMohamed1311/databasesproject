<?php
// admin/manage_customers.php
require_once '../includes/functions.php';
requireAdminLogin();

$page_title = "Manage Customers";
$message = '';

$conn = getConnection();

// Handle delete customer
if (isset($_GET['delete'])) {
    $customer_id = intval($_GET['delete']);
    
    // Check if customer has reservations
    $check_reservations = "SELECT COUNT(*) as reservation_count FROM reservations WHERE customer_id = $customer_id";
    $result = mysqli_query($conn, $check_reservations);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['reservation_count'] > 0) {
        $message = '<div class="alert alert-danger">Cannot delete customer with active reservations. Delete reservations first.</div>';
    } else {
        $delete_sql = "DELETE FROM customers WHERE id = $customer_id";
        
        if (mysqli_query($conn, $delete_sql)) {
            $message = '<div class="alert alert-success">Customer deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting customer: ' . mysqli_error($conn) . '</div>';
        }
    }
}

// Handle customer status update
if (isset($_GET['toggle_status'])) {
    $customer_id = intval($_GET['toggle_status']);
    // In real system, you might want to add an 'active' field to customers table
    $message = '<div class="alert alert-info">Status toggle feature would be implemented here</div>';
}

// Search functionality
$search = '';
$where_clause = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
    $where_clause = "WHERE first_name LIKE '%$search%' 
                     OR last_name LIKE '%$search%' 
                     OR email LIKE '%$search%' 
                     OR phone LIKE '%$search%'";
}

// Get all customers
$sql = "SELECT * FROM customers $where_clause ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Get total customers count
$count_sql = "SELECT COUNT(*) as total FROM customers";
$count_result = mysqli_query($conn, $count_sql);
$total_customers = mysqli_fetch_assoc($count_result)['total'];

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Customers</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge badge-primary badge-pill p-2">Total: <?php echo $total_customers; ?> customers</span>
    </div>
</div>

<?php echo $message; ?>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Search Customers</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="form-inline">
            <div class="form-group mr-2 mb-2">
                <input type="text" class="form-control" name="search" placeholder="Search by name, email, phone..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn btn-primary mb-2">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="manage_customers.php" class="btn btn-secondary mb-2 ml-2">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Customers List</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>License</th>
                        <th>Joined</th>
                        <th>Reservations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($customer = mysqli_fetch_assoc($result)): 
                            
                            // Get reservation count for this customer
                            $customer_id = $customer['id'];
                            $res_count_sql = "SELECT COUNT(*) as count FROM reservations WHERE customer_id = $customer_id";
                            $res_count_result = mysqli_query($conn, $res_count_sql);
                            $res_count = mysqli_fetch_assoc($res_count_result)['count'];
                            
                        ?>
                        <tr>
                            <td><?php echo $customer['id']; ?></td>
                            <td>
                                <strong><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></strong>
                            </td>
                            <td><?php echo $customer['email']; ?></td>
                            <td><?php echo $customer['phone'] ?: 'N/A'; ?></td>
                            <td><?php echo $customer['driver_license'] ?: 'N/A'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $res_count; ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info view-customer" 
                                        data-id="<?php echo $customer['id']; ?>"
                                        data-toggle="modal" 
                                        data-target="#customerModal">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <a href="?delete=<?php echo $customer['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this customer?\n\nThis will delete all their reservations as well!')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <?php if (!empty($search)): ?>
                                    No customers found matching your search.
                                <?php else: ?>
                                    No customers found in the system.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Export Options -->
        <div class="mt-3">
            <a href="reports.php?type=customers&export=csv" class="btn btn-outline-success">
                <i class="fas fa-file-csv"></i> Export as CSV
            </a>
            <a href="reports.php?type=customers&export=pdf" class="btn btn-outline-danger ml-2">
                <i class="fas fa-file-pdf"></i> Export as PDF
            </a>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Customer Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="customerDetails">
                Loading customer information...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editCustomerBtn">Edit</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // View customer details
    $('.view-customer').click(function() {
        var customerId = $(this).data('id');
        
        $.ajax({
            url: 'ajax/get_customer_details.php',
            type: 'GET',
            data: { id: customerId },
            success: function(response) {
                $('#customerDetails').html(response);
            },
            error: function() {
                $('#customerDetails').html('<div class="alert alert-danger">Error loading customer details</div>');
            }
        });
    });

    // Edit customer button
    $('#editCustomerBtn').click(function() {
        var customerId = $('#customerDetails').data('customer-id');
        if (customerId) {
            window.location.href = 'edit_customer.php?id=' + customerId;
        }
    });
});
</script>

<?php 
mysqli_close($conn);
require_once '../includes/footer.php'; 
?>