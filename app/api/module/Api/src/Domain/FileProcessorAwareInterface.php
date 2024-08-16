<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Ebsr\FileProcessorInterface as FileProcessor;

/**
 * FileProcessor Aware Interface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface FileProcessorAwareInterface
{
    public function setFileProcessor(FileProcessor $fileProcessor);

    /**
     * @return FileProcessor
     */
    public function getFileProcessor();
}
