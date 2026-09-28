<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolConfirmation extends Model
{
    protected $fillable = ['delivery_id', 'school_id', 'expected_qty', 'received_qty', 'rejected_qty', 'attendance', 'complaint', 'feedback', 'status', 'confirmed_by'];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
