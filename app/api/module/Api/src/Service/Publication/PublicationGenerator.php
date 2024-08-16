<?php

namespace Dvsa\Olcs\Api\Service\Publication;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;

/**
 * Class PublicationGenerator
 * @package Dvsa\Olcs\Api\Service\Publication
 */
class PublicationGenerator
{
    public function __construct(private $publicationConfig, private readonly ServiceLocatorInterface $publicationContextManager, private readonly ServiceLocatorInterface $publicationProcessManager)
    {
    }

    public function createPublication($publicationConfigKey, $publication, $context)
    {
        if (!isset($this->publicationConfig[$publicationConfigKey])) {
            throw new \Exception('Invalid publication config');
        }

        $contextObject = $this->fetchContext($this->publicationConfig[$publicationConfigKey], $publication, $context);

        return $this->processPublication($this->publicationConfig[$publicationConfigKey], $publication, $contextObject);
    }

    private function fetchContext($config, PublicationLinkEntity $publication, $existingContext)
    {
        $context = new \ArrayObject($existingContext);

        if (isset($config['context'])) {
            foreach ($config['context'] as $contextClass) {
                $this->publicationContextManager->get($contextClass)->provide($publication, $context);
            }
        }

        $contextArray = $context->getArrayCopy();

        return new ImmutableArrayObject($contextArray);
    }

    private function processPublication($config, PublicationLinkEntity $publication, $context)
    {
        if (!isset($config['process'])) {
            throw new \Exception('No publication processors specified');
        }

        foreach ($config['process'] as $process) {
            $this->publicationProcessManager->get($process)->process($publication, $context);
        }

        return $publication;
    }
}
