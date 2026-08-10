<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of inputs that are never flashed for validation exceptions.
     *
     * @var list<string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var list<class-string<Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        HttpException::class,
        ValidationException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (NotFoundHttpException $e) {
            if ($this->isJsonRequest()) {
                return $this->jsonError('Route introuvable.', 404, $e);
            }
        });

        $this->renderable(function (AuthenticationException $e) {
            if ($this->isJsonRequest()) {
                return $this->jsonError('Non authentifié.', 401, $e);
            }
        });

        $this->renderable(function (ValidationException $e) {
            if ($this->isJsonRequest()) {
                return $this->jsonError($e->getMessage(), 422, $e, $e->errors());
            }
        });

        $this->renderable(function (HttpException $e) {
            if ($this->isJsonRequest()) {
                return $this->jsonError($e->getMessage() ?: 'Erreur.', $e->getStatusCode(), $e);
            }
        });

        $this->renderable(function (Throwable $e) {
            if ($this->isJsonRequest()) {
                $message = $this->isHttpException($e)
                    ? $e->getMessage()
                    : ($this->shouldShowInternalError() ? $e->getMessage() : 'Une erreur interne est survenue.');

                $status = $this->isHttpException($e) ? $e->getStatusCode() : 500;

                return $this->jsonError($message, $status, $e);
            }
        });
    }

    /**
     * Determine whether the current request expects a JSON error payload.
     */
    protected function isJsonRequest(): bool
    {
        $request = request();

        return $request->expectsJson()
            || $request->is('api/*')
            || $request->is('api/v1/*');
    }

    /**
     * Whether to surface internal error messages in the response.
     */
    protected function shouldShowInternalError(): bool
    {
        return config('app.debug') === true;
    }

    /**
     * Build a consistent JSON error payload without leaking internals.
     *
     * @param  array<string, list<string>>|null  $errors
     */
    protected function jsonError(string $message, int $status, Throwable $e, ?array $errors = null): JsonResponse
    {
        $payload = ['message' => $message];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        if (config('app.debug') && ! $this->isHttpException($e)) {
            $payload['exception'] = $e::class;
            $payload['trace'] = collect($e->getTrace())->take(8)->all();
        }

        return response()->json($payload, $status);
    }
}
