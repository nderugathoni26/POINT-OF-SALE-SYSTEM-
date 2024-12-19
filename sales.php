<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from the inventory
function getProducts($conn) {
    $query = "SELECT id, product_name, price, stock FROM inventory";
    return $conn->query($query);
}

// Initialize session for the cart
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add product to cart
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Fetch product details
    $query = "SELECT id, product_name, price, stock FROM inventory WHERE id = $productId";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Check stock availability
        if ($product['stock'] >= $quantity) {
            $item = [
                'id' => $product['id'],
                'name' => $product['product_name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'total' => $product['price'] * $quantity // Ensure total is set
            ];
            $_SESSION['cart'][] = $item;
        } else {
            $message = "Insufficient stock for " . $product['product_name'] . ".";
        }
    }
}

// Remove product from cart
if (isset($_POST['remove_from_cart'])) {
    $cartIndex = $_POST['cart_index'];
    unset($_SESSION['cart'][$cartIndex]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
}

// Handle checkout and generate receipt
$receiptGenerated = false;
$receipt = [];
if (isset($_POST['checkout'])) {
    $receipt = $_SESSION['cart'];
    $receiptTotal = 0;

    // Update stock in the database and calculate the total cost
    foreach ($receipt as $item) {
        $itemTotal = isset($item['total']) ? $item['total'] : 0;
        $receiptTotal += $itemTotal;

        // Reduce stock in the database
        $updateStockQuery = "UPDATE inventory SET stock = stock - {$item['quantity']} WHERE id = {$item['id']}";
        $conn->query($updateStockQuery);
    }

    // Clear the cart 
    $_SESSION['cart'] = [];
    $receiptGenerated = true;
}

// Add cart items to the records table when "Add to Records" is clicked
if (isset($_POST['add_to_records'])) {
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $productId = $item['id'];
            $productName = $item['name'];
            $price = $item['price'];
            $quantity = $item['quantity'];
            $total = $item['total'];
            $dateAdded = date('Y-m-d H:i:s');

            // Insert each item into the sales_records table
            $query = "INSERT INTO sales_records (product_id, product_name, price, quantity, total, date_added)
                      VALUES ('$productId', '$productName', '$price', '$quantity', '$total', '$dateAdded')";
            if ($conn->query($query) === TRUE) {
                $recordSuccessMessage = "Items successfully added to records!";
            } else {
                $recordErrorMessage = "Error adding items to records: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .products, .cart { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        .cart-total { font-weight: bold; }
        .receipt { margin-top: 20px; border: 1px solid #ddd; padding: 20px; position: relative; }
        .receipt h2 { margin-bottom: 20px; }
        .receipt-buttons { margin-top: 20px; display: flex; justify-content: space-between; }
    </style>
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
        <h1>Sales</h1>

        <!-- Products Section -->
        <div class="products">
            <h2>Available Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $products = getProducts($conn);
                    if ($products->num_rows > 0) {
                        while ($product = $products->fetch_assoc()) {
                            echo "<tr>
                                <td>{$product['product_name']}</td>
                                <td>\${$product['price']}</td>
                                <td>{$product['stock']}</td>
                                <td>
                                    <form method='POST' style='display: flex; align-items: center;'>
                                        <input type='hidden' name='product_id' value='{$product['id']}'>
                                        <input type='number' name='quantity' min='1' max='{$product['stock']}' required>
                                        <button type='submit' name='add_to_cart'>Add to Cart</button>
                                    </form>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No products available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Cart Section -->
        <div class="cart">
            <h2>Cart</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($_SESSION['cart'])) {
                        $cartTotal = 0;
                        foreach ($_SESSION['cart'] as $index => $item) {
                            $itemTotal = isset($item['total']) ? $item['total'] : 0;
                            echo "<tr>
                                <td>{$item['name']}</td>
                                <td>\${$item['price']}</td>
                                <td>{$item['quantity']}</td>
                                <td>\${$itemTotal}</td>
                                <td>
                                    <form method='POST'>
                                        <input type='hidden' name='cart_index' value='$index'>
                                        <button type='submit' name='remove_from_cart'>Remove</button>
                                    </form>
                                </td>
                            </tr>";
                            $cartTotal += $itemTotal;
                        }
                        echo "<tr>
                            <td colspan='3' class='cart-total'>Grand Total:</td>
                            <td colspan='2' class='cart-total'>\$$cartTotal</td>
                        </tr>";
                        echo "<tr>
                            <td colspan='5'>
                                <form method='POST'>
                                    <button type='submit' name='checkout'>Checkout</button>
                                </form>
                            </td>
                        </tr>";
                    } else {
                        echo "<tr><td colspan='5'>Your cart is empty</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Add to Records Button -->
            <?php if (!empty($_SESSION['cart'])): ?>
                <form method="POST">
                    <button type="submit" name="add_to_records">Add to Records</button>
                </form>
                <?php
                    if (isset($recordSuccessMessage)) {
                        echo "<p style='color: green;'>$recordSuccessMessage</p>";
                    }
                    if (isset($recordErrorMessage)) {
                        echo "<p style='color: red;'>$recordErrorMessage</p>";
                    }
                ?>
            <?php endif; ?>
        </div>

        <!-- Receipt Section -->
        <?php if ($receiptGenerated): ?>
            <div class="receipt">
                <h2>Receipt</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($receipt as $item) {
                            $itemTotal = isset($item['total']) ? $item['total'] : 0;
                            echo "<tr>
                                <td>{$item['name']}</td>
                                <td>\${$item['price']}</td>
                                <td>{$item['quantity']}</td>
                                <td>\${$itemTotal}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div class="receipt-buttons">
                    <button onclick="window.print()">Print Receipt</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
