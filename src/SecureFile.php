<?php
/**
 * Created by PhpStorm.
 * User: s.bahador
 * Date: 2/19/2019
 * Time: 10:43 AM
 */

namespace SaliBhdr\SecureFile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use SaliBhdr\UrlSigner\UrlSignerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use SaliBhdr\SecureFile\Exceptions\LinkExpiredException;
use SaliBhdr\UrlSigner\Exceptions\SignatureUrlExpiredException;
use SaliBhdr\SecureFile\Exceptions\FileNotBelongsToUserException;
use SaliBhdr\UrlSigner\Exceptions\SignatureTimestampMissingException;

class SecureFile
{
    public const SUBSCRIPTION_KEY = 'sub';
    public const PATH_KEY = 'path';

    protected $linkExpired = false;

    protected $params = [];

    protected $validateUser = true;

    /** @var UrlSignerInterface $signature */
    protected $urlSigner;

    public function __construct(UrlSignerInterface $urlSigner)
    {
        $this->urlSigner = $urlSigner;
    }

    /**
     * stream the file for android app
     * stream : get piece by piece
     *
     * @param $path
     * @return BinaryFileResponse
     */
    public static function getStreamedFile($path)
    {
        $response = new BinaryFileResponse($path);

        BinaryFileResponse::trustXSendfileTypeHeader();

        return $response;
    }

    /**
     * @param $downloadLink
     * @param $fileLink
     * @param bool $addUser
     * @return string
     */
    public function create($downloadLink, $fileLink, $addUser = true)
    {
        $this->addFile($fileLink);

        if ($addUser)
            $this->addUser();

        if (!is_null($fileLink) && File::exists($this->downloadPath($fileLink))) {
            return $this->urlSigner->create($downloadLink,$this->getParams());
        }

        return null;
    }

    /**
     * @return $this
     */
    protected function addUser()
    {
        $this->params[static::SUBSCRIPTION_KEY] = Request::instance()->bearerToken();

        return $this;
    }

    /**
     * @param $fileLink
     * @return $this
     */
    protected function addFile($fileLink)
    {
        $this->params[static::PATH_KEY] = $fileLink;

        return $this;
    }

    /**
     * @param $fileLink
     * @return string
     */
    public function downloadPath($fileLink)
    {
        return storage_path('app/' . $fileLink);
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getParams($key = null)
    {
        if (!is_null($key))
            return isset($this->params[$key]) ? $this->params[$key] : null;

        return $this->params;
    }

    /**
     * @param mixed $params
     * @return SecureFile
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileLink()
    {
        return $this->getParams(static::PATH_KEY);
    }

    /**
     * same validation without user id
     *
     * @param $path
     * @param array $params
     * @return SecureFile
     * @throws FileNotBelongsToUserException
     * @throws LinkExpiredException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureMissingException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureNotValidException
     */
    public function validateWithoutUser($path, array $params)
    {
        return $this->skipUserValidation()
            ->validate(null, $path, $params);

    }

    /**
     * validate requested url
     * validations can be user specific on links that are generates only for user
     * validations can be without user if links are made for every one
     *
     * @param int $userId
     * @param $path
     * @param array $params
     * @return SecureFile
     * @throws FileNotBelongsToUserException
     * @throws LinkExpiredException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureMissingException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureNotValidException
     */
    public function validate(?int $userId, $path, array $params)
    {
        $this->setParams($params)
            ->validateSignature($path, $params);

        if ($this->isUserMustValidate())
            $this->validateUser($userId);

        return $this;
    }

    /**
     * @param $path
     * @param $params
     * @return $this
     * @throws LinkExpiredException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureMissingException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureNotValidException
     */
    public function validateSignature($path, $params)
    {
        try{
            $this->urlSigner->validate($path, $params);
        }catch (SignatureUrlExpiredException $e){
            throw new LinkExpiredException($e->getMessage());
        }catch (SignatureTimestampMissingException $e){
            throw new LinkExpiredException($e->getMessage());
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isUserMustValidate(): bool
    {
        return $this->validateUser;
    }

    /**
     * @param $userId
     * @return $this
     * @throws FileNotBelongsToUserException
     */
    public function validateUser($userId)
    {
        if ($this->isFileBelongsToUser($userId)) {
            return $this;
        } else {
            throw new FileNotBelongsToUserException();
        }
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function isFileBelongsToUser($userId): bool
    {
        $signatureUser = $this->getUser();

        if (!is_null($signatureUser) && $signatureUser->id !== $userId) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->getSub()
            ? Auth::setToken($this->getSub())->user()
            : null;
    }

    /**
     * @return mixed
     */
    protected function getSub()
    {
        return $this->getParams(static::SUBSCRIPTION_KEY);
    }

    /**
     * user is not going to be check in hashed url
     * with this method you can disable user validation
     * useful with routes that are not user specific
     *
     * @return SecureFile
     */
    public function skipUserValidation(): SecureFile
    {
        $this->validateUser = false;

        return $this;
    }
}