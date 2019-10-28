<?php
/**
 * Created by PhpStorm.
 * User: s.bahador
 * Date: 10/24/2019
 * Time: 5:22 PM
 */

namespace SaliBhdr\SecureFile\ServiceProviders;

use SaliBhdr\SecureFile\SecureFile;
use Illuminate\Support\ServiceProvider;
use SaliBhdr\SecureFile\LaravelUrlSigner;
use Laravel\Lumen\Application as LumenApplication;
use SaliBhdr\UrlSigner\Laravel\Commands\SignerKeyGenerate;

class SecureFileServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignerNotFoundException
     */
    public function register()
    {
        $this->setupConfig();

        $this->commands([
            SignerKeyGenerate::class,
        ]);

        $urlSigner = new LaravelUrlSigner();

        $this->app->singleton(SecureFile::class, function ($app) use ($urlSigner){
            return new SecureFile($urlSigner->getUrlSigner());
        });

        $this->app->alias(SecureFile::class, 'typhoonSecureFile');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $this->addConfig();

        $this->mergeConfigFrom($this->getConfigFile(), 'secure-file');
    }

    /**
     * published config file
     */
    protected function addConfig()
    {
        if($this->app instanceof LumenApplication){
            $this->app->configure('secure-file');
        }else{
            $this->publishes([$this->getConfigFile() => config_path('secure-file.php')],'typhoonSecureFile');
        }
    }

    /**
     * gets config file
     *
     * @return string
     */
    protected function getConfigFile()
    {
        return __DIR__ . '../../config/secure-file.php';
    }
}