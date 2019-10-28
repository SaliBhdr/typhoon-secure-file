<?php
/**
 * Created by PhpStorm.
 * User: s.bahador
 * Date: 8/4/2019
 * Time: 3:52 PM
 */

namespace SaliBhdr\SecureFile\File;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use SaliBhdr\SecureFile\SecureFile;
use SaliBhdr\UrlSigner\Signature\Signature;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractFileHandler
{
    /** @var SecureFile $secureFile */
    protected $secureFile;

    /** @var \Illuminate\Http\Request $request */
    protected $request;

    public function __construct(SecureFile $secureFile)
    {
        $this->secureFile = $secureFile;
        $this->request    = Request::instance();
    }

    /**
     * @param int|null $user_id for validating download link based on user
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws ValidationException
     * @throws \SaliBhdr\SecureFile\Exceptions\FileNotBelongsToUserException
     * @throws \SaliBhdr\SecureFile\Exceptions\LinkExpiredException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureMissingException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureNotValidException
     */
    public function download(?int $user_id = null)
    {
        $this->validate($user_id);

        $path = $this->secureFile->downloadPath($this->secureFile->getFileLink());

        if (File::exists($path)) {
            return $this->secureFile::getStreamedFile($path);
        }

        throw new NotFoundHttpException();
    }

    /**
     *
     * @param int|null $user_id for validating download link based on user
     * @throws ValidationException
     * @throws \SaliBhdr\SecureFile\Exceptions\FileNotBelongsToUserException
     * @throws \SaliBhdr\SecureFile\Exceptions\LinkExpiredException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureMissingException
     * @throws \SaliBhdr\UrlSigner\Exceptions\SignatureNotValidException
     */
    protected function validate($user_id = null)
    {
        $this->validateRequest();

        if ($this->isBasedOnUser()) {
            $this->secureFile->validate(
                $this->getUserId($user_id),
                $this->downloadUrl(),
                $this->request->all());
        } else {

            $this->secureFile->validateWithoutUser(
                $this->downloadUrl(),
                $this->request->all()
            );
        }
    }

    /**
     * @throws ValidationException
     */
    protected function validateRequest()
    {
        $rules = [
            Signature::SIGNATURE_TIMESTAMP_KEY => 'required',
            Signature::SIGNATURE_KEY_NAME      => 'required|string',
            SecureFile::PATH_KEY               => 'required|string',
        ];

        if ($this->isBasedOnUser())
            $rules[SecureFile::SUBSCRIPTION_KEY] = 'required';

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->failed())
            throw new ValidationException($validator);

    }

    /**
     * @param $user_id
     * @return int|null
     */
    protected function getUserId($user_id)
    {
        if (is_null($user_id)) {
            if (function_exists('auth')) {
                $user_id = auth()->id();
            } else {
                $user_id = Auth::id();
            }
        }

        return $user_id;
    }

    /**
     * determines that the generated url need to be generated and validated based on user or not
     *
     * @return bool
     */
    protected abstract function isBasedOnUser(): bool;

    /**
     * location of the file (if the file is static)
     * if you must path the file dynamically you must return null in this method
     *
     * @return string|null
     */
    protected abstract function staticFilePath(): ?string;

    /**
     * download url that you can access through routes
     *
     * @return string
     */
    protected abstract function downloadUrl(): string;

    /**
     * base api url based on app version
     *
     * @param $url
     * @return string
     */
    public function baseUrl(?string $url)
    {
        return url($url);
    }

    /**
     * @param string|null $dynamicFilePath file path from storage/app/{$filePath}
     * @return string
     */
    public function makeDownloadLink(?string $dynamicFilePath)
    {
        return $this->secureFile
            ->create(
                $this->downloadUrl(),
                $dynamicFilePath ?? $this->staticFilePath(),
                $this->isBasedOnUser());
    }
}