<?php

namespace Dvsa\Olcs\Cli\Service\Utils;

/**
 * Class helper for creation file
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class ExportToCsv
{
    public const ERR_CANT_CREATE_DIR = 'Can\'t create directory to file: ';
    public const ERR_CANT_CREATE_FILE = 'Can\'t create file ';

    /**
     * Create file for writing
     *
     * @param string $filePath
     *
     * @return resource
     */
    public static function createFile($filePath)
    {
        self::createDir($filePath);

        //  open file & add rows
        $fh = @fopen($filePath, 'w');
        if ($fh === false) {
            throw new \Exception(self::ERR_CANT_CREATE_FILE . $filePath);
        }

        return $fh;
    }

    /**
     * Create directories to file if not exists
     *
     * @param string $filePath
     */
    private static function createDir($filePath)
    {
        $dir = dirname($filePath);
        if (!@mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new \Exception(self::ERR_CANT_CREATE_DIR . $filePath);
        }
    }
}
