<?php

/**
 * Delete Bus
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface as Repository;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand as DeleteCommand;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Query\Bus\PreviousVariationByRouteNo;

/**
 * Delete Bus
 */
final class DeleteBus extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Bus';

    protected $extraRepos = ['TxcInbox', 'EbsrSubmission'];

    /**
     * Delete a bus registration and provides a redirect id for the previous bus reg, if it exists
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command DeleteCommand For traceability */
        $result = new Result();

        /** @var Repository $repo */
        $repo = $this->getRepo();

        /* @var BusReg $busReg */
        $busReg = $repo->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $busReg->canDelete();

        $routeNoQuery = [
            'sort' => 'variationNo',
            'order' => 'DESC',
            'page' => 1,
            'limit' => 1,
            'licenceId' => $busReg->getLicence()->getId(),
            'routeNo' => $busReg->getRouteNo(),
            'variationNo' => $busReg->getVariationNo(),
        ];

        $ebsrSubmissions = $busReg->getEbsrSubmissions();
        $txcInboxs = $busReg->getTxcInboxs();

        /** @var Entities\Ebsr\EbsrSubmission $ebsrSubmission */
        foreach ($ebsrSubmissions as $ebsrSubmission) {
            $this->getRepo('EbsrSubmission')->delete($ebsrSubmission);
        }

        /** @var Entities\Ebsr\TxcInbox $txcInbox */
        foreach ($txcInboxs as $txcInbox) {
            $this->getRepo('TxcInbox')->delete($txcInbox);
        }

        $previousBusReg = $repo->fetchList(PreviousVariationByRouteNo::create($routeNoQuery), Query::HYDRATE_OBJECT);

        $repo->delete($busReg);

        $result->addMessage('Deleted');

        if ($previousBusReg->count()) {
            $result->addId('previousBusRegId', $previousBusReg[0]->getId());
        }

        return $result;
    }
}
