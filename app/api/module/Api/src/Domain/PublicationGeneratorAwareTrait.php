<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Publication\PublicationGenerator;

/**
 * PublicationGeneratorAwareTrait
 */
trait PublicationGeneratorAwareTrait
{
    /**
     * @var PublicationGenerator
     */
    protected $publicationGenerator;

    public function setPublicationGenerator(PublicationGenerator $service)
    {
        $this->publicationGenerator = $service;
    }

    /**
     * @return PublicationGenerator
     */
    public function getPublicationGenerator()
    {
        return $this->publicationGenerator;
    }
}
