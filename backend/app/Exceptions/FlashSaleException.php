<?php

namespace App\Exceptions;

use Exception;

class FlashSaleException extends Exception
{
    protected $message = 'Flash sale error';
    protected $code = 400;

    public function __construct(string $message)
    {
        $this->message = $message;
        parent::__construct($this->message, $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error_type' => 'flash_sale_error'
        ], $this->code);
    }
}
