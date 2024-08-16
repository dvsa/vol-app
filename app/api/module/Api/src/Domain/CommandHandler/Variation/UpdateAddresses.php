<?php

/**
 * Variation Update Addresses
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command\Licence\SaveAddresses;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Variation Update Addresses
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class UpdateAddresses extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $licence = $application->getLicence();

        $params = $command->getArrayCopy();
        $params['id'] = $licence->getId();

        $result = $this->handleSideEffect(SaveAddresses::create($params));

        $result->merge(
            $this->handleSideEffect(
                UpdateApplicationCompletionCommand::create(
                    [
                        'id' => $application->getId(),
                        'section' => 'addresses',
                        'data' => [
                            'hasChanged' => $result->getFlag('isDirty')
                        ]
                    ]
                )
            )
        );

        // @NOTE: duped with Licence\UpdateAddresses
        if ($result->getFlag('isDirty') && $this->isGranted(Permission::SELFSERVE_USER)) {
            $taskParams = [
                'category' => Category::CATEGORY_APPLICATION,
                'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_ADDRESS_CHANGE_DIGITAL,
                'description' => 'Address Change',
                'licence' => $licence->getId(),
                'actionDate' => (new DateTime())->format('Y-m-d H:i:s'),
            ];

            $result->merge($this->handleSideEffect(CreateTask::create($taskParams)));
        }

        $result->setFlag('hasChanged', $result->getFlag('isDirty'));

        return $result;
    }
}
