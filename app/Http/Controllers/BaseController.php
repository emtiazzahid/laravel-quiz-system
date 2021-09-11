<?php

namespace App\Http\Controllers;

class BaseController extends Controller
{
    public function sendResponse($result, $message, $code = 200)
    {
        $response = [
            'success' => true,
            'results'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    public function sendError($message, $error = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($error)) {
            $response['errors'] = $error;
        }

        return response()->json($response, $code);
    }
}
