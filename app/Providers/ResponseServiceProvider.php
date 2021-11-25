<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    public static function sendResponse($result, $status_code)
    {
        $response = [
            'success' => true,
            'data'    => $result,
        ];
        return response()->json($response, $status_code);
    }

    public static function sendError($error, $code = 505)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }

    public function boot()
    {
        //
    }
}
