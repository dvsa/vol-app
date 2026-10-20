<?php

namespace Dvsa\Olcs\DocumentShare\Service;

use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Laminas\Http\Response;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
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
     * @param FilesystemOperator $filesystem File System
     */
    public function __construct(
        protected FilesystemOperator $filesystem,
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
        } catch (UnableToReadFile) {
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
            $this->filesystem->delete($path);
            $response->setStatusCode(200);
        } catch (UnableToReadFile) {
            $response->setStatusCode(404);
        } catch (UnableToDeleteFile) {
            $response->setStatusCode(500);
        } catch (FilesystemException) {
            $response->setStatusCode(500);
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
        $fh = null;
        try {
            $fh = fopen($file->getResource(), 'rb');

            if ($fh === false) {
                $this->logger->error('Failed to open file for reading', ['file' => $file->getResource(), 'path' => $path]);

                $response->setResponse(false);
            } else {
                $this->filesystem->writeStream($path, $fh);
                $response->setResponse(true);
            }
        } catch (UnableToWriteFile) {
            $response->setResponse(false);
        } finally {
            if (is_resource($fh)) {
                fclose($fh);
            }
        }
        return $response;
    }

    /**
     * Update (overwrite) an existing file on remote storage
     *
     * @param string $path File Path on storage
     * @param File   $file File
     *
     * @return WebDavResponse
     * @throws \Exception
     */
    #[\Override]
    public function update($path, File $file)
    {
        $response = new WebDavResponse();
        $fh = null;
        try {
            $fh = fopen($file->getResource(), 'rb');

            if ($fh === false) {
                $this->logger->error('Failed to open file for reading', ['file' => $file->getResource(), 'path' => $path]);
                $response->setResponse(false);
            } else {
                $this->filesystem->writeStream($path, $fh);
                $response->setResponse(true);
            }
        } catch (UnableToReadFile) {
            $this->logger->error('File not found for update', ['path' => $path]);
            $response->setResponse(false);
        } finally {
            if (is_resource($fh)) {
                fclose($fh);
            }
        }
        return $response;
    }
}
