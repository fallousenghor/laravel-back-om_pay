<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Retourne une réponse de succès standardisée
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = '', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($message)) {
            $response['message'] = $message;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Retourne une réponse d'erreur standardisée
     *
     * @param string $errorCode
     * @param string $message
     * @param array $details
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(string $errorCode, string $message, array $details = [], int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $message,
            ]
        ];

        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Retourne une réponse depuis un résultat de service
     *
     * @param array $result
     * @return JsonResponse
     */
    protected function responseFromResult(array $result): JsonResponse
    {
        if ($result['success']) {
            $statusCode = $result['status'] ?? 200;
            $message = $result['message'] ?? '';
            $data = $result['data'] ?? null;

            return $this->successResponse($data, $message, $statusCode);
        } else {
            $errorCode = $result['error']['code'];
            $message = $result['error']['message'];
            $details = $result['error']['details'] ?? [];
            $statusCode = $result['status'] ?? 400;

            return $this->errorResponse($errorCode, $message, $details, $statusCode);
        }
    }

    /**
     * Retourne une réponse de succès avec pagination
     *
     * @param mixed $data
     * @param array $pagination
     * @param string $message
     * @return JsonResponse
     */
    protected function paginatedResponse($data, array $pagination, string $message = ''): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => [
                'items' => $data,
                'pagination' => $pagination
            ]
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    /**
     * Retourne une réponse de création réussie
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $message = ''): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Retourne une réponse de suppression réussie
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function deletedResponse(string $message = 'Suppression réussie'): JsonResponse
    {
        return $this->successResponse(null, $message, 200);
    }

    /**
     * Retourne une réponse de mise à jour réussie
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function updatedResponse($data = null, string $message = 'Mise à jour réussie'): JsonResponse
    {
        return $this->successResponse($data, $message, 200);
    }
}