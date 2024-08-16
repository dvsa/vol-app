<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedLicence;

/**
 * Licence Publish
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Licence extends AbstractCommandHandler implements TransactionedInterface, PublicationGeneratorAwareInterface
{
    use PublicationGeneratorAwareTrait;
    use CreatePublicationTrait;

    protected $repoServiceName = 'PublicationLink';

    protected $extraRepos = ['Licence', 'Publication'];

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $licence LicenceEntity */
        $licence = $this->getRepo('Licence')->fetchUsingId($command);
        $pubType = $licence->isGoods() ? 'A&D' : 'N&P';
        $publication = $this->getPublication($licence->getTrafficArea()->getId(), $pubType);

        $pubSection = $command->getPublicationSection();
        if (empty($pubSection)) {
            $pubSection = $this->getPublicationSectionId($licence);
        }
        $publicationSection = $this->getPublicationSection($pubSection);

        $unpublishedQuery = $this->getUnpublishedLicenceQuery(
            $publication->getId(),
            $licence->getId(),
            $pubSection
        );
        $publicationLink = $this->getPublicationLink($unpublishedQuery);

        if ($publicationLink->getId() === null) {
            $publicationLink->createLicence(
                $licence,
                $publication,
                $publicationSection,
                $licence->getTrafficArea()
            );
        }

        return $this->createPublication('LicencePublication', $publicationLink, []);
    }

    /**
     * Auto detect which section we should be publishing to
     *
     *
     * @return int publicationSection ID
     * @throws ForbiddenException
     */
    private function getPublicationSectionId(LicenceEntity $licence)
    {
        $map = [
            LicenceEntity::LICENCE_STATUS_REVOKED => PublicationSectionEntity::LIC_REVOKED_SECTION,
            LicenceEntity::LICENCE_STATUS_SURRENDERED => PublicationSectionEntity::LIC_SURRENDERED_SECTION,
            LicenceEntity::LICENCE_STATUS_TERMINATED => PublicationSectionEntity::LIC_TERMINATED_SECTION,
            LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT => PublicationSectionEntity::LIC_CNS_SECTION
        ];

        if (isset($map[$licence->getStatus()->getId()])) {
            return $map[$licence->getStatus()->getId()];
        }

        throw new \RuntimeException('Could not match to a publication section');
    }

    /**
     * @param int $publication
     * @param int $licence
     * @param int $pubSection
     *
     * @return UnpublishedLicence
     */
    private function getUnpublishedLicenceQuery($publication, $licence, $pubSection)
    {
        $data =  [
            'publication' => $publication,
            'licence' => $licence,
            'publicationSection' => $pubSection
        ];

        return UnpublishedLicence::create($data);
    }
}
