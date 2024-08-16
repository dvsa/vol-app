<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\Olcs\Api\Entity\CancelableInterface;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Abstract handler to cancel an application
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AbstractCancelApplicationHandler extends AbstractCommandHandler
{
    protected $repoServiceName = 'changeMe';
    protected $cancelStatus = 'changeMe';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var RepositoryInterface  $repo
         * @var CancelableInterface $application
         */
        $repo = $this->getRepo();
        $application = $repo->fetchUsingId($command);
        $newStatus = $this->refData($this->cancelStatus);
        $application->cancel($newStatus);

        $repo->save($application);

        $outstandingFees = $application->getOutstandingFees();

        /** @var Fee $fee */
        foreach ($outstandingFees as $fee) {
            $this->result->merge($this->handleSideEffect(CancelFee::create(['id' => $fee->getId()])));
        }

        $this->result->addId($this->repoServiceName, $application->getId());
        $this->result->addMessage($this->repoServiceName . ' cancelled');

        return $this->result;
    }
}
