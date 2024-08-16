<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\ValidatingReprintCaller as ValidatingReprintCallerCmd;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateCoverLetter as GenerateCoverLetterCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Reprint as ReprintCmd;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Exception;

/**
 * Call reprint command with validation
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class ValidatingReprintCaller extends AbstractCommandHandler
{
    protected $repoServiceName = 'CommunityLic';

    /**
     * Handle command
     *
     * @param ValidatingReprintCallerCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $licenceId = $command->getLicence();
        $userId = $command->getUser();
        $communityLicences = $command->getCommunityLicences();

        $validCommunityLicenceIds = [];

        foreach ($communityLicences as $communityLicence) {
            $communityLicenceId = $communityLicence['communityLicenceId'];
            $communityLicenceIssueNo = $communityLicence['communityLicenceIssueNo'];

            if ($this->validate($communityLicenceId, $communityLicenceIssueNo, $licenceId)) {
                $validCommunityLicenceIds[] = $communityLicenceId;
            }
        }

        if (count($validCommunityLicenceIds) > 0) {
            try {
                $this->handleSideEffect(
                    ReprintCmd::create(
                        [
                            'isBatchReprint' => true,
                            'user' => $userId,
                            'licence' => $licenceId,
                            'communityLicenceIds' => $validCommunityLicenceIds
                        ]
                    )
                );
            } catch (Exception $e) {
                $this->result->addMessage(
                    sprintf(
                        'Error calling Reprint command with licence id %s and community licence ids %s: %s',
                        $licenceId,
                        implode(', ', $validCommunityLicenceIds),
                        $e->getMessage()
                    )
                );
            }
        }

        try {
            $this->handleSideEffect(
                GenerateCoverLetterCmd::create(
                    [
                        'user' => $userId,
                        'licence' => $licenceId,
                    ]
                )
            );
        } catch (Exception $e) {
            $this->result->addMessage(
                sprintf(
                    'Error calling GenerateCoverLetter command with licence id %s: %s',
                    $licenceId,
                    $e->getMessage()
                )
            );
        }

        return $this->result;
    }

    /**
     * Checks if the supplied combination of values are valid for reprinting
     *
     * @param int $communityLicenceId
     * @param int $communityLicenceIssueNo
     * @param int $licenceId
     *
     * @return bool
     */
    private function validate($communityLicenceId, $communityLicenceIssueNo, $licenceId)
    {
        try {
            $communityLicence = $this->getRepo()->fetchById($communityLicenceId);
        } catch (NotFoundException) {
            $this->result->addMessage(
                sprintf(
                    'No community licence exists with id %s',
                    $communityLicenceId
                )
            );
            return false;
        }

        if ($communityLicence->getIssueNo() != $communityLicenceIssueNo) {
            $this->result->addMessage(
                sprintf(
                    'Community licence with id %s exists but has an issue number of %s instead of the expected %s',
                    $communityLicenceId,
                    $communityLicence->getIssueNo(),
                    $communityLicenceIssueNo
                )
            );
            return false;
        }

        if (!$communityLicence->isActive()) {
            $this->result->addMessage(
                sprintf(
                    'Community licence with id %s exists but is not active',
                    $communityLicenceId
                )
            );
            return false;
        }

        $associatedLicence = $communityLicence->getLicence();
        $associatedLicenceId = $associatedLicence->getId();

        if ($associatedLicenceId != $licenceId) {
            $this->result->addMessage(
                sprintf(
                    'Licence id %s associated with community licence id %s does not match expected value of %s',
                    $associatedLicenceId,
                    $communityLicenceId,
                    $licenceId
                )
            );
            return false;
        }

        if (!$associatedLicence->hasStatusRequiredForCommunityLicenceReprint()) {
            $this->result->addMessage(
                sprintf(
                    'Licence id %s associated with community licence id %s does not have the correct status (currently %s)',
                    $associatedLicenceId,
                    $communityLicenceId,
                    $associatedLicence->getStatus()->getId()
                )
            );
            return false;
        }

        return true;
    }
}
