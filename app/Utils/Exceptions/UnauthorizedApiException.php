<?php

namespace App\Utils\Exceptions;

use \Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnauthorizedApiException extends Exception {

    protected int $statusCode;
    protected string $essage;

    public function __construct(string $message = "Usuario no cuenta con el permiso necesario.", int $code = 403){
        //$this->statusCode = $code;
        //$this->message = $message;
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse {
        return response()->json([
            "success" => false,
            "data" => (object)[],
            "message" => $this->message,
        ], $this->code);
    }
}
