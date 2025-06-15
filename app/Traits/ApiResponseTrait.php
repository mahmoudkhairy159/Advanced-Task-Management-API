<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Send a success response with data, message, and status statusCode.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, $message = '', $statusCode = 200)
    {
        $response = ['data' => $data];
        $response['success'] = true;
        $response['statusCode'] = $statusCode;
        if (!empty($message)) {
            $response['message'] = $message;
        }
        return response()->json($response, $statusCode);
    }

    /**
     * Send an error response with errors list, message, and status statusCode.
     *
     * @param array|string $errors
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($errors = [], $message = '', $statusCode = 400)
    {
        $response = ['message' => $message];
        $response['success'] = false;
        $response['statusCode'] = $statusCode;
        if (!empty($errors)) {
            $response['errors'] = is_array($errors) ? $errors : [$errors];
        }
        return response()->json($response, $statusCode);
    }

    /**
     * Send a message response with a message and status statusCode.
     *
     * @param string $message
     * @param bool $success
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function messageResponse($message, $success = true, $statusCode = 200)
    {
        $response = ['message' => $message];
        $response['statusCode'] = $statusCode;
        if (!empty($success)) {
            $response['success'] = $success;
        }
        return response()->json($response, $statusCode);
    }
}
