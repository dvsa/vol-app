<?php

/**
 * Create Office Copy / Licence Version
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch as GenerateBatchCommand;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Office Copy / Licence Version
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateOfficeCopy extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';
    protected $extraRepos = ['Licence'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        $licenceId = $command->getLicence();

        $this->validateOfficeCopy($licenceId);

        $data = [
            'status' => $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_ACTIVE),
            'specifiedDate' => new DateTime('now')
        ];

        $licence = $this->getRepo('Licence')->fetchById($licenceId);
        $data['serialNoPrefix'] = $licence->getSerialNoPrefixFromTrafficArea();
        $data['licence'] = $this->getRepo()->getReference(LicenceEntity::class, $licenceId);
        $data['issueNo'] = 0;

        $communityLic = $this->createCommunityLicObject($data);
        $this->getRepo()->save($communityLic);
        $result->addId('communityLic' . $communityLic->getId(), $communityLic->getId());
        $result->addMessage('Office copy created successfully');

        $sideEffects = $this->determineSideEffects($licenceId, $communityLic->getId());
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

    private function determineSideEffects($licenceId, $communityLicenceId)
    {
        $sideEffects = [];
        $sideEffects[] = $this->createGenerateBatchCommand($licenceId, [$communityLicenceId]);

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

    private function validateOfficeCopy($licenceId)
    {
        if ($this->getRepo()->fetchOfficeCopy($licenceId)) {
            throw new ValidationException(
                [
                    'officeCopy' => [
                        CommunityLicEntity::ERROR_OFFICE_COPY_EXISTS => 'Office copy already exists'
                    ]
                ]
            );
        }
    }
}
