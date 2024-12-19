<?php
// Database connection
$servername = "localhost";  // Change this to your database server
$username = "root";         // Change this to your database username
$password = "";             // Change this to your database password
$dbname = "project_system";   // Change this to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get all inventory items
function getInventory($conn) {
    $query = "SELECT * FROM inventory";
    return $conn->query($query);
}

// Function to get all sales records
function getSalesRecords($conn) {
    $query = "SELECT * FROM sales_records";
    return $conn->query($query);
}

// Function to delete a product
function deleteProduct($conn, $id) {
    $query = "DELETE FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Function to delete a sales record
function deleteSalesRecord($conn, $id) {
    $query = "DELETE FROM sales_records WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Handle delete request for inventory
if (isset($_GET['delete_inventory'])) {
    $id = intval($_GET['delete_inventory']);
    if (deleteProduct($conn, $id)) {
        header("Location: reports.php");
        exit;
    } else {
        echo "Error deleting product.";
    }
}

// Handle delete request for sales record
if (isset($_GET['delete_sales'])) {
    $id = intval($_GET['delete_sales']);
    if (deleteSalesRecord($conn, $id)) {
        header("Location: reports.php");
        exit;
    } else {
        echo "Error deleting sales record.";
    }
}

// Fetch all inventory items
$inventory = getInventory($conn);

// Fetch all sales records
$salesRecords = getSalesRecords($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="sidebar">
        <h2>POS</h2>
        <button id="dark-mode-toggle">Toggle Dark Mode</button>

        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="sales.php">Sales</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="../JAY_2024/MAIN/login.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1>Reports</h1>
        
        <!-- Stock Overview Section -->
        <div class="report-section" id="stock-section">
            <h2>Stock Overview</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $inventory->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['stock']) ?></td>
                            <td><?= htmlspecialchars($row['price']) ?></td>
                            <td>
                                <a href="reports.php?delete_inventory=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <!-- Print Button for Stock Overview -->
            <button onclick="printSection('stock-section')">Print Stock Overview</button>
        </div>

        <!-- Sales Records Section -->
        <div class="report-section" id="sales-section">
            <h2>Sales Records</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity Sold</th>
                        <th>Total</th>
                        <th>Sale Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $salesRecords->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars($row['price']) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td><?= htmlspecialchars($row['total']) ?></td>
                            <td><?= htmlspecialchars($row['date_added']) ?></td>
                            <td>
                                <a href="reports.php?delete_sales=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this sales record?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <!-- Print Button for Sales Records -->
            <button onclick="printSection('sales-section')">Print Sales Records</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../script.js"></script>

    <script>
        // Function to print a specific section of the page
        function printSection(sectionId) {
            var printContents = document.getElementById(sectionId).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
    <script>
    const toggleSwitch = document.getElementById('dark-mode-toggle');
    toggleSwitch.addEventListener('click', function() {
        document.body.classList.toggle('dark');
    });
</script>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
