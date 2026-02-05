<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\CreateTaxiPhv as Command;
use Psr\Container\ContainerInterface;

/**
 * Create TaxiPhv
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreateTaxiPhv extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    /**
     * @var \Dvsa\Olcs\Api\Domain\Service\TrafficAreaValidator
     */
    private $trafficAreaValidator;

    /**
     * @param Command $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $application = $this->getRepo()->fetchUsingId($command);

        if ($application->isNew()) {
            if (isset($command->getAddress()['postcode']) && !empty($command->getAddress()['postcode'])) {
                $result = $this->trafficAreaValidator->validateForSameTrafficAreasWithPostcode(
                    $application,
                    $command->getAddress()['postcode']
                );
                if (is_array($result)) {
                    throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException($result);
                }
            }
        }

        $result = new Result();
        $result->merge($this->createPrivateHireLicence($application, $command));
        $result->merge($this->updateApplicationCompletion($application));

        return $result;
    }

    /**
     * Create PrivateHireLicence entity
     *
     *
     * @return Result
     */
    private function createPrivateHireLicence(Application $application, Command $command)
    {
        $data = [
            'licence' => $application->getLicence()->getId(),
            'lva' => $command->getLva(),
            'privateHireLicenceNo' => $command->getPrivateHireLicenceNo(),
            'councilName' => $command->getCouncilName(),
            'address' => $command->getAddress(),
        ];

        return $this->handleSideEffect(\Dvsa\Olcs\Transfer\Command\PrivateHireLicence\Create::create($data));
    }

    /**
     * Update the ApplicationCompletion
     *
     *
     * @return Result
     */
    private function updateApplicationCompletion(Application $application)
    {
        $data = [
            'id' => $application->getId(),
            'section' => 'taxiPhv',
        ];

        return $this->handleSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::create($data)
        );
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->trafficAreaValidator = $container->get('TrafficAreaValidator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
