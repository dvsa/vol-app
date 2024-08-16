<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\Publication\Publication;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Create Publication Trait
 */
trait CreatePublicationTrait
{
    /**
     * @param int $pubSection
     * @return PublicationSectionEntity
     * @throws RuntimeException
     */
    public function getPublicationSection($pubSection)
    {
        return $this->getRepo()->getReference(PublicationSectionEntity::class, $pubSection);
    }

    /**
     * @param int $trafficAreaId
     * @param string $pubType
     * @return Publication
     * @throws RuntimeException
     */
    public function getPublication($trafficAreaId, $pubType)
    {
        return $this->getRepo('Publication')->fetchLatestForTrafficAreaAndType($trafficAreaId, $pubType);
    }

    /**
     * Check if we have an existing publication link, if not return a new one
     *
     * @throws RuntimeException
     * @return PublicationLinkEntity
     */
    public function getPublicationLink(QueryInterface $query)
    {
        $previousPublication = $this->getRepo()->fetchSingleUnpublished($query);

        if (!empty($previousPublication)) {
            return $previousPublication;
        }

        return new PublicationLinkEntity();
    }

    /**
     * @param $publicationConfig
     * @param $existingContext
     * @return Result
     * @throws RuntimeException
     * @throws \Exception
     */
    private function createPublication(
        $publicationConfig,
        PublicationLinkEntity $publicationLink,
        $existingContext
    ) {
        $publicationLink = $this->getPublicationGenerator()->createPublication(
            $publicationConfig,
            $publicationLink,
            $existingContext
        );

        $this->getRepo()->save($publicationLink);

        $result = new Result();
        $result->addId('publicationLink', $publicationLink->getId());

        return $result;
    }
}
