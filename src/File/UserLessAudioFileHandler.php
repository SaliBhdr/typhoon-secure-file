<?php
/**
 * Created by PhpStorm.
 * User: s.bahador
 * Date: 3/10/2019
 * Time: 3:49 PM
 */

namespace SaliBhdr\SecureFile\File;

/**
 * user less means no need for user validation for this download link
 */
class UserLessAudioFileHandler extends AbstractFileHandler
{

    /**
     * @return bool
     */
    protected function isBasedOnUser(): bool
    {
        return false;
    }

    /**
     * @return string|null
     */
    protected function staticFilePath(): ?string
    {
       return null;
    }

    /**
     * user less download link based on app version
     *
     * @return string
     */
    protected function downloadUrl(): string
    {
        return $this->baseUrl('downloads/userless-audio-book');
    }
}