<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\CreateGrantFee as CreateGrantFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\RefundInterimTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask as CloseTexTaskCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\CloseFeeDueTask as CloseFeeDueTaskCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\CancelAllInterimFees as CancelAllInterimFeesCmd;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Grant Goods
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantGoods extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;
    use RefundInterimTrait;

    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command);

        $this->updateStatusAndDate($application, ApplicationEntity::APPLICATION_STATUS_GRANTED);
        $result->addMessage('Application status updated');

        $this->updateStatusAndDate($application->getLicence(), Licence::LICENCE_STATUS_GRANTED);
        $result->addMessage('Licence status updated');

        $this->getRepo()->save($application);

        // If Internal user grants Goods variation
        if ($application->isVariation() && $this->isInternalUser()) {
            $result->merge(
                $this->handleSideEffectAsSystemUser(CloseTexTaskCmd::create(['id' => $application->getId()]))
            );
            // close fee due tasks, createGrantFee will create a new fee due task
            $result->merge(
                $this->handleSideEffectAsSystemUser(CloseFeeDueTaskCmd::create(['id' => $application->getId()]))
            );
        }
        if ($this->isInternalUser()) {
            $result->merge(
                $this->handleSideEffectAsSystemUser(CancelAllInterimFeesCmd::create(['id' => $application->getId()]))
            );
        }

        $result->merge($this->createGrantFee($application));

        if ($application->getCurrentInterimStatus() === ApplicationEntity::INTERIM_STATUS_REQUESTED) {
            $this->maybeRefundInterimFee($application);
        }

        return $result;
    }

    /**
     * @param ApplicationEntity|Licence $entity
     * @param $status
     */
    protected function updateStatusAndDate($entity, $status)
    {
        $entity->setStatus($this->getRepo()->getRefdataReference($status));
        $entity->setGrantedDate(new DateTime());
    }

    protected function createGrantFee(ApplicationEntity $application)
    {
        $data = [
            'id' => $application->getId()
        ];

        return $this->handleSideEffectAsSystemUser(CreateGrantFeeCmd::create($data));
    }
}
