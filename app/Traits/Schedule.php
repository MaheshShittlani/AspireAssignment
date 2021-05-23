<?php
namespace App\Traits;

use Carbon\Carbon;
use App\Models\LoanSchedule;

trait Schedule {

    public function generateSchedule($loan)
    {
        //Convert loan terms into weeks
        $loan_weeks = $loan->loan_terms * 52;
        $weekly_amount = ceil($loan->loan_amount / $loan_weeks);
        
        $schedules = [];
        $loan_approved_date = Carbon::parse($loan->approved_at);
        for($i = 1; $i <= $loan_weeks; $i++) {
            $weed_due_date = $loan_approved_date->addWeeks($i)->format('Y-m-d');
            array_push($schedules,['loan_id' => $loan->id,'week_number' => $i, 'schedule_date' => $weed_due_date,'amount' => $weekly_amount,'status' => 'DUE']);
        }
        return $schedules;
    }
}
