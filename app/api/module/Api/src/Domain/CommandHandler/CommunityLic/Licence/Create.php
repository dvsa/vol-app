<?php

/**
 * Create Community Licences / Licence Version
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch as GenerateBatchCommand;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCommand;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\Licence\CreateOfficeCopy as CreateOfficeCopyCommand;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Create Community Licences / Licence Version
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';

    protected $extraRepos = ['Licence'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        $licenceId = $command->getLicence();
        $totalLicences = $command->getTotalLicences();

        $data = [
            'status' => $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_ACTIVE),
            'specifiedDate' => new DateTime('now')
        ];

        $validLicences = $this->getRepo()
            ->fetchValidLicences($command->getLicence());
        $validLicencesCount = count($validLicences);

        $this->validateLicencesCount($licenceId, $validLicencesCount, $totalLicences);

        $startIssueNo = $validLicencesCount ?
            $validLicences[$validLicencesCount - 1]->getIssueNo() + 1 : 1;

        $licence = $this->getRepo('Licence')->fetchById($licenceId);
        $data['serialNoPrefix'] = $licence->getSerialNoPrefixFromTrafficArea();
        $data['licence'] = $this->getRepo()->getReference(LicenceEntity::class, $licenceId);

        $ids = [];
        for ($i = 1; $i <= $totalLicences; $i++) {
            $data['issueNo'] = $startIssueNo++;

            $communityLic = $this->createCommunityLicObject($data);
            $this->getRepo()->save($communityLic);
            $result->addId('communityLic' . $communityLic->getId(), $communityLic->getId());
            $result->addMessage('Community licence created successfully');
            $ids[] = $communityLic->getId();
        }

        $sideEffects = $this->determineSideEffects($licenceId, $ids);
        foreach ($sideEffects as $sideEffect) {
            $result->merge($this->handleSideEffect($sideEffect));
        }

        return $result;
    }

    /**
     * @param array $data
     * @return CommunityLicEntity
     */
    private function createCommunityLicObject($data)
    {
        $communityLic = new CommunityLicEntity();
        $communityLic->updateCommunityLic($data);
        return $communityLic;
    }

    private function determineSideEffects($licenceId, $communityLicenceIds)
    {
        $sideEffects = [];

        $sideEffects[] = $this->createGenerateBatchCommand($licenceId, $communityLicenceIds);

        $sideEffects[] = $this->createUpdateTotalCommunityLicencesCommand($licenceId);
        if (!$this->getRepo()->fetchOfficeCopy($licenceId)) {
            $sideEffects[] = $this->createCreateOfficeCopyCommand($licenceId);
        }

        return $sideEffects;
    }

    private function createGenerateBatchCommand($licenceId, $communityLicenceIds)
    {
        return GenerateBatchCommand::create(
            [
                'isBatchReprint' => false,
                'licence' => $licenceId,
                'communityLicenceIds' => $communityLicenceIds
            ]
        );
    }

    private function createUpdateTotalCommunityLicencesCommand($licenceId)
    {
        return UpdateTotalCommunityLicencesCommand::create(
            [
                'id' => $licenceId
            ]
        );
    }

    private function createCreateOfficeCopyCommand($licenceId)
    {
        return CreateOfficeCopyCommand::create(
            [
                'licence' => $licenceId,
            ]
        );
    }

    private function validateLicencesCount($licenceId, $validLicences, $totalLicences)
    {
        $licence = $this->getRepo('Licence')->fetchById($licenceId);
        $totAuthVehicles = (int) $licence->getTotAuthVehicles();
        if (($totalLicences +  $validLicences) > $totAuthVehicles) {
            throw new ValidationException(
                [
                    'communityLicence' => [
                        CommunityLicEntity::ERROR_OFFICE_COPY_EXISTS =>
                            'The number of licences exceeds the total vehicle authority'
                    ]
                ]
            );
        }
    }
}
