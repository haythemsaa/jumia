<?php

namespace App\Exceptions;

use Exception;

class InsufficientPointsException extends Exception
{
    protected $message = 'Insufficient loyalty points';
    protected $code = 400;

    public function __construct(int $required, int $available)
    {
        $this->message = "Insufficient points. Required: {$required}, Available: {$available}";
        parent::__construct($this->message, $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error_type' => 'insufficient_points'
        ], $this->code);
    }
}
