<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_h', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_customer')->constrained('ms_customer');
            $table->string('nomor_transaksi');
            $table->date('tanggal_transaksi');
            $table->decimal('total_transaksi', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_h');
    }
};
