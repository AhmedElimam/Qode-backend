<?php

namespace App\Traits;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait ApiExceptionTrait
{
    use ApiResponseTrait;

    public function handleApiException(Request $request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return $this->validationErrorResponse(
                $exception->errors(),
                'Validation failed'
            );
        }

        if ($exception instanceof AuthenticationException) {
            return $this->unauthorizedResponse(
                'Authentication required'
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->notFoundResponse(
                'Resource not found'
            );
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->notFoundResponse(
                'Endpoint not found'
            );
        }

        if ($exception instanceof HttpException) {
            return $this->errorResponse(
                $exception->getMessage() ?: 'HTTP error occurred',
                $exception->getStatusCode()
            );
        }

        if (config('app.debug')) {
            return $this->errorResponse(
                $exception->getMessage(),
                500,
                [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]
            );
        }

        return $this->serverErrorResponse(
            'An unexpected error occurred'
        );
    }

    protected function handleValidationException(ValidationException $exception): JsonResponse
    {
        return $this->validationErrorResponse(
            $exception->errors(),
            'The given data was invalid'
        );
    }

    protected function handleModelNotFoundException(ModelNotFoundException $exception): JsonResponse
    {
        $modelName = strtolower(class_basename($exception->getModel()));
        return $this->notFoundResponse(
            "Unable to find {$modelName} with the specified identifier"
        );
    }

    protected function handleAuthenticationException(AuthenticationException $exception): JsonResponse
    {
        return $this->unauthorizedResponse(
            'Unauthenticated'
        );
    }

    protected function handleAuthorizationException($exception): JsonResponse
    {
        return $this->forbiddenResponse(
            $exception->getMessage() ?: 'You do not have permission to perform this action'
        );
    }

    protected function handleQueryException($exception): JsonResponse
    {
        if (config('app.debug')) {
            return $this->errorResponse(
                'Database error occurred',
                500,
                [
                    'message' => $exception->getMessage(),
                    'sql' => $exception->getSql(),
                    'bindings' => $exception->getBindings(),
                ]
            );
        }

        return $this->serverErrorResponse(
            'Database error occurred'
        );
    }

    protected function handleThrottleRequestsException($exception): JsonResponse
    {
        return $this->errorResponse(
            'Too many requests. Please try again later.',
            429
        );
    }
}
