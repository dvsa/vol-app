<?php

/**
 * Create Fee
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as FeeCreateFee;

/**
 * Create Fee
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateFee extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['FeeType'];

    /**
     * @param Cmd $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        $result->merge($this->createFee($command));

        return $result;
    }

    /**
     * Create the createFee command
     *
     *
     * @return Result
     */
    private function createFee(Cmd $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $trafficArea = null;
        if ($application->getNiFlag() === 'Y') {
            $trafficArea = $this->getRepo()
                ->getReference(TrafficArea::class, TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);
        }

        $date = $application->getReceivedDate();
        if ($date === null) {
            $date = $application->getCreatedOn();
        }

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        $optional = $command->getOptional();

        $feeType = $this->getRepo('FeeType')->fetchLatest(
            $this->getRepo()->getRefdataReference($command->getFeeTypeFeeType()),
            $application->getGoodsOrPsv(),
            $application->getLicenceType(),
            $date,
            $trafficArea,
            $optional
        );

        if ($feeType === null) {
            $this->result->addMessage([Application::ERROR_FEE_NOT_CREATED => 'The interim fee is not created']);
            return $this->result;
        }

        $data = [
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId(),
            'invoicedDate' => date('Y-m-d'),
            'description' => $feeType->getDescription() . ' for application ' . $application->getId(),
            'feeType' => $feeType->getId(),
            'amount' => $feeType->getFixedValue() == 0 ? $feeType->getFiveYearValue() : $feeType->getFixedValue()
        ];

        if ($command->getTask() !== null) {
            $data['task'] = $command->getTask();
        }

        return $this->handleSideEffect(FeeCreateFee::create($data));
    }
}
