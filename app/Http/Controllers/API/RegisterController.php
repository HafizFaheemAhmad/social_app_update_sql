<?php

namespace App\Providers\ResponseServiceProvider;

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterUserRequest;
use App\Providers\ResponseServiceProvider;
use Exception;
use App\Jobs\SendEmailJob;

class RegisterController extends Controller
{
    public static $detail;

//For user Registration
    public function register(RegisterUserRequest $request)
    {
        try {
            //generate email verification token
            $input = $request->validated();
            $file_name = null;
            // converting base64 decoded image to simple image if exist
            if (!empty($input['attachment'])) {
                // upload Attachment
                $destinationPath = storage_path('\api\users\\');
                $input_type_aux = explode("/", $input['attachment']['mime']);
                $attachment_extention = $input_type_aux[1];
                $image_base64 = base64_decode($input['attachment']['data']);
                $file_name = $input['name'] . uniqid() . '.' . $attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
            }
            $email_varified_token = base64_encode($input['name']);
            $input['varified_token'] = $email_varified_token;
            $input['password'] = bcrypt($input['password']);
            $details= $input['email'];
            //$input['profile_image'] = $file_name;
            $user = User::create($input);
            //generate URL link
            dispatch(new SendEmailJob($details['email']));
            $success['name'] =  $user->name;
            if (isset($success)) {
                $success['message'] =  " User Successful Register.";
                return ResponseServiceProvider::sendResponse($success, 200);
            }
        } catch (\Exception $ex) {
            return ResponseServiceProvider::sendError(['error' => $ex->getMessage()], 500);
        }
    }

//For email verification on registration
    public function emailVarify($email, $token)
    {
        try {
            $user = User::where('email', $email)->where('varified_token', $token)->first();
            //check user is not empty
            if (!empty($user['email'])) {
                //get time of confirm verification
                $user->email_verified_at = date('Y-m-d h:i:s');
                $user->varified_token = '';
                //save confirmation time in database filed name "email_verified_at"
                $user->save();
                //send responsed if Email link is confirm
                $success['message'] =  " Your Email is confirm.Now you are successfully Register";
                return ResponseServiceProvider::sendResponse($success, 200);
            } else {
                //send responsed if Email link  is already use
                $error_message['message'] =  "'Unauthorised.', ['error' => 'Link already used', 'detail' => 'this link already in use please create anotherone'";
                throw new Exception($error_message['message']);
            }
        } catch (\Exception $ex) {
            return ResponseServiceProvider::sendError(['error' => $ex->getMessage()], 500);
        }
    }
}
