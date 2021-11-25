<?php

namespace App\Service;

use \Firebase\JWT\JWT;
use \Firebase\JWT\key;

class jwtAuth
{
    public static function createJwtToken($user_data)
    {
        $payload_info = array(
            "iss" => "localhost",
            "iat" => time(),
            "nbf" => time() + 10,
            "exp" => time() + 1800,
            "aud" => "app",
            "data" => $user_data
        );
        try {

            $Auth_key = JWT::encode($payload_info, config('constant.JWT_SECRET_KEY', 'Hafiz'));

            return array('token' => $Auth_key);
        } catch (\Exception $e) {

            return array('error' => $e->getMessage());
        }
    }

    public static function varifyToken($token)
    {
        try {

            $decoded = JWT::decode($token, new Key(config('constant.JWT_SECRET_KEY', 'Hafiz'), 'HS256'));
            return $decoded;
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
