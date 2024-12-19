<?php
// Database connection
$servername = "localhost";  // Your database server
$username = "root";         // Your database username
$password = "";             // Your database password
$dbname = "project_system";   // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get total stock
function getTotalStock($conn) {
    $query = "SELECT SUM(stock) AS total_stock FROM inventory";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_stock'];
    }
    return 0;
}

// Function to get total categories
function getTotalCategories($conn) {
    $query = "SELECT COUNT(DISTINCT category) AS total_categories FROM inventory";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_categories'];
    }
    return 0;
}

// Function to get total products
function getTotalProducts($conn) {
    $query = "SELECT COUNT(*) AS total_products FROM inventory";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_products'];
    }
    return 0;
}

// Function to get total sales
function getTotalSales($conn) {
    $query = "SELECT COUNT(*) AS total_sales FROM sales_records";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_sales'];
    }
    return 0;
}

// Function to get total revenue
function getTotalRevenue($conn) {
    $query = "SELECT SUM(total) AS total_revenue FROM sales_records";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_revenue'];
    }
    return 0;
}

// Function to get most sold product
function getMostSoldProduct($conn) {
    $query = "SELECT product_name, SUM(quantity) AS total_quantity 
              FROM sales_records 
              GROUP BY product_name 
              ORDER BY total_quantity DESC LIMIT 1";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['product_name'];
    }
    return 'N/A';
}

// Fetch data
$totalStock = getTotalStock($conn);
$totalCategories = getTotalCategories($conn);
$totalProducts = getTotalProducts($conn);
$totalSales = getTotalSales($conn);
$totalRevenue = getTotalRevenue($conn);
$mostSoldProduct = getMostSoldProduct($conn);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 cards per row */
            gap: 20px; /* Space between cards */
            width: 100%;
            max-width: 1200px; /* Optional max-width */
        }
        .card {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
        }
        h2 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        p {
            font-size: 1.2em;
        }
        .cards-container {
            margin-bottom: 40px; /* Add spacing between the top and bottom row */
        }
        @media (max-width: 768px) {
            .cards {
                grid-template-columns: repeat(2, 1fr); /* 2 cards per row on smaller screens */
            }
        }
        @media (max-width: 480px) {
            .cards {
                grid-template-columns: 1fr; /* 1 card per row on extra small screens */
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>pos</h2>
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
        <h1>Dashboard</h1>
        
        <!-- Top Row Cards -->
        <div class="cards-container">
            <div class="cards">
                <div class="card">
                    <h2>Total Stock</h2>
                    <p><?php echo $totalStock; ?> Units</p>
                </div>
                <div class="card">
                    <h2>Categories</h2>
                    <p><?php echo $totalCategories; ?> Categories</p>
                </div>
                <div class="card">
                    <h2>Total Products</h2>
                    <p><?php echo $totalProducts; ?> Products</p>
                </div>
            </div>
        </div>

        <!-- Bottom Row Cards -->
        <div class="cards-container">
            <div class="cards">
                <div class="card">
                    <h2>Total Sales</h2>
                    <p><?php echo $totalSales; ?> Sales</p>
                </div>
                <div class="card">
                    <h2>Total Revenue</h2>
                    <p>$<?php echo number_format($totalRevenue, 2); ?> Revenue</p>
                </div>
                <div class="card">
                    <h2>Most Sold Product</h2>
                    <p><?php echo $mostSoldProduct; ?></p>
                </div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
    <script>
    const toggleSwitch = document.getElementById('dark-mode-toggle');
    toggleSwitch.addEventListener('click', function() {
        document.body.classList.toggle('dark');
    });
</script>

</body>
</html>
