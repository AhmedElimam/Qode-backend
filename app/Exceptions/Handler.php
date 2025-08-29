<?php

namespace App\Exceptions;

use App\Traits\ApiExceptionTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiExceptionTrait;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($request, $e);
            }
        });
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleAuthenticationException($exception);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Convert a validation exception into a JSON response.
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleValidationException($exception);
        }

        return parent::invalidJson($request, $exception);
    }

    /**
     * Convert a model not found exception into a JSON response.
     */
    protected function renderModelNotFound($request, ModelNotFoundException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleModelNotFoundException($exception);
        }

        return parent::renderModelNotFound($request, $exception);
    }

    /**
     * Convert a not found HTTP exception into a JSON response.
     */
    protected function renderNotFound($request, NotFoundHttpException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::renderNotFound($request, $exception);
    }

    /**
     * Convert an HTTP exception into a JSON response.
     */
    protected function renderHttpException($request, HttpException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::renderHttpException($request, $exception);
    }
}
