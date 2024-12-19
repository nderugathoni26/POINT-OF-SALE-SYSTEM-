document.addEventListener('DOMContentLoaded', () => {
    const addProductBtn = document.getElementById('addProductBtn');
    const inventoryTable = document.getElementById('inventoryTable');

    if (addProductBtn) {
        addProductBtn.addEventListener('click', () => {
            // Create a new row for the input fields
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="text" placeholder="Enter product name" id="newProductName"></td>
                <td><input type="text" placeholder="Enter category" id="newProductCategory"></td>
                <td><input type="number" placeholder="Enter stock" id="newProductStock"></td>
                <td><input type="number" placeholder="Enter price" id="newProductPrice"></td>
                <td>
                    <button id="saveProduct" class="save">Save</button>
                    <button id="cancelProduct" class="cancel">Cancel</button>
                </td>
            `;
            inventoryTable.appendChild(newRow);

            // Save product logic
            document.getElementById('saveProduct').addEventListener('click', () => {
                const productName = document.getElementById('newProductName').value;
                const productCategory = document.getElementById('newProductCategory').value;
                const productStock = document.getElementById('newProductStock').value;
                const productPrice = document.getElementById('newProductPrice').value;

                // Validate input fields
                if (productName && productCategory && productStock && productPrice) {
                    // Add new product row to the table
                    const savedRow = document.createElement('tr');
                    savedRow.innerHTML = `
                        <td>${productName}</td>
                        <td>${productCategory}</td>
                        <td>${productStock}</td>
                        <td>$${productPrice}</td>
                        <td>
                            <button class="edit">Edit</button>
                            <button class="delete">Delete</button>
                        </td>
                    `;
                    inventoryTable.replaceChild(savedRow, newRow); // Replace input row with saved row
                } else {
                    alert('Please fill in all fields.');
                }
            });

            // Cancel adding product logic
            document.getElementById('cancelProduct').addEventListener('click', () => {
                newRow.remove();
            });
        });
    }

    if (inventoryTable) {
        inventoryTable.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete')) {
                e.target.closest('tr').remove();
            } else if (e.target.classList.contains('edit')) {
                alert('Edit functionality not implemented yet.');
            }
        });
    }
});



    // Function to toggle dark mode
    const toggleButton = document.getElementById('darkModeToggle');
    const body = document.body;
    
    // Check if dark mode is already set in localStorage
    if(localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('darkmode');
    }

    toggleButton.addEventListener('click', () => {
        // Toggle darkmode class on body
        body.classList.toggle('darkmode');
        
        // Save the user's preference in localStorage
        if (body.classList.contains('darkmode')) {
            localStorage.setItem('darkMode', 'enabled');
        } else {
            localStorage.setItem('darkMode', 'disabled');
        }
    });
