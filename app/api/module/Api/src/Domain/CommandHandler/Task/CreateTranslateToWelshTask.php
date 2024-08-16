<?php

/**
 * Create Translate To Welsh Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Task;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Translate To Welsh Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateTranslateToWelshTask extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public function handleCommand(CommandInterface $command)
    {
        $data = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_LICENSING_GENERAL_TASK,
            'description' => 'Welsh translation required: ' . $command->getDescription(),
            'urgent' => 'Y',
            'licence' => $command->getLicence(),
        ];

        return $this->handleSideEffect(CreateTaskCmd::create($data));
    }
}
