<?php

// src/Exception/ProductOutOfStockException.php
namespace App\Exception;

class ProductOutOfStockException extends \Exception
{
    public function __construct(string $message = "Product is out of stock", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
