<?php

namespace App\Providers\ResponseServiceProvider;
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friend;
use \Firebase\JWT\JWT;
use \Firebase\JWT\key;
use App\Providers\ResponseServiceProvider;
use Exception;

class FriendController extends Controller
{

// Add Friend
    public function addFriend(Request $req)
    {
        $fid = $req->fid;
        $token = request()->bearerToken();
        try {
            $decoded_data = JWT::decode($token, new Key('example', 'HS256'));
            User::where("jwt_token", $token)->first();
            if ($decoded_data->data->id != $fid) {
                if (Friend::where(['userid_1' => $decoded_data->data->id, 'userid_2' => $fid])
                    ->orwhere(['userid_1' => $fid, 'userid_2' => $decoded_data->data->id])
                    ->doesntExist()
                ) {
                    $friend = new Friend;
                    $friend->userid_1 = $decoded_data->data->id;
                    $friend->userid_2 = $fid;
                    $friend->save();
                    $success['message'] = "you are friend now of $fid";
                    return ResponseServiceProvider::sendResponse($success, 200);
                } else {
                    $success['message'] = "User with id = $fid is already your friend";
                    throw new Exception($success['message']);
                }
            } else {
                $success['message'] = "you are not allow to be friend of yourself";
                throw new Exception($success['message']);
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

//Remove Friend
    public function removeFriend(Request $req)
    {
        try {
            $fid = $req->fid;
            $token = request()->bearerToken();
            $decoded_data = JWT::decode($token, new Key('example', 'HS256'));
            User::where("jwt_token", $token)->first();

            if (Friend::where(['userid_1' => $decoded_data->data->id, 'userid_2' => $fid])->delete()) {
                $success['message'] = "Unfriend Successfuly";
                return ResponseServiceProvider::sendResponse($success, 200);
            } else {
                $success['message'] = "You are not the friend of $fid";
                throw new Exception($success['message']);
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
