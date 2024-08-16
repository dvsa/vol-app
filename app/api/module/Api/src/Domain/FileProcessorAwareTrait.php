<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Ebsr\FileProcessorInterface as FileProcessor;

/**
 * FileProcessor Aware Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait FileProcessorAwareTrait
{
    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    public function setFileProcessor(FileProcessor $fileProcessor)
    {
        $this->fileProcessor = $fileProcessor;
    }

    /**
     * @return FileProcessor
     */
    public function getFileProcessor()
    {
        return $this->fileProcessor;
    }
}
