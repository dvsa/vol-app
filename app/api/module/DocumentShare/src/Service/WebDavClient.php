<?php

namespace Dvsa\Olcs\DocumentShare\Service;

use Dvsa\Olcs\DocumentShare\Data\Object\File;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

class WebDavClient implements DocumentStoreInterface
{
    public const DS_DOWNLOAD_FILE_PREFIX = 'ds_dwnld_';

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * Client constructor.
     *
     * @param FilesystemInterface $filesystem File System
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Read content from document store
     *
     * @param string $path Path
     */
    public function read($path): File | false
    {
        $tmpFileName = tempnam(sys_get_temp_dir(), self::DS_DOWNLOAD_FILE_PREFIX);

        try {
            $readStream = $this->filesystem->readStream($path);
            file_put_contents($tmpFileName, $readStream);

            $file = new File();
            $file->setContentFromStream($tmpFileName);

            if ($file->getSize() !== 0) {
                return $file;
            }
        } catch (FileNotFoundException) {
            return false;
        } finally {
            if (is_file($tmpFileName)) {
                unlink($tmpFileName);
            }
        }

        return false;
    }

    /**
     * Remove file on storage
     *
     * @param string $path Path to file on storage
     *
     * @param bool   $hard
     *
     * @return bool
     */
    public function remove($path, $hard = false): bool
    {
        try {
            return $this->filesystem->delete($path);
        } catch (FileNotFoundException) {
            return false;
        }
    }

    /**
     * Store file on remote storage
     *
     * @param string $path File Path on storage
     * @param File   $file File
     *
     * @return WebDavResponse
     * @throws \Exception
     */
    public function write($path, File $file)
    {
        $response = new WebDavResponse();
        try {
            $fh = fopen($file->getResource(), 'rb');
            $response->setResponse($this->filesystem->writeStream($path, $fh));
        } catch (FileExistsException) {
            $response->setResponse(false);
        } finally {
            @fclose($fh);
        }
        return $response;
    }
}
