<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Tm\UpdateNysiisName as UpdateNysiisNameCmd;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Service\Nysiis\NysiisRestClient;
use Dvsa\Olcs\Api\Domain\Exception\NysiisException;
use Psr\Container\ContainerInterface;

/**
 * Queue request to update TM name with Nysiis values
 */
final class UpdateNysiisName extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'TransportManager';

    /**
     * Client to connect to Nysiis servers
     *
     * @var NysiisRestClient
     */
    private $nysiisClient;

    /**
     * Command to queue a request to update TM with Nysiis data
     *
     * @param CommandInterface|UpdateNysiisNameCmd $command command to update nysiis name
     *
     * @return Result
     * @throws NysiisException
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->result->addMessage('TM NYSIIS currently disabled');
        return $this->result;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->nysiisClient = $container->get(NysiisRestClient::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
