<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penjualan</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h2 class="text-left mt-4 mb-4">TRANSAKSI PENJUALAN</h2>

        <!-- Filter by date range -->
        <div class="mb-4">
            <div>
                <label for="start_date" style="font-weight: bold;">Filter Tanggal Transaksi</label>
            </div>
            <div class="row align-items-center">
                <!-- Date Filter Form -->
                
                <form id="filter-form" class="col-md-8">
                    <div class="form-row align-items-center">
                        
                        <div class="col-auto">
                            <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                        </div>
                        <div class="col-auto">
                            <span>s/d</span>
                        </div>
                        <div class="col-auto">
                            <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                        </div>
                        <div class="col-auto">
                            <button type="button" id="filter" class="btn btn-primary">
                                <i class="fa fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <!-- Add Transaksi Button -->
                <div class="col-md-4 text-right">
                    <a href="{{ route('transaksi.create') }}" class="btn btn-success">
                        <i class="fa fa-plus-circle"></i> Tambah Transaksi
                    </a>
                    <a href="#" class="btn btn-info">
                        <i class="fa fa-file-excel"></i> Export Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- DataTables -->
        <table id="transaksiTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Transaksi</th>
                    <th>Customer</th>
                    <th>Total Transaksi</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>

    </div>

    <!-- Include jQuery, DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize DataTables
        var table = $('#transaksiTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('transaksi.index') }}',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nomor_transaksi', name: 'nomor_transaksi' },
                { data: 'customer', name: 'customer' },
                { data: 'total_transaksi', name: 'total_transaksi' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Filter button event listener
        $('#filter').click(function() {
            table.draw();
        });
    });
    </script>
</body>
</html>
