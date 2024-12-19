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
    $query = "SELECT * FROM inventory ORDER BY created_at DESC";
    return $conn->query($query);
}

// Function to add a new product
function addProduct($conn, $productName, $category, $stock, $price) {
    $query = "INSERT INTO inventory (product_name, category, stock, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdi", $productName, $category, $stock, $price);
    return $stmt->execute();
}

// Function to update an existing product
function updateProduct($conn, $id, $productName, $category, $stock, $price) {
    $query = "UPDATE inventory SET product_name = ?, category = ?, stock = ?, price = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssidi", $productName, $category, $stock, $price, $id);
    return $stmt->execute();
}

// Function to delete a product
function deleteProduct($conn, $id) {
    $query = "DELETE FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if (deleteProduct($conn, $id)) {
        header("Location: inventory.php");
        exit;
    } else {
        echo "Error deleting product.";
    }
}

// Handle form submission for adding or updating a product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $productName = $_POST['product_name'];
    $category = $_POST['category'];
    $stock = intval($_POST['stock']);
    $price = floatval($_POST['price']);

    if ($id) {
        // Update the product
        if (updateProduct($conn, $id, $productName, $category, $stock, $price)) {
            header("Location: inventory.php");
            exit;
        } else {
            echo "Error updating product.";
        }
    } else {
        // Add new product
        if (addProduct($conn, $productName, $category, $stock, $price)) {
            header("Location: inventory.php");
            exit;
        } else {
            echo "Error adding product.";
        }
    }
}

// Fetch all inventory items
$inventory = getInventory($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="sidebar">
        <h2>pos</h2>
        <button id="dark-mode-toggle">Toggle Dark Mode</button>

        <ul>
        <li><a href="index.php">Dashboard</a></li>
             <li><a href="sales.php">sales</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="../JAY_2024/MAIN/login.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1>Inventory Management</h1>
        <button id="addProductBtn" onclick="showAddForm()">Add Product</button>

        <!-- Add/Edit Form -->
        <div id="dynamicFormContainer" style="display: none;">
            <form method="POST" action="inventory.php">
                <input type="hidden" name="id" id="productId">
                <label for="product_name">Product Name:</label>
                <input type="text" name="product_name" id="productName" required>
                <label for="category">Category:</label>
                <input type="text" name="category" id="category" required>
                <label for="stock">Stock:</label>
                <input type="number" name="stock" id="stock" required>
                <label for="price">Price:</label>
                <input type="number" step="0.01" name="price" id="price" required>
                <button type="submit">Save</button>
                <button type="button" onclick="hideForm()">Cancel</button>
            </form>
        </div>

        <!-- Inventory Table -->
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Price</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="inventoryTable">
                <?php while ($row = $inventory->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= htmlspecialchars($row['stock']) ?></td>
                        <td><?= htmlspecialchars($row['price']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td>
                            <button onclick="editProduct(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                            <a href="inventory.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script>
        function showAddForm() {
            document.getElementById('dynamicFormContainer').style.display = 'block';
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('category').value = '';
            document.getElementById('stock').value = '';
            document.getElementById('price').value = '';
        }

        function hideForm() {
            document.getElementById('dynamicFormContainer').style.display = 'none';
        }

        function editProduct(product) {
            showAddForm();
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.product_name;
            document.getElementById('category').value = product.category;
            document.getElementById('stock').value = product.stock;
            document.getElementById('price').value = product.price;
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
