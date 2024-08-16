<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\SystemParameter;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update a SystemParameter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface, CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'SystemParameter';

    /**
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        /* @var $systemParameter \Dvsa\Olcs\Api\Entity\System\SystemParameter */
        $systemParameter = $this->getRepo()->fetchUsingId($command);
        $systemParameter->setParamValue($command->getParamValue());
        /*
         * this CommandHandler could be called from 2 different places, one command (Command/SystremParameter/Update)
         * doesn't pass the description field, another one (TransferCommand/SystemParameter/Update) pass the description
         * field so we need to check if we have such a method in command before updating
         */
        if (method_exists($command, 'getDescription')) {
            $systemParameter->setDescription($command->getDescription());
        }
        $this->getRepo()->save($systemParameter);

        $this->clearSystemParamCache($command->getId());
        $this->result->addMessage("SystemParameter '{$command->getId()}' updated");
        return $this->result;
    }
}
