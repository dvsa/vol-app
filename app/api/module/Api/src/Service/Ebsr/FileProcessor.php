<?php

namespace Dvsa\Olcs\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Filesystem\Filesystem;
use Dvsa\Olcs\Api\Service\File\FileUploaderInterface;
use Symfony\Component\Finder\Finder;
use Laminas\Filter\Decompress;
use Dvsa\Olcs\Api\Domain\Exception\EbsrPackException;
use Laminas\Filter\Exception\RuntimeException as LaminasFilterRuntimeException;

/**
 * Class FileProcessor
 * @package Dvsa\Olcs\Api\Service\Ebsr
 */
class FileProcessor implements FileProcessorInterface
{
    public const DECOMPRESS_ERROR_PREFIX = 'There was a problem with the pack file: ';
    /**
     * @var string
     */
    private $subDirPath = '';

    /**
     * FileProcessor constructor.
     *
     * @param FileUploaderInterface $fileUploader     file uploader
     * @param Filesystem            $fileSystem       symphony file system component
     * @param Decompress            $decompressFilter decompression filter
     * @param string                $tmpDir           the temporary directory
     */
    public function __construct(private readonly FileUploaderInterface $fileUploader, private readonly Filesystem $fileSystem, private readonly Decompress $decompressFilter, private $tmpDir)
    {
    }

    /**
     * Sets the sub directory path, allows outputting to different directories within /tmp and makes the file processor
     * a bit more reusable in future
     *
     * @param string $subDirPath the sub directory path
     *
     * @return void
     */
    public function setSubDirPath($subDirPath)
    {
        $this->subDirPath = $subDirPath;
    }

    /**
     * Returns the filename of extracted EBSR xml file
     *
     * @param string $identifier     document identifier
     *
     * @return string
     * @throws \RuntimeException
     * @throws EbsrPackException
     */
    public function fetchXmlFileNameFromDocumentStore($identifier): string
    {
        $targetDir = $this->tmpDir . $this->subDirPath;

        if (!$this->fileSystem->exists($targetDir)) {
            try {
                $this->fileSystem->mkdir($targetDir);
            } catch (\Exception $e) {
                throw new \RuntimeException('Unable to create the tmp directory: ' . $e->getMessage());
            }
        }

        $file = $this->fileUploader->download($identifier);

        $filePath = $this->fileSystem->createTmpFile($targetDir, 'ebsr');
        $this->fileSystem->dumpFile($filePath, $file->getContent());

        $tmpDir = $this->fileSystem->createTmpDir($targetDir, 'zip');
        $this->decompressFilter->setTarget($tmpDir);

        //attempt to decompress the zip file
        try {
            $this->decompressFilter->filter($filePath);
        } catch (LaminasFilterRuntimeException $e) {
            throw new EbsrPackException(self::DECOMPRESS_ERROR_PREFIX . $e->getMessage());
        }

        $finder = new Finder();
        $files = iterator_to_array($finder->files()->name('*.xml')->in($tmpDir));

        if (count($files) > 1) {
            throw new EbsrPackException('There is more than one XML file in the pack');
        } elseif (!count($files)) {
            throw new EbsrPackException('Could not find an XML file in the pack');
        }

        $xml = key($files);

        return $xml;
    }
}
