<?php

namespace Dvsa\Olcs\Api\Service\ConvertToPdf;

interface ConvertHtmlToPdfInterface
{
    /**
     * Convert HTML content to a PDF
     *
     * @param string $htmlContent  HTML content to convert
     * @param string $destination  Destination file path for the PDF
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RestResponseException
     */
    public function convertHtml(string $htmlContent, string $destination): void;
}
