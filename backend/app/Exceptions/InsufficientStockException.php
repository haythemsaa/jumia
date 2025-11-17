<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $message = 'Insufficient stock available';
    protected $code = 400;

    public function __construct(string $productName, int $available)
    {
        $this->message = "Insufficient stock for {$productName}. Only {$available} available.";
        parent::__construct($this->message, $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error_type' => 'insufficient_stock'
        ], $this->code);
    }
}
