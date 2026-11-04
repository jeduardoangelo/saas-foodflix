<?php

namespace App\Exceptions;

use App\Http\Resources\Api\ErrorResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tymon\JWTAuth\Exceptions\JWTException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if (! $request->is('api/v1/*')) {
            return parent::render($request, $exception);
        }

        $status = 500;
        $code = 'server_error';
        $message = 'Não foi possível processar a solicitação.';
        $details = null;
        $headers = [];

        if ($exception instanceof ValidationException) {
            $status = 422;
            $code = 'validation_error';
            $message = 'Os dados informados são inválidos.';
            $details = $exception->errors();
        } elseif ($exception instanceof AuthenticationException || $exception instanceof JWTException) {
            $status = 401;
            $code = 'unauthenticated';
            $message = 'Token de autenticação ausente, inválido ou expirado.';
        } elseif ($exception instanceof AuthorizationException) {
            $status = 403;
            $code = 'forbidden';
            $message = 'Você não tem permissão para acessar este recurso.';
        } elseif ($exception instanceof ModelNotFoundException) {
            $status = 404;
            $code = 'not_found';
            $message = 'Recurso não encontrado.';
        } elseif ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $headers = $exception->getHeaders();

            if ($status === 401) {
                $code = 'unauthenticated';
                $message = 'Token de autenticação ausente, inválido ou expirado.';
            } elseif ($status === 403) {
                $code = 'forbidden';
                $message = 'Você não tem permissão para acessar este recurso.';
            } elseif ($status === 404) {
                $code = 'not_found';
                $message = 'Recurso não encontrado.';
            } elseif ($status === 429) {
                $code = 'too_many_requests';
                $message = 'Muitas tentativas. Aguarde e tente novamente.';
            }
        }

        return (new ErrorResource(compact('code', 'message', 'details')))
            ->response()
            ->setStatusCode($status)
            ->withHeaders($headers);
    }
}
