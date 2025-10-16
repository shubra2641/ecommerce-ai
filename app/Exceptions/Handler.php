<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Exception;
use Log;

/**
 * Exception Handler
 * 
 * This class handles all exceptions thrown by the application and provides
 * appropriate responses based on the exception type and request context.
 * 
 * @package App\Exceptions
 * @author Laravel Application
 * @version 1.0.0
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     * 
     * These exceptions will not be logged to the application log.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     * 
     * These inputs will not be included in the session flash data.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
    ];

    /**
     * Report or log an exception.
     * 
     * This method is called when an exception is thrown and needs to be logged.
     * It provides enhanced logging with context information.
     *
     * @param Throwable $exception The exception to report
     * @return void
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        try {
            // Log exception with context
            $this->logException($exception);
            
            // Call parent report method
            parent::report($exception);
            
        } catch (Exception $e) {
            // Fallback logging if parent report fails
            Log::error('Failed to report exception: ' . $e->getMessage(), [
                'original_exception' => $exception->getMessage(),
                'original_trace' => $exception->getTraceAsString()
            ]);
        }
    }

    /**
     * Render an exception into an HTTP response.
     * 
     * This method determines how to display the exception to the user
     * based on the request type and exception type.
     *
     * @param Request $request The HTTP request
     * @param Throwable $exception The exception to render
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        try {
            // Handle different exception types
            if ($exception instanceof ValidationException) {
                return $this->handleValidationException($exception, $request);
            }
            
            if ($exception instanceof AuthenticationException) {
                return $this->handleAuthenticationException($exception, $request);
            }
            
            if ($exception instanceof ModelNotFoundException) {
                return $this->handleModelNotFoundException($exception, $request);
            }
            
            if ($exception instanceof NotFoundHttpException) {
                return $this->handleNotFoundHttpException($exception, $request);
            }
            
            if ($exception instanceof MethodNotAllowedHttpException) {
                return $this->handleMethodNotAllowedException($exception, $request);
            }
            
            if ($exception instanceof QueryException) {
                return $this->handleQueryException($exception, $request);
            }
            
            if ($exception instanceof HttpException) {
                return $this->handleHttpException($exception, $request);
            }
            
            // Default handling
            return parent::render($request, $exception);
            
        } catch (Exception $e) {
            // Fallback error handling
            Log::error('Error in exception renderer: ' . $e->getMessage());
            return $this->handleGenericException($exception, $request);
        }
    }

    /**
     * Log exception with enhanced context
     * 
     * @param Throwable $exception
     * @return void
     */
    private function logException(Throwable $exception): void
    {
        $context = [
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ];

        // Log based on exception severity
        if ($exception instanceof HttpException && $exception->getStatusCode() >= 500) {
            Log::error('Server error occurred', $context);
        } elseif ($exception instanceof ValidationException) {
            Log::warning('Validation error occurred', $context);
        } else {
            Log::error('Application error occurred', $context);
        }
    }

    /**
     * Handle validation exceptions
     * 
     * @param ValidationException $exception
     * @param Request $request
     * @return Response
     */
    private function handleValidationException(ValidationException $exception, Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $exception->errors()
            ], 422);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle authentication exceptions
     * 
     * @param AuthenticationException $exception
     * @param Request $request
     * @return Response
     */
    private function handleAuthenticationException(AuthenticationException $exception, Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'Authentication required'
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Handle model not found exceptions
     * 
     * @param ModelNotFoundException $exception
     * @param Request $request
     * @return Response
     */
    private function handleModelNotFoundException(ModelNotFoundException $exception, Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Resource not found',
                'error' => 'The requested resource does not exist'
            ], 404);
        }

        return response()->view('errors.404', [], 404);
    }

    /**
     * Handle not found HTTP exceptions
     * 
     * @param NotFoundHttpException $exception
     * @param Request $request
     * @return Response
     */
    private function handleNotFoundHttpException(NotFoundHttpException $exception, Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Page not found',
                'error' => 'The requested page does not exist'
            ], 404);
        }

        return response()->view('errors.404', [], 404);
    }

    /**
     * Handle method not allowed exceptions
     * 
     * @param MethodNotAllowedHttpException $exception
     * @param Request $request
     * @return Response
     */
    private function handleMethodNotAllowedException(MethodNotAllowedHttpException $exception, Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Method not allowed',
                'error' => 'The HTTP method is not allowed for this resource'
            ], 405);
        }

        return response()->view('errors.405', [], 405);
    }

    /**
     * Handle database query exceptions
     * 
     * @param QueryException $exception
     * @param Request $request
     * @return Response
     */
    private function handleQueryException(QueryException $exception, Request $request): Response
    {
        // Log the actual database error for debugging
        Log::error('Database query error: ' . $exception->getMessage(), [
            'sql' => $exception->getSql(),
            'bindings' => $exception->getBindings()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Database error occurred',
                'error' => 'A database error occurred while processing your request'
            ], 500);
        }

        return response()->view('errors.500', [], 500);
    }

    /**
     * Handle HTTP exceptions
     * 
     * @param HttpException $exception
     * @param Request $request
     * @return Response
     */
    private function handleHttpException(HttpException $exception, Request $request): Response
    {
        $statusCode = $exception->getStatusCode();
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $exception->getMessage() ?: 'HTTP error occurred',
                'error' => 'An HTTP error occurred'
            ], $statusCode);
        }

        return response()->view("errors.{$statusCode}", [], $statusCode);
    }

    /**
     * Handle generic exceptions
     * 
     * @param Throwable $exception
     * @param Request $request
     * @return Response
     */
    private function handleGenericException(Throwable $exception, Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => 'An unexpected error occurred while processing your request'
            ], 500);
        }

        return response()->view('errors.500', [], 500);
    }
}