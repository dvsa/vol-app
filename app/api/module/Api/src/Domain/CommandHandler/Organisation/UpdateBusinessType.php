<?php

/**
 * Update Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Organisation;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Organisation\ChangeBusinessType as ChangeBusinessTypeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\Organisation\UpdateBusinessType as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Update Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateBusinessType extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface, CacheAwareInterface
{
    use AuthAwareTrait;
    use CacheAwareTrait;

    public const string ERROR_NO_TYPE = 'ORG-BT-1';
    public const string ERROR_CANT_CHANGE_TYPE = 'ORG-BT-2';

    protected $repoServiceName = 'Organisation';

    protected $oldType;

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Organisation $organisation */
        $organisation = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $this->oldType = $organisation->getType()->getId();

        $canChangeBusinessType = $this->canChangeBusinessType($command, $organisation);

        // If we can change business type...
        if ($canChangeBusinessType) {
            // A) But we don't have a business type set
            if ($command->getBusinessType() === null) {
                throw new ValidationException([self::ERROR_NO_TYPE => 'Missing business type']);
            }

            // B) But we are not changing business type
            if (!$this->businessTypeWillChange($organisation, $command)) {
                $result->addMessage('Business type unchanged');
                $this->maybeUpdateApplicationCompletion($command, $result);
                return $result;
            }
        } else {
            // If we can't change business type...
            // A) But we are attempting to change it
            if ($this->businessTypeWillChange($organisation, $command)) {
                throw new ValidationException(
                    [self::ERROR_CANT_CHANGE_TYPE => 'Attempted to change business type when update is not allowed']
                );
            }

            // B) Otherwise...
            $result->addMessage('Can\'t update business type');

            $this->maybeUpdateApplicationCompletion($command, $result);

            return $result;
        }

        // Need to handle differently for internal "L/V" (SS and "A" just get updated here and now)
        if ($command->getApplication() === null && $this->isInternalUser()) {
            $data = [
                'id' => $organisation->getId(),
                'businessType' => $command->getBusinessType(),
                'confirm' => $command->getConfirm()
            ];

            $this->handleSideEffect(ChangeBusinessTypeCmd::create($data));
        } else {
            $organisation->setType($this->getRepo()->getRefdataReference($command->getBusinessType()));
            $this->getRepo()->save($organisation);
        }

        $result->addMessage('Business type updated');

        $this->maybeUpdateApplicationCompletion($command, $result);
        $this->clearOrganisationCaches($organisation);

        return $result;
    }

    private function maybeUpdateApplicationCompletion(Cmd $command, Result $result)
    {
        $appId = null;

        if ($command->getApplication() !== null) {
            $appId = $command->getApplication();
        } elseif ($command->getVariation() !== null) {
            $appId = $command->getVariation();
        }

        if ($appId !== null) {
            $result->merge(
                $this->handleSideEffect(
                    $this->createUpdateApplicationCompletionCommand($appId)
                )
            );
        }
    }

    private function businessTypeWillChange(Organisation $organisation, Cmd $command)
    {
        if ($command->getBusinessType() === null) {
            return false;
        }

        return ($organisation->getType() !== $this->getRepo()->getRefdataReference($command->getBusinessType()));
    }

    private function canChangeBusinessType(Cmd $command, Organisation $organisation)
    {
        if ($this->isGranted(Permission::INTERNAL_USER)) {
            return true;
        }

        if (($command->getLicence() !== null || $command->getVariation() !== null)) {
            return false;
        }

        if ($command->getApplication() !== null && $organisation->hasInforceLicences()) {
            return false;
        }

        return true;
    }

    private function createUpdateApplicationCompletionCommand($applicationId)
    {
        return UpdateApplicationCompletion::create(
            [
                'id' => $applicationId,
                'section' => 'businessType',
                'data' => [
                    'type' => $this->oldType
                ]
            ]
        );
    }
}
