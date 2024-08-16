<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch as GenerateBatchCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCommand;

/**
 * Reprint community licences
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Reprint extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';
    protected $extraRepos = ['Licence', 'SystemParameter'];

    /**
     * Handle Command
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommunityLic\Reprint $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        //by default we make DB updates, unless it's a bulk reprint when we rely on system param
        $shouldMakeDbUpdates = true;
        $isBatchReprint = $command->getIsBatchReprint();

        if ($isBatchReprint) {
            /** @var SystemParameterRepo $sysParamRepo */
            $sysParamRepo = $this->getRepo('SystemParameter');

            //if param NOT disabled, make the DB updates
            $shouldMakeDbUpdates = !$sysParamRepo->fetchValue(SystemParameter::DISABLE_COM_LIC_BULK_REPRINT_DB);
        }

        $ids = $command->getCommunityLicenceIds();
        $licenceId = $command->getLicence();

        $issueNumbers = $this->getIssueNumbersByIds($ids);

        if ($shouldMakeDbUpdates) {
            $voidLicencesCommand = TransferCmd\CommunityLic\Annul::create(
                [
                    'licence' => $licenceId,
                    'communityLicenceIds' => $ids,
                    'checkOfficeCopy' => false
                ]
            );

            $this->result->merge($this->handleSideEffect($voidLicencesCommand));

            /**
             * @var LicenceRepo   $licenceRepo
             * @var LicenceEntity $licence
             */
            $licenceRepo = $this->getRepo('Licence');
            $licence = $licenceRepo->fetchById($licenceId);

            $data = [
                'status' => $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_ACTIVE),
                'specifiedDate' => new \DateTime('now'),
                'serialNoPrefix' => $licence->getSerialNoPrefixFromTrafficArea(),
                'licence' => $licence
            ];

            foreach ($issueNumbers as $issueNumber) {
                $data['issueNo'] = $issueNumber;
                $communityLic = $this->createCommunityLicObject($data);
                $this->getRepo()->save($communityLic);
                $this->result->addId('communityLic' . $communityLic->getId(), $communityLic->getId());
                $this->result->addMessage("The selected licence with issue number {$issueNumber} has been generated");
            }
        } else {
            $this->result->addMessage("Community licences reprinted without updating DB");
        }

        $generateBatchCmd = GenerateBatchCommand::create(
            [
                'isBatchReprint' => $command->getIsBatchReprint(),
                'licence' => $licenceId,
                'communityLicenceIds' => $ids,
                'identifier' => $command->getApplication(),
                'user' => $command->getUser(),
            ]
        );
        $this->result->merge($this->handleSideEffect($generateBatchCmd));

        if ($shouldMakeDbUpdates) {
            $this->result->merge(
                $this->handleSideEffect(UpdateTotalCommunityLicencesCommand::create(['id' => $licenceId]))
            );
        }

        return $this->result;
    }

    /**
     * Get issue numbers, and also validate licence is active
     *
     * @param array $ids Licences Ids
     *
     * @return array
     * @throws ValidationException
     */
    protected function getIssueNumbersByIds($ids)
    {
        $issueNumbers = [];

        /** @var CommunityLicRepo $communityLicRepo */
        $communityLicRepo = $this->getRepo('CommunityLic');
        $communityLicences = $communityLicRepo->fetchLicencesByIds($ids);

        /** @var CommunityLic $communityLicence */
        foreach ($communityLicences as $communityLicence) {
            if (!$communityLicence->isActive()) {
                throw new ValidationException(
                    [
                        'communityLicence' => [
                            CommunityLicEntity::ERROR_CANT_REPRINT =>
                                'You can only reprint \'Active\' community licences'
                        ]
                    ]
                );
            }

            $issueNumbers[] = $communityLicence->getIssueNo();
        }

        return $issueNumbers;
    }

    /**
     * Create a Community Licence entity with some initial data
     *
     * @param array $data Community Licence data
     *
     * @return CommunityLicEntity
     */
    private function createCommunityLicObject($data)
    {
        $communityLic = new CommunityLicEntity();
        $communityLic->updateCommunityLic($data);
        return $communityLic;
    }
}
