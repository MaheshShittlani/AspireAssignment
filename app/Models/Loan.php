<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = ['amount','loan_terms','user_id','status','roi','loan_amount'];
    
    /**
     * GEt the schedules of loan
     */
    public function schedules()
    {
        return $this->hasMany(LoanSchedule::class);
    }

    /**
     * Get the user belongs to the loan
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
