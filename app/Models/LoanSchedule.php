<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['loan_id', 'week_number', 'schedule_date', 'amount', 'status'];

    /**
     * Get the loan belong to the schedule
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
