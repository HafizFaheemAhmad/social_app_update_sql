<?php

namespace App\Providers\ResponseServiceProvider;

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPassword;
use App\Http\Requests\ForgotRequest;
use App\Providers\ResponseServiceProvider;

class ForgotPasswordController extends Controller
{

    public function forgotPassword(ForgotRequest $request)
    {
        try {
            $input = $request->validated();
            $user_data = User::where('email', $input['email'])->first();
            $string = "ABC";
            $password = substr(str_shuffle(str_repeat($string, 12)), 0, 12);
            $user_data->password = bcrypt($password);
            $user_data->save();
            //for generate link in URL
            $details['link'] = url('api/forogtpassword/' . $user_data->password . 'api/email/' . $user_data->email . '/');
            Mail::to($input['email'])->send(new ForgotPassword($details));
            if ($details) {
                $success['message'] =  "New Password Send to Your Mail";
                return ResponseServiceProvider::sendResponse($success, 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
