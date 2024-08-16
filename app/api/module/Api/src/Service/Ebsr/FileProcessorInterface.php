<?php

namespace Dvsa\Olcs\Api\Service\Ebsr;

/**
 * Interface FileProcessorInterface
 * @package Dvsa\Olcs\Api\Service\Ebsr
 */
interface FileProcessorInterface
{
    /**
     * fetches xml file from the document store
     *
     * @param $identifier
     * @return string
     */
    public function fetchXmlFileNameFromDocumentStore($identifier);

    /**
     * Sets the sub directory path
     *
     * @param string $subDirPath
     * @return string
     */
    public function setSubDirPath($subDirPath);
}
