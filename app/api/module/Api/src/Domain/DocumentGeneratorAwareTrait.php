<?php

namespace Dvsa\Olcs\Api\Domain;

use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Service\Document\DocumentGenerator;

/**
 * DocumentGeneratorAwareTrait
 */
trait DocumentGeneratorAwareTrait
{
    /**
     * @var DocumentGenerator
     */
    protected $documentGenerator;

    public function setDocumentGenerator(DocumentGenerator $service)
    {
        $this->documentGenerator = $service;
    }

    /**
     * @return DocumentGenerator
     */
    public function getDocumentGenerator()
    {
        return $this->documentGenerator;
    }
}
