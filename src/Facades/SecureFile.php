<?php
/**
 * Created by PhpStorm.
 * User: s.bahador
 * Date: 10/24/2019
 * Time: 5:57 PM
 */
namespace SaliBhdr\UrlSigner\Facades;

use Illuminate\Support\Facades\Facade;

class SecureFile extends Facade
{
    /**    * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'typhoonSecureFile';
    }
}