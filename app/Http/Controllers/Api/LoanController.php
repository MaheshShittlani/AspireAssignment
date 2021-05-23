<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use Validator;
use Carbon\Carbon;
use App\Models\LoanSchedule;

class LoanController extends Controller
{
    /**
     * Traits which have the functionality to make weekly schedules
     */
    use \App\Traits\Schedule;
    

    /**
     * Calculate final loan amount by using simple interest
     * @param amount - Principal amount
     * @param roi - Rate of interest
     * @param loan_terms - Time of loan in years
     * 
     * @return amount - Principal + interest
     */
    private function calcLoanAmount($amount, $roi, $loan_terms) {
        $amount = $amount + ($amount * $roi * $loan_terms / 100);
        return $amount;
    }

    /**
     * Apply for the new loan - A user can only apply for a new loan if any PENDING loan doesn't exists.
     * 
     * @param amout
     * @param loan_terms 
     * 
     * @return json
     */

    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'amount' => 'required|integer',
            'loan_terms' => 'required|numeric'
        ]);      

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        $user = $request->user();
        
        
        $loan = Loan::where(['user_id' => $user->id,'status' => 'PENDING'])->first();
        if($loan) {
            return response()->json(['msg' => 'Already Applied for a loan which have panding status','data' => ['loan' => $loan]],409);
        }

        try {
            $loan = Loan::create([
                'amount' => $request->amount,
                'loan_terms' => $request->loan_terms,
                'user_id' => $user->id,
            ]);
            return response()->json(['msg' => 'Application Submitted Successfully.','data'=>['user' => $loan]],201);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Something went wrong.','error' => $e->getMessage()],500);
        }
    }

    /**
     * Update loan status - Expected that once user apply for the loan it can be Approved / UnApproved.
     * This api is executed by admin, for the sake of simplity no seperate admin panel is created.
     * This api can be run without any authentication.
     * 
     * Ideally it should be run by the authencated admin user.
     * 
     * @param user_id
     * @param loan_id
     * @param status
     * @param roi
     * 
     * @return json
     */
    public function updateStatus(Request $request)
    {   
        $validator = Validator::make($request->all(),[
            'user_id' => 'required|integer',
            'loan_id' => 'required|integer',
            'status' => 'required|in:PENDING,APPROVED,UNAPPROVED',
            'roi' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        try {
            $loan = Loan::where(['id' => $request->loan_id,'user_id' => $request->user_id])->first();
            if($loan) {
                $loan->status = $request->status;
                $loan->roi = $request->roi;
                $loan->loan_amount = $this->calcLoanAmount($loan->amount, $loan->roi, $loan->loan_terms);
                $loan->approved_at = Carbon::now();
                $loan->save();
                if($loan->status === 'APPROVED') {
                    $schedules = $this->generateSchedule($loan);
                    $loan->schedules()->createMany($schedules);
                }
                return response()->json(['msg' => 'Loan status updated successfully','data' => ['loan' => $loan]]);
            }else {
                return response()->json(['msg' => 'Invalid Loan Details'],409);
            }
        } catch(Exception $e) {
            return response()->json(['msg' => 'Something went wrong', 'error' => $e->getMessage()],500);
        }

    }
    /**
     * This api is just to show the complete loan information along with user and schedule.
     * This api supposed to be executed by the admin.
     * 
     * Ideally this api should be executed by authenticated admin user but here for the sake of simplicity
     * it can be run with any admin authentication.
     * 
     * @param loan_id
     * @return json
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'loan_id' => 'required|integer',
        ]);

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        $loan = Loan::where('id',$request->loan_id)->with('user','schedules')->first();
        return response()->json(['msg' => 'Loan Details','data' => $loan]);
    }

    /**
     * This api implements the functionality to pay the weekly payment by the user.
     * 
     * @param loan_id
     * @param amount
     * 
     * @return json
     */
    public function rePay(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'loan_id' => 'required|integer',
            'amount' => 'required|integer',
        ]);

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }
        $user = $request->user();
        try {
            $scheduleToPay = LoanSchedule::where(['loan_id' => $request->loan_id,'status' => 'DUE'])->first();
            $loan =  $scheduleToPay->loan()->first();
            if($loan->user_id !== $user->id) {
                return response()->json(['msg' => 'Load Details doesn\'t belong to user. Try again.'],409);
            }
            if($request->amount != $scheduleToPay->amount) {
                return response()->json(['msg' => 'Invalid Amount'],409);
            }

            $scheduleToPay->status = 'PAID';
            $scheduleToPay->save();
            return response()->json(['msg' => 'Weekly payment paid successfully.','data' => ['payment_details' => $scheduleToPay]]);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Something went wrong', 'error' => $e->getMessage()],500);
        }
    }
}
