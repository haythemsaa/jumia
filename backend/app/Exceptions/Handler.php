<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
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

        // Custom rendering for API responses
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle API exceptions with consistent JSON responses
     */
    protected function handleApiException(Throwable $exception, $request)
    {
        $statusCode = 500;
        $message = __('messages.error');
        $errors = null;

        // Validation Exception
        if ($exception instanceof ValidationException) {
            $statusCode = 422;
            $message = __('messages.validation_error');
            $errors = $exception->errors();
        }

        // Authentication Exception
        elseif ($exception instanceof AuthenticationException) {
            $statusCode = 401;
            $message = __('messages.unauthorized');
        }

        // Authorization Exception
        elseif ($exception instanceof AccessDeniedHttpException) {
            $statusCode = 403;
            $message = __('messages.permission_denied');
        }

        // Model Not Found
        elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $message = __('messages.not_found');
        }

        // Not Found HTTP
        elseif ($exception instanceof NotFoundHttpException) {
            $statusCode = 404;
            $message = __('messages.not_found');
        }

        // Rate Limiting
        elseif ($exception instanceof TooManyRequestsHttpException) {
            $statusCode = 429;
            $message = 'Too many requests. Please try again later.';
        }

        // Payment Exceptions
        elseif ($exception instanceof PaymentException) {
            $statusCode = 400;
            $message = $exception->getMessage();
        }

        // Insufficient Stock Exception
        elseif ($exception instanceof InsufficientStockException) {
            $statusCode = 400;
            $message = $exception->getMessage();
        }

        // Insufficient Points Exception
        elseif ($exception instanceof InsufficientPointsException) {
            $statusCode = 400;
            $message = $exception->getMessage();
        }

        // Generic HTTP Exceptions
        elseif (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage() ?: __('messages.error');
        }

        // Log errors in production
        if (app()->environment('production') && $statusCode >= 500) {
            \Log::error('API Error', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);
        }

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        // Include stack trace in development
        if (app()->environment('local') && $statusCode >= 500) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorized')
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
