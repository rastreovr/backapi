<?php

namespace App\Utils\Exceptions;

use \Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadEndpointException extends Exception
{
    public function __construct(
        string $message = "La autenticación no es válida",
        int $code = 401
    ) {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(
            [
                "success" => false,
                "data" => (object) [],
                "message" => $this->message,
            ],
            $this->code
        );
    }
}
