<?php

namespace Dvsa\Olcs\DocumentShare\Service;

use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Laminas\Http\Response;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;

class WebDavClient implements DocumentStoreInterface
{
    public const DS_DOWNLOAD_FILE_PREFIX = 'ds_dwnld_';

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
    public function __construct(
        protected FilesystemInterface $filesystem,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Read content from document store
     *
     * @param string $path Path
     * @throws \Exception
     */
    #[\Override]
    public function read($path): File | false
    {
        $tmpFileName = tempnam(sys_get_temp_dir(), self::DS_DOWNLOAD_FILE_PREFIX);

        if ($tmpFileName === false) {
            $this->logger->error('Failed to create temp file', ['path' => $path, 'tmpDir' => sys_get_temp_dir()]);
            return false;
        }

        $this->logger->debug('Temp file created', ['tmpFileName' => $tmpFileName, 'is_file' => is_file($tmpFileName), 'is_readable' => is_readable($tmpFileName), 'is_writable' => is_writable($tmpFileName)]);

        try {
            $readStream = $this->filesystem->readStream($path);
            $fpc = file_put_contents($tmpFileName, $readStream);

            if ($fpc === false) {
                $this->logger->error('Failed to write file to temp location', ['path' => $path, 'tmpFileName' => $tmpFileName]);
                return false;
            }

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
     * @return Response
     */
    #[\Override]
    public function remove($path, $hard = false): Response
    {
        $response = new Response();

        try {
            $response->setStatusCode($this->filesystem->delete($path) ? 200 : 500);
        } catch (FileNotFoundException) {
            $response->setStatusCode(404);
        }

        return $response;
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
    #[\Override]
    public function write($path, File $file)
    {
        $response = new WebDavResponse();
        try {
            $this->logger->debug('Opening file for reading', ['file' => $file->getResource(), 'path' => $path]);

            $fh = fopen($file->getResource(), 'rb');

            if ($fh === false) {
                $this->logger->error('Failed to open file for reading', ['file' => $file->getResource(), 'path' => $path]);

                $response->setResponse(false);
            } else {
                $response->setResponse($this->filesystem->writeStream($path, $fh));
            }
        } catch (FileExistsException) {
            $response->setResponse(false);
        } finally {
            @fclose($fh);
        }
        return $response;
    }
}
