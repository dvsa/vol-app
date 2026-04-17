<?php

namespace Dvsa\Olcs\Api\Service\ConvertToPdf;

interface ConvertToPdfInterface
{
    /**
     * Convert a document to a PDF
     *
     * @param string $fileName    File to be converted
     * @param string $destination Destination file, the PDF file name
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RestResponseException
     */
    public function convert($fileName, $destination);
}
