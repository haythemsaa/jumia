<?php

namespace App\Exceptions;

use Exception;

class InvalidCouponException extends Exception
{
    protected $message = 'Invalid or expired coupon';
    protected $code = 400;

    public function __construct(string $reason = null)
    {
        if ($reason) {
            $this->message = "Coupon error: {$reason}";
        }

        parent::__construct($this->message, $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error_type' => 'invalid_coupon'
        ], $this->code);
    }
}
