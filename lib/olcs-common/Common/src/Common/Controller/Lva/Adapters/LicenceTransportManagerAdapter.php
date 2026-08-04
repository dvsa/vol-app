<?php

namespace Common\Controller\Lva\Adapters;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Psr\Container\ContainerInterface;

/**
 * Licence Transport Manager Adapter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class LicenceTransportManagerAdapter extends AbstractTransportManagerAdapter
{
    protected $lva = 'licence';

    public function __construct(TransferAnnotationBuilder $transferAnnotationBuilder, CachingQueryService $querySrv, CommandService $commandSrv, ContainerInterface $container)
    {
        parent::__construct($transferAnnotationBuilder, $querySrv, $commandSrv, $container);
    }

    /**
     * get table data
     *
     * @param null $applicationId not used here but needed to conform to interface
     * @param int  $licenceId     licence id
     *
     * @return array|null
     */
    #[\Override]
    public function getTableData($applicationId, $licenceId)
    {
        $query = $this->transferAnnotationBuilder->createQuery(
            \Dvsa\Olcs\Transfer\Query\Licence\TransportManagers::create(['id' => $licenceId])
        );

        $response = $this->querySrv->send($query);
        return $response->isForbidden()
            ? null
            : $this->mapResultForTable([], $response->getResult()['tmLicences']);
    }

    /**
     * delete a transport manager from a licence
     *
     * @param array $ids           ids to be deleted
     * @param null  $applicationId not used here but needed to conform to interface
     *
     * @return bool
     */
    #[\Override]
    public function delete(array $ids, $applicationId)
    {
        $command = $this->transferAnnotationBuilder->createCommand(
            \Dvsa\Olcs\Transfer\Command\TransportManagerLicence\Delete::create(['ids' => $ids])
        );

        return $this->commandSrv->send($command)->isOk();
    }
}
