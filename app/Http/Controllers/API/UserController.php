<?php

namespace App\Providers\ResponseServiceProvider;

namespace App\Http\Controllers\API;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Providers\ResponseServiceProvider;
use App\Service\jwtAuth;


class UserController extends Controller
{

//For user Login
    public function login(LoginUserRequest $request)
    {
        try {

            $input = $request->validated();
            //check email and password for authentication
            if (Auth::attempt(['email' => $input['email'], 'password' => $input['password']])) {
                $user = Auth::user();
                $user_data = array(
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email
                );
                $jwt  = jwtAuth::createJwtToken($user_data);
                $user->jwt_token = $jwt['token'];
                //  $user->update();
                User::where("email", $user->email)->update(["jwt_token" => $jwt['token']]);
                $success['message'] =  " User Successful login.";
                $success['Authentication'] = $jwt['token'];
                return ResponseServiceProvider::sendResponse($success, 200);
            }
        } catch (\Exception $ex) {
            return ResponseServiceProvider::sendError(['error' => $ex->getMessage()], 500);
        }
    }

//for logout user
    public function logout(Request $request)
    {
        try {

            $token = $request->bearerToken();
            $delete = User::where("jwt_token", $token)->update(["jwt_token" => NULL]);
            if ($delete) {
                $success['message'] =  " User Successful Logout.";
                return ResponseServiceProvider::sendResponse($success, 200);
            } else {
                $error_message['message'] =  "Something went Worng!";
                throw new Exception($error_message['message']);
            }
        } catch (\Exception $ex) {
            return ResponseServiceProvider::sendError(['error' => $ex->getMessage()], 500);
        }
    }

//For Delete User
    public function DeleteUser($id)
    {
        try {
            $user = new User();
            $user = User::find($id);
            if ($user) {
                $user->delete();
                $success['message'] =  " User Successfully Delete.";
                return ResponseServiceProvider::sendResponse($success, 200);
            } else {
                $error_message['message'] =  "User not exist";
                throw new Exception($error_message['message']);
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

//For Search User
    public function SearchUser($name)
    {
        return User::where('name', 'like', '%' . $name . '%')->get();
    }

//For Update user
    public function UpdateUser(Request $request, $id)
    {
        try {
            $user = User::find($id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = $request->input('password');
            $input = $request;
            $file_name = null;
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
                $input['profile_image'] = $file_name;

                $user->save();
                $success['message'] =  "User Updated Successfully";
                return ResponseServiceProvider::sendResponse($success, 200);
            } else {
                $error_message['message'] =  "Something went Worng!";
                throw new Exception($error_message['message']);
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
