<?php

namespace App\Http\Controllers;

use App\Models\TransaksiH;
use App\Models\TransaksiD;
use App\Models\MsCustomer;
use App\Models\Counter;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $transaksi = TransaksiH::with('customer');

            return DataTables::of($transaksi)
                ->addIndexColumn()
                ->addColumn('customer', function ($row) {
                    return $row->customer->nama;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="#" class="edit btn btn-sm btn-warning">Edit</a>';
                    $btn .= ' <a href="#" class="delete btn btn-sm btn-danger">Hapus</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('transaksi.index');
    }

    public function create()
    {
        $customers = MsCustomer::all();

        // Generate a transaction number for display without incrementing the counter
        $transactionNumber = $this->generateTransactionNumber();

        return view('transaksi.create', compact('customers', 'transactionNumber'));
    }

    public function store(Request $request)
{
    // Validation
    $request->validate([
        'customer_id' => 'required_without:new_customer_name',
        'new_customer_name' => 'required_if:customer_id,new',
        'new_customer_address' => 'required_if:customer_id,new',
        'new_customer_phone' => 'required_if:customer_id,new',
        'transaction_date' => 'required|date',
        'items' => 'required|array|min:1',
        'items.*.id' => 'required|integer',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    // If a new customer is being added
    if ($request->customer_id === 'new') {
        $customer = MsCustomer::create([
            'name' => $request->new_customer_name,
            'address' => $request->new_customer_address,
            'phone' => $request->new_customer_phone,
        ]);
        $customerId = $customer->id;
    } else {
        $customerId = $request->customer_id;
    }

    // Generate the transaction number (with counter increment)
    $transactionNumber = $this->generateTransactionNumber(true);

    // Create the transaction header
    $transaction = new TransaksiH();
    $transaction->nomor_transaksi = $transactionNumber;
    $transaction->id_customer = $customerId;
    $transaction->tanggal_transaksi = $request->transaction_date;
    $transaction->total_transaksi = array_sum(array_column($request->items, 'subtotal')); // Calculate total from subtotals
    $transaction->save();

    // Store transaction details
    foreach ($request->items as $item) {
        TransaksiD::create([
            'id_transaksi_h' => $transaction->id,
            'kd_barang' => $item['id'],
            'nama_barang' => $item['name'], // Ensure 'name' is included in the request
            'qty' => $item['quantity'],
            'subtotal' => $item['subtotal'], // Save user-input subtotal
        ]);
    }
    $this->incrementCounter();

    return redirect()->route('transaksi.index')->with('success', 'Transaction saved successfully.');
}


    protected function generateTransactionNumber($incrementCounter = false)
    {
        // Fetch the current year and month
        $year = date('Y');
        $month = date('m');

        // Fetch or create the counter for the current month and year
        $counter = Counter::firstOrCreate(
            ['bulan' => $month, 'tahun' => $year],
            ['counter' => 0]
        );

        // Generate the transaction number
        $transactionNumber = sprintf('S0/%s-%s/%03d', $year, $month, $counter->counter);

        // Increment the counter if required
        if ($incrementCounter) {
            $counter->increment('counter');
        }

        return $transactionNumber;
    }

    protected function incrementCounter()
    {
        $year = date('Y');
        $month = date('m');

        // Fetch the counter for the current month and year
        $counter = Counter::where('bulan', $month)->where('tahun', $year)->first();

        if ($counter) {
            $counter->increment('counter');
        }
    }

    public function getProducts()
    {
        $response = Http::withHeaders([
            'Client-Service' => 'gmedia-recruitment',
            'Auth-Key' => 'demo-admin',
            'User-Id' => '1',
            'Token' => '8godoajVqNNOFz21npycK6iofUgFXl1kluEJt/WYFts9C8IZqUOf7rOXCe0m4f9B',
        ])->post('http://gmedia.bz/DemoCase/main/list_barang', [
            'start' => 0,
            'count' => 10, // Example value
        ]);

        return $response->json()['response'];
    }

    public function getProductDetails($id)
    {
        $response = Http::withHeaders([
            'Client-Service' => 'gmedia-recruitment',
            'Auth-Key' => 'demo-admin',
            'User-Id' => '1',
            'Token' => '8godoajVqNNOFz21npycK6iofUgFXl1kluEJt/WYFts9C8IZqUOf7rOXCe0m4f9B',
        ])->post('http://gmedia.bz/DemoCase/main/get_barang', [
            'kd_barang' => $id,
        ]);

        return $response->json();
    }
}
