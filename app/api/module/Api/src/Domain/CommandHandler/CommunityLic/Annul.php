<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCommand;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Void community licences
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Annul extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';

    protected $extraRepos = ['Licence'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommunityLic\Annul $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getCommunityLicenceIds();
        $licenceId = $command->getLicence();

        if ($command->getCheckOfficeCopy()) {
            $this->validateLicences($ids, $licenceId);
        }
        $licences = $this->getRepo()->fetchLicencesByIds($ids);

        $result = new Result();
        /** @var CommunityLicEntity $communityLicence */
        foreach ($licences as $communityLicence) {
            $id = $communityLicence->getId();

            $communityLicence->changeStatusAndExpiryDate(
                $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_ANNUL),
                new DateTime('now')
            );

            $this->getRepo()->save($communityLicence);
            $result->addMessage("Community Licence {$id} annulled");
            $result->addId('communityLic' . $id, $id);
        }

        $updateTotalCommunityLicences =  UpdateTotalCommunityLicencesCommand::create(['id' => $licenceId]);
        $updateResult = $this->handleSideEffect($updateTotalCommunityLicences);
        $result->merge($updateResult);

        if (method_exists($command, 'getApplication') && $command->getApplication()) {
            $result->merge(
                $this->handleSideEffect(
                    UpdateApplicationCompletion::create(
                        [
                            'id' => $command->getApplication(),
                            'section' => 'communityLicences'
                        ]
                    )
                )
            );
        }

        return $result;
    }

    /**
     * Validate by ids to community licences from licence
     *
     * @param array $ids       Identitifiers
     * @param int   $licenceId Licence Id
     *
     * @return void
     * @throws ValidationException
     */
    protected function validateLicences($ids, $licenceId)
    {
        /** @var \Dvsa\Olcs\Api\Entity\Licence\Licence $licence */
        $licence = $this->getRepo('Licence')->fetchById($licenceId);

        if ($licence->hasCommunityLicenceOfficeCopy($ids)) {
            $validLicences = $this->getRepo()->fetchValidLicences($licenceId);

            /** @var CommunityLicEntity $validLicence */
            foreach ($validLicences as $validLicence) {
                if (!in_array($validLicence->getId(), $ids)) {
                    throw new ValidationException(
                        [
                            'communityLicence' => [
                                CommunityLicEntity::ERROR_CANT_ANNUL =>
                                    'You cannot annul the office copy without annulling all the licences'
                            ]
                        ]
                    );
                }
            }
        }
    }
}
