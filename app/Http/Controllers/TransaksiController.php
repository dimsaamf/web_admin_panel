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
                    return $row->customer->name;
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
        $transactionNumber = $this->generateTransactionNumber();

        $response = Http::withHeaders([
            'Client-Service' => 'gmedia-recruitment',
            'Auth-Key' => 'demo-admin',
            'User-Id' => '1',
            'Token' => '8godoajVqNNOFz21npycK6iofUgFXl1kluEJt/WYFts9C8IZqUOf7rOXCe0m4f9B',
        ])->post('http://gmedia.bz/DemoCase/main/list_barang', [
            'start' => 0,
            'count' => 10,
        ]);

        $data = $response->json()['response'];

        return view('transaksi.create', compact('customers', 'transactionNumber', 'data'));
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'customer_id' => 'required_without:new_customer_name|exists:ms_customer,id',
            'new_customer_name' => 'required_if:customer_id,new',
            'new_customer_address' => 'required_if:customer_id,new',
            'new_customer_phone' => 'required_if:customer_id,new',
            'items' => 'required|array',
            'items.*.kd_barang' => 'required|exists:products,kd_barang',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.subtotal' => 'required|numeric|min:0'
        ]);

        // Handle new customer if selected
        $customerId = $request->input('customer_id');
        if ($customerId === 'new') {
            $customer = new MsCustomer();
            $customer->nama = $request->input('new_customer_name');
            $customer->alamat = $request->input('new_customer_address');
            $customer->phone = $request->input('new_customer_phone');
            $customer->save();
            $customerId = $customer->id;
        }

        // Generate the transaction number (with counter increment)
        $transactionNumber = $this->generateTransactionNumber(true);

        // Create transaction header
        $transaksiH = new TransaksiH();
        $transaksiH->nomor_transaksi = $transactionNumber;
        $transaksiH->id_customer = $customerId;
        $transaksiH->tanggal_transaksi = $request->input('transaction_date');
        $transaksiH->total_transaksi = array_sum(array_column($request->input('items'), 'subtotal')); // Calculate total from subtotals
        $transaksiH->save();

        // Add transaction details
        foreach ($request->input('items') as $item) {
            $transaksiD = new TransaksiD();
            $transaksiD->id_transaksi_h = $transaksiH->id;
            $transaksiD->kd_barang = $item['kd_barang'];
            $transaksiD->qty = $item['qty'];
            $transaksiD->subtotal = $item['subtotal'];
            $transaksiD->save();
        }

        // Increment the counter
        $this->incrementCounter();

        // Redirect or respond
        return redirect()->route('transaksi.index')->with('success', 'Transaction successfully created!');
    }

    protected function generateTransactionNumber($incrementCounter = false)
    {
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

    public function getProductDetails(Request $request)
    {
        $kd_barang = $request->query('kd_barang');

        $response = Http::withHeaders([
            'Client-Service' => 'gmedia-recruitment',
            'Auth-Key' => 'demo-admin',
            'User-Id' => '1',
            'Token' => '8godoajVqNNOFz21npycK6iofUgFXl1kluEJt/WYFts9C8IZqUOf7rOXCe0m4f9B',
        ])->post('http://gmedia.bz/DemoCase/main/get_barang', [
            'kd_barang' => $kd_barang,
        ]);

        return response()->json($response->json());
    }
}