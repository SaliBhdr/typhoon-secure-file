<?php
/**
 * Created by PhpStorm.
 * User: s.bahador
 * Date: 3/10/2019
 * Time: 3:49 PM
 */

namespace SaliBhdr\SecureFile\File;

class AudioFileHandler extends AbstractFileHandler
{
    /**
     * @return bool
     */
    protected function isBasedOnUser(): bool
    {
        return true;
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
        return $this->baseUrl('downloads/audio-book');
    }
}