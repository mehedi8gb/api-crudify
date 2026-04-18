<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends Controller
{
    // ==========================================
    // Response Helpers (DRY)
    // ==========================================

    /**
     * Send success response.
     */
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $status = Response::HTTP_OK
    ): JsonResponse {
        return sendSuccessResponse($message, $data, $status);
    }

    /**
     * Send error response.
     */
    protected function errorResponse(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        mixed $errors = null
    ): JsonResponse {
        return sendErrorResponse($message, $status, $errors);
    }
}
