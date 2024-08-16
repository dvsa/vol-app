<?php

/**
 * Grant Condition Undertaking
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;

/**
 * Grant Condition Undertaking
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantConditionUndertaking extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ConditionUndertaking'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $conditionsUndertakings = $application->getConditionUndertakings();

        if ($conditionsUndertakings->count() < 1) {
            return $result;
        }

        $add = $update = $delete = 0;

        /** @var ConditionUndertaking $conditionsUndertaking */
        foreach ($conditionsUndertakings as $conditionsUndertaking) {
            switch ($conditionsUndertaking->getAction()) {
                case 'A':
                    $this->createConditionUndertaking($conditionsUndertaking, $application->getLicence());
                    $add++;
                    break;
                case 'U':
                    $this->updateConditionUndertaking($conditionsUndertaking);
                    $update++;
                    break;
                case 'D':
                    $this->deleteConditionUndertaking($conditionsUndertaking);
                    $delete++;
                    break;
            }
        }

        $result->addMessage($add . ' licence condition undertaking record(s) created');
        $result->addMessage($update . ' licence condition undertaking record(s) updated');
        $result->addMessage($delete . ' licence condition undertaking record(s) removed');

        return $result;
    }

    /**
     * Create a licence CU record from the ADD delta
     */
    protected function createConditionUndertaking(ConditionUndertaking $deltaCu, Licence $licence)
    {
        $licenceCu = new ConditionUndertaking($deltaCu->getConditionType(), $deltaCu->getIsFulfilled(), 'N');
        $licenceCu->setLicence($licence);

        $this->copyDeltaData($deltaCu, $licenceCu);

        $this->getRepo('ConditionUndertaking')->save($licenceCu);
    }

    /**
     * Update a licence CU record from the UPDATE delta
     */
    protected function updateConditionUndertaking(ConditionUndertaking $deltaCu)
    {
        $licenceCu = $deltaCu->getLicConditionVariation();

        $this->copyDeltaData($deltaCu, $licenceCu);

        $licenceCu->setConditionType($deltaCu->getConditionType());
        $licenceCu->setIsFulfilled($deltaCu->getIsFulfilled());
        $licenceCu->setIsDraft('N');
        $licenceCu->setAction(null);

        $this->getRepo('ConditionUndertaking')->save($licenceCu);
    }

    /**
     * Delete condition undertaking during granting
     * @NOTE The AC specifies we need to set the approvalUser, so we need to make an UPDATE call before DELETE
     * which seems odd but as we are soft deleting, this gives us an audit trail
     */
    protected function deleteConditionUndertaking(ConditionUndertaking $deltaCu)
    {
        $licenceCu = $deltaCu->getLicConditionVariation();

        $licenceCu->setApprovalUser($this->getCurrentUser());

        $this->getRepo('ConditionUndertaking')->save($licenceCu);
        $this->getRepo('ConditionUndertaking')->delete($licenceCu);
    }

    protected function copyDeltaData(ConditionUndertaking $deltaCu, ConditionUndertaking $licenceCu)
    {
        $licenceCu->setApprovalUser($this->getCurrentUser());
        $licenceCu->setAddedVia($deltaCu->getAddedVia());
        $licenceCu->setAttachedTo($deltaCu->getAttachedTo());
        $licenceCu->setCase($deltaCu->getCase());
        $licenceCu->setOperatingCentre($deltaCu->getOperatingCentre());
        $licenceCu->setNotes($deltaCu->getNotes());
        $licenceCu->setConditionCategory($deltaCu->getConditionCategory());
    }
}
