<?php
/**
 * Created by PhpStorm.
 * User: s.bahador
 * Date: 1/28/2019
 * Time: 2:28 PM
 */

namespace SaliBhdr\SecureFile\Exceptions;

use Throwable;

class FileNotBelongsToUserException extends SecureFileException
{
    public function __construct(string $message = 'File is not belongs to this user', int $code = 402,Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}