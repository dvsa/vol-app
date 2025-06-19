<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;

/**
 * EditorJs Converter Aware Interface
 */
interface EditorJsConverterAwareInterface
{
    /**
     * Set ConverterService
     *
     * @param ConverterService $converterService
     */
    public function setConverterService(ConverterService $converterService): void;

    /**
     * Get ConverterService
     *
     * @return ConverterService
     */
    public function getConverterService(): ConverterService;
}