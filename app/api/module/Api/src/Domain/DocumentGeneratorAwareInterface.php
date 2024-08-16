<?php

namespace Dvsa\Olcs\Api\Domain;

use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Service\Document\DocumentGenerator;

/**
 * DocumentGeneratorAwareInterface
 */
interface DocumentGeneratorAwareInterface
{
    public function setDocumentGenerator(DocumentGenerator $service);

    /**
     * @return DocumentGenerator
     */
    public function getDocumentGenerator();
}
