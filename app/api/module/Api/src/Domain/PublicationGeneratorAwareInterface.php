<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Publication\PublicationGenerator;

/**
 * PublicationGeneratorAwareInterface
 */
interface PublicationGeneratorAwareInterface
{
    public function setPublicationGenerator(PublicationGenerator $service);

    /**
     * @return PublicationGenerator
     */
    public function getPublicationGenerator();
}
