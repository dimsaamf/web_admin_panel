<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>

<div class="container mx-auto p-4">
    <h2 class="text-2xl mb-4">Form Transaksi</h2>
    <form action="{{ route('transaksi.store') }}" method="POST" id="transaction-form">
        @csrf

        <div class="mb-4">
            <label for="transaction_number" class="block text-sm font-medium text-gray-700">Nomor Transaksi</label>
            <input type="text" id="transaction_number" name="transaction_number" value="{{ old('transaction_number', $transactionNumber) }}" readonly class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="mb-4">
            <label for="transaction_date" class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
            <input type="date" id="transaction_date" name="transaction_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="mb-4">
            <label for="customer_id" class="block text-sm font-medium text-gray-700">Pilih Customer</label>
            <select id="customer_id" name="customer_id" required onchange="toggleCustomerForm()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="" disabled selected>Pilih Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
                <option value="new">Tambah Customer</option>
            </select>
        </div>
        
        <div id="new-customer-fields" class="mb-4 hidden">
            <h4 class="text-lg font-semibold">Data Customer</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="form-group">
                    <label for="new_customer_name" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" id="new_customer_name" name="new_customer_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="form-group">
                    <label for="new_customer_address" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <input type="text" id="new_customer_address" name="new_customer_address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="form-group">
                    <label for="new_customer_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" id="new_customer_phone" name="new_customer_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label for="barang-dropdown" class="block text-sm font-medium text-gray-700">Pilih Barang</label>
            <select id="barang-dropdown" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Pilih Barang</option>
                @foreach($data as $barang)
                    <option value="{{ $barang['kd_barang'] }}">{{ $barang['nama_barang'] }}</option>
                @endforeach
            </select>
            <input type="number" id="quantity" name="quantity" placeholder="Qty" min="1" class="mt-2 block w-full border-gray-300 rounded-md shadow-sm">
            <input type="text" id="subtotal" name="subtotal" placeholder="Subtotal" class="mt-2 block w-full border-gray-300 rounded-md shadow-sm">
            <button type="button" onclick="addProduct()" class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md">Tambah Barang</button>
        </div>

        <table id="items-table" class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">No</th>
                    <th class="px-4 py-2 border-b">Nama Barang</th>
                    <th class="px-4 py-2 border-b">Qty</th>
                    <th class="px-4 py-2 border-b">Subtotal</th>
                    <th class="px-4 py-2 border-b">Action</th>
                </tr>
            </thead>
            <tbody id="item-list">
                <!-- Populate with JS -->
            </tbody>
        </table>

        <div class="mt-4">
            <label for="total" class="block text-sm font-medium text-gray-700">Total Transaksi: </label>
            <span id="total" class="text-xl font-bold">Rp 0</span>
        </div>

        <div class="mt-4">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">Simpan Transaksi</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let selectedItems = [];

    function toggleCustomerForm() {
        const customerSelect = document.getElementById('customer_id');
        const newCustomerFields = document.getElementById('new-customer-fields');
        newCustomerFields.style.display = customerSelect.value === 'new' ? 'block' : 'none';
    }

    $('#barang-dropdown').on('change', function() {
        var kd_barang = $(this).val();
        if (kd_barang) {
            $.ajax({
                url: '{{ route("transaksi.getProductDetails") }}',
                type: 'GET',
                data: { kd_barang: kd_barang },
                success: function(response) {
                    $('#barang-detail').html(`
                        <p>Nama Barang: ${response.response.nama_barang}</p>
                        <p>Kode Barang: ${response.response.kd_barang}</p>
                        <p>Harga: ${response.response.harga}</p>
                    `);
                },
                error: function() {
                    $('#barang-detail').html('<p>Data tidak ditemukan.</p>');
                }
            });
        } else {
            $('#barang-detail').html('');
        }
    });

    function addProduct() {
        const productCode = $('#barang-dropdown').val();
        const quantity = parseInt($('#quantity').val(), 10);
        const subtotal = parseFloat($('#subtotal').val().replace(/[^0-9.-]+/g, ""));

        if (productCode && quantity > 0 && !isNaN(subtotal)) {
            const product = {
                id: productCode,
                name: $('#barang-dropdown option:selected').text(),
                quantity,
                subtotal
            };

            selectedItems.push(product);
            updateItemList();
            updateTotal();
        } else {
            alert('Please make sure you have selected a valid product and entered correct values for quantity and subtotal.');
        }
    }

    function updateItemList() {
        const itemList = $('#item-list');
        itemList.empty();
        selectedItems.forEach((item, index) => {
            const row = `<tr>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>Rp ${item.subtotal.toLocaleString()}</td>
                <td><button type="button" onclick="removeItem(${index})" class="px-4 py-1 bg-red-500 text-white rounded-md">Hapus</button></td>
            </tr>`;
            itemList.append(row);
        });
    }

    function updateTotal() {
        const total = selectedItems.reduce((sum, item) => sum + item.subtotal, 0);
        $('#total').text(`Rp ${total.toLocaleString()}`);
    }

    function removeItem(index) {
        selectedItems.splice(index, 1);
        updateItemList();
        updateTotal();
    }
</script>
</body>
</html>