<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\SystemParameter;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;

final class Create extends AbstractCommandHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'SystemParameter';

    /**
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $systemParameter = new SystemParameter();
        $systemParameter->setId($command->getId());
        $systemParameter->setParamValue($command->getParamValue());
        $systemParameter->setDescription($command->getDescription());

        $this->clearSystemParamListCache();
        $this->getRepo()->save($systemParameter);

        $this->result->addId('systemParameter', $systemParameter->getId());
        $this->result->addMessage("System Parameter '{$systemParameter->getId()}' created");
        return $this->result;
    }
}
