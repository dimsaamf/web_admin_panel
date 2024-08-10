<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsCustomer extends Model
{
    use HasFactory;

    protected $table = 'ms_customer';

    protected $fillable = ['nama', 'alamat', 'phone'];

    public function transaksiH()
    {
        return $this->hasMany(TransaksiH::class, 'id_customer');
    }
}
