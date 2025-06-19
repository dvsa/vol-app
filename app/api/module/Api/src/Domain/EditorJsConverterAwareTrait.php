<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;

/**
 * EditorJs Converter Aware Trait
 */
trait EditorJsConverterAwareTrait
{
    /**
     * @var ConverterService
     */
    protected ConverterService $converterService;

    /**
     * Set ConverterService
     *
     * @param ConverterService $converterService
     */
    public function setConverterService(ConverterService $converterService): void
    {
        $this->converterService = $converterService;
    }

    /**
     * Get ConverterService
     *
     * @return ConverterService
     */
    public function getConverterService(): ConverterService
    {
        return $this->converterService;
    }
}