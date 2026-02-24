<?php

/**
 * Delete a SystemParameter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\SystemParameter;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete a SystemParameter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Delete extends AbstractDeleteCommandHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'SystemParameter';

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $this->clearSystemParamCache($command->getId());
        return parent::handleCommand($command);
    }
}
