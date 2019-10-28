<?php
/**
 * Created by PhpStorm.
 * User: s.bahador
 * Date: 10/24/2019
 * Time: 5:30 PM
 */

namespace SaliBhdr\SecureFile;

use SaliBhdr\UrlSigner\Exceptions\SignerNotFoundException;
use SaliBhdr\UrlSigner\Laravel\LaravelUrlSigner as BaseUrlSigner;

class LaravelUrlSigner extends BaseUrlSigner
{
    /**
     * UrlSigner constructor.
     * @throws SignerNotFoundException
     */
    public function __construct()
    {
        $this->config = config('secure-file');

        $this->setUrlSigner();
    }

}