<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Transaksi</title>
    
</head>
<body>

<div class="container">
    <h2 class="text-left mb-4">FORM TRANSAKSI</h2>
    <form action="/transactions/store" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="form-group">
            <label for="transaction_number">Nomor Transaksi</label>
            <input type="text" id="transaction_number" name="transaction_number" value="{{ old('transaction_number', $transactionNumber) }}" readonly>
        </div>

        <div class="form-group">
            <label for="transaction_date">Tanggal Transaksi</label>
            <input type="date" id="transaction_date" name="transaction_date" required>
        </div>

        <div class="form-group">
            <label for="customer_id">Pilih Customer</label>
            <select id="customer_id" name="customer_id" required onchange="toggleCustomerForm()">
                <option value="" disabled selected>Pilih Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
                <option value="new">Tambah Customer</option>
            </select>
        </div>
        
        <div id="new-customer-fields" style="display: none;">
            <h4>Data Customer</h4>
            <div class="form-row">
                <div class="form-group-cust">
                    <label for="new_customer_name">Nama</label>
                    <input type="text" id="new_customer_name" name="new_customer_name">
                </div>
                <div class="form-group-cust">
                    <label for="new_customer_address">Alamat</label>
                    <input type="text" id="new_customer_address" name="new_customer_address">
                </div>
                <div class="form-group-cust">
                    <label for="new_customer_phone">Phone</label>
                    <input type="text" id="new_customer_phone" name="new_customer_phone">
                </div>
            </div>
        </div>      

        <div class="form-group">
            <label for="items">Pilih Barang</label>
            <select id="product_id" name="product_id">
                <!-- Populate with JS -->
            </select>
            <input type="number" id="quantity" name="quantity" placeholder="Qty" min="1" style="width: 100px; display: inline-block; margin-left: 10px;">
            <input type="text" id="subtotal" name="subtotal" placeholder="Subtotal" style="width: 150px; display: inline-block; margin-left: 10px;">
            <button type="button" onclick="addProduct()">Tambah Barang</button>
        </div>

        <table id="items-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="item-list">
                <!-- Populate with JS -->
            </tbody>
        </table>

        <div class="total-container">
            <label for="total">Total Transaksi: </label>
            <span id="total">Rp 0</span>
        </div>

        <div class="action-buttons">
            <button type="submit">Simpan Transaksi</button>
        </div>
    </form>
</div>

<script>
    let products = [];
    let selectedItems = [];

    document.addEventListener('DOMContentLoaded', function() {
        fetchProducts();
    });

    function fetchProducts() {
    fetch('/transaksi/get-products')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Products fetched:', data); // Debugging line to check API response
            if (data && data.length) {
                products = data;
                populateProductSelect();
            } else {
                console.error('No products found or incorrect data format:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching products:', error);
        });
}


function populateProductSelect() {
    const productSelect = document.getElementById('product_id');
    
    // Clear existing options
    productSelect.innerHTML = '';

    // Add a default option
    let defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Pilih Barang';
    defaultOption.disabled = true;
    defaultOption.selected = true;
    productSelect.appendChild(defaultOption);

    // Add options for each product
    products.forEach(product => {
        let option = document.createElement('option');
        option.value = product.id;
        option.textContent = product.name;
        productSelect.appendChild(option);
    });
}


    function addProduct() {
    const productId = document.getElementById('product_id').value;
    const quantity = parseInt(document.getElementById('quantity').value, 10);
    const subtotal = parseFloat(document.getElementById('subtotal').value.replace(/[^0-9.-]+/g, "")); // Remove currency symbols

    const product = products.find(p => p.id == productId);

    if (product && quantity > 0 && !isNaN(subtotal)) {
        const existingItemIndex = selectedItems.findIndex(item => item.id === productId);

        if (existingItemIndex >= 0) {
            // Update existing item
            selectedItems[existingItemIndex] = { ...selectedItems[existingItemIndex], quantity, subtotal };
        } else {
            // Add new item
            selectedItems.push({
                id: product.id,
                name: product.name,
                quantity,
                subtotal
            });
        }

        updateItemList();
        updateTotal();
    } else {
        alert('Please make sure you have selected a valid product and entered correct values for quantity and subtotal.');
    }
}


function updateItemList() {
    const itemList = document.getElementById('item-list');
    itemList.innerHTML = '';
    selectedItems.forEach((item, index) => {
        let row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.name}</td>
            <td>${item.quantity}</td>
            <td>Rp ${item.subtotal.toLocaleString()}</td>
            <td><button type="button" onclick="removeItem(${index})">Hapus</button></td>
        `;
        itemList.appendChild(row);
    });
}


    function updateTotal() {
        const total = selectedItems.reduce((sum, item) => sum + item.subtotal, 0);
        document.getElementById('total').textContent = `Rp ${total.toLocaleString()}`;
    }

    function removeItem(index) {
        selectedItems.splice(index, 1);
        updateItemList();
        updateTotal();
    }
</script>

<script>
    function toggleCustomerForm() {
        const customerSelect = document.getElementById('customer_id');
        const newCustomerFields = document.getElementById('new-customer-fields');

        if (customerSelect.value === 'new') {
            newCustomerFields.style.display = 'block';
        } else {
            newCustomerFields.style.display = 'none';
        }
    }
</script>


</body>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            color: #333;
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
            min-height: 100vh; /* Ensure body takes full viewport height */
            display: flex; /* Enable flexbox layout */
            align-items: center; /* Center container vertically */
            justify-content: center; /* Center container horizontally */
        }

        .container {
            background-color: #fff; /* White background for the container */
            width: 100%; /* Full width of the viewport */
            max-width: 1095px; /* Max width of the container */
            padding: 20px; /* Padding inside the container */
            border-radius: 0; /* No rounded corners */
            box-shadow: none; /* No shadow */
        }

        h2 {
            font-size: 40px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        select {
            color: #333; /* Warna font dropdown */
            background-color: #fff; /* Warna latar belakang dropdown */
            border: 1px solid #ddd; /* Warna border dropdown */
        }

        input[readonly] {
            background-color: #f7f7f7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f7f7f7;
        }

        button {
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background-color: #45a049;
        }

        #subtotal,
        #total {
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
        display: flex;
        gap: 30px; /* Space between the input fields */
        }

        .form-group-cust {
            flex: 1; /* Make each form group take equal space */
        }

        .form-group input {
            width: 100%; /* Ensure inputs take full width of the form group */
        }

        .total-container {
            margin-top: 20px;
            font-size: 16px;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .action-buttons button {
            margin-left: 10px;
        }
        
    </style>
</html>
