<?php

namespace Dvsa\Olcs\Utils\Helper;

/**
 * Utility file for File
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class FileHelper
{
    /**
     * Get file extention from path
     *
     * @param string|null $path Path to file
     *
     * @return string
     */
    public static function getExtension($path)
    {
        if ($path === null) {
            return '';
        }

        return substr(strrchr($path, '.'), 1);
    }
}
