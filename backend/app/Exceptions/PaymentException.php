<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    protected $message = 'Payment processing failed';
    protected $code = 400;

    public function __construct(string $message = null, int $code = 400)
    {
        if ($message) {
            $this->message = $message;
        }

        $this->code = $code;

        parent::__construct($this->message, $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error_type' => 'payment_error'
        ], $this->code);
    }
}
