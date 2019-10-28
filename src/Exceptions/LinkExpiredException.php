<?php
/**
 * Created by PhpStorm.
 * User: s.bahador
 * Date: 2/20/2019
 * Time: 11:14 AM
 */

namespace SaliBhdr\SecureFile\Exceptions;

use Throwable;

class LinkExpiredException extends SecureFileException
{
    public function __construct(string $message = 'File Link is Expire', int $code = 410,Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}