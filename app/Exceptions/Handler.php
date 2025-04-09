<?php

namespace App\Exceptions;

use App\Utils\Exceptions\UnauthorizedApiException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;

use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

use Illuminate\Database\QueryException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = ["password", "password_confirmation"];

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

    public function render($request, $e)
    {
        /*if ($e instanceof UnauthorizedApiException) {
            return $e->render($request);
        }

        // return parent::render($request, $e);
        return response()->json([ // ahora todas las excepciones que se lanzan seran api response compliant!
            "success" => false,
            "data" => parent::render($request, $e),
            "message" => "Exception!",
        ]);
        */

        switch (true) {
            // workaround para switches con instanceof
            case $e instanceof UnauthorizedApiException:
                return $e->render($request);
            case $e instanceof NotFoundHttpException:
                return sendApiFailure(
                    parent::render($request, $e),
                    "Not Found",
                    404
                );
            case $e instanceof ValidationException:
                $json = parent::render($request, $e);
                return sendApiFailure(
                    $json->original["errors"],
                    $json->original["message"]
                );
            case $e instanceof TokenExpiredException:
                return sendApiFailure(
                    ["token_error" => "expired"],
                    "Token expired",
                    401
                ); // HTTP 401: NOT AUTHED
            case $e instanceof TokenInvalidException:
                return sendApiFailure(
                    ["token_error" => "invalid"],
                    "Token invalid",
                    401
                );
            case $e instanceof QueryException:
                if (
                    strpos(strtolower($e->getMessage()), "duplicate entry") !==
                    false
                ) {
                    // cualquier otro tipo
                    return sendApiFailure(
                        (object) [],
                        "Se intentó duplicar un registro único",
                        422
                    );
                }
                // cualquier otro tipo de exception
                $json = parent::render($request, $e);
                return sendApiFailure(
                    ["outer" => $json->original, "inner" => $e->getMessage()],
                    "Fallo Mysql: " . $json->original["message"],
                    500
                );

            case parent::render($request, $e)->original["message"] ==
                "Call to a member function allowOrFail() on null":
                return sendApiFailure(
                    ["token_error" => "missing user"],
                    "El usuario en el JWT ya no existe",
                    401
                );
            default:
                return sendApiFailure(
                    ["inner_message" => $e->getMessage()],
                    "Excepción crítica no tratada",
                    500
                );
            /*
                return response()->json([
                    "success" => false,
                    "data" => parent::render($request, $e),
                    "message" => "Exception!",
                ]);*/
        }
    }
}
