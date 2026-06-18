<?php

namespace Common\Controller\Lva\Adapters;

use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Psr\Container\ContainerInterface;

/**
 * Application Transport Manager Adapter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class ApplicationTransportManagerAdapter extends AbstractTransportManagerAdapter
{
    protected $applicationData;

    public function __construct(TransferAnnotationBuilder $transferAnnotationBuilder, CachingQueryService $querySrv, CommandService $commandSrv, ContainerInterface $container)
    {
        parent::__construct($transferAnnotationBuilder, $querySrv, $commandSrv, $container);
    }

    /**
     * Load data into the table
     */
    #[\Override]
    public function getTableData($applicationId, $licenceId)
    {
        $query = $this->transferAnnotationBuilder->createQuery(
            \Dvsa\Olcs\Transfer\Query\Application\TransportManagers::create(['id' => $applicationId])
        );

        $data = $this->querySrv->send($query)->getResult();

        $this->applicationData = $data;

        return $this->mapResultForTable($data['transportManagers']);
    }

    /**
     * Must this licence type have at least one Transport Manager
     *
     * @param int $applicationId Application ID
     *
     * @return bool
     */
    #[\Override]
    public function mustHaveAtLeastOneTm()
    {
        if (!isset($this->applicationData['licenceType']['id'])) {
            throw new \RuntimeException('Application data is not setup');
        }

        $mustHaveTypes = [
            RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            RefData::LICENCE_TYPE_STANDARD_NATIONAL,
        ];

        return in_array($this->applicationData['licenceType']['id'], $mustHaveTypes, true);
    }

    /**
     * Delete Transport Managers
     *
     * @param array $ids Transport Manager Application IDs
     *
     * @return bool whether successful
     */
    #[\Override]
    public function delete(array $ids, $applicationId)
    {
        $command = $this->transferAnnotationBuilder->createCommand(
            \Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Delete::create(['ids' => $ids])
        );

        return $this->commandSrv->send($command)->isOk();
    }
}
