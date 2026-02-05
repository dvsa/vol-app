<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Application\UpdateTaxiPhv as Command;
use Psr\Container\ContainerInterface;

/**
 * UpdateTaxiPhv
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdateTaxiPhv extends AbstractCommandHandler implements TransactionedInterface
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
            $trafficAreaId = null;
            if ($command->getTrafficArea()) {
                $trafficAreaId = $command->getTrafficArea();
            } elseif ($application->getTrafficArea()) {
                $trafficAreaId = $application->getTrafficArea()->getId();
            }
            if ($trafficAreaId) {
                $message = $this->trafficAreaValidator->validateForSameTrafficAreas($application, $trafficAreaId);
                if ($message !== true) {
                    throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException($message);
                }
            }
        }

        $result = new Result();
        if ($command->getTrafficArea()) {
            $result->merge(
                $this->updateTrafficArea($application->getLicence(), $command->getTrafficArea())
            );
        }
        $result->merge($this->updateApplicationCompletion($application));

        return $result;
    }

    /**
     * Update the TrafficAre
     *
     * @param string $trafficAreaId traffic area ID
     * @return Result
     */
    private function updateTrafficArea(Licence $licence, $trafficAreaId)
    {
        $data = [
            'id' => $licence->getId(),
            'trafficArea' => $trafficAreaId,
        ];

        return $this->handleSideEffect(
            \Dvsa\Olcs\Transfer\Command\Licence\UpdateTrafficArea::create($data)
        );
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
