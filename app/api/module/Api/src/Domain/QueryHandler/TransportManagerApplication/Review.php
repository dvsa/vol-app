<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Generator;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Review
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Review extends AbstractQueryHandler
{
    protected $repoServiceName = 'TransportManagerApplication';

    /**
     * @var Generator
     */
    protected $reviewSnapshotService;

    public function handleQuery(QueryInterface $query)
    {
        /** @var TransportManagerApplication $tma */
        $tma = $this->getRepo()->fetchUsingId($query);

        $isInternalUser = $this->isGranted(Permission::INTERNAL_USER);

        $markup = $this->reviewSnapshotService->generate($tma, $isInternalUser);

        return ['markup' => $markup];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Review
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->reviewSnapshotService = $container->get('TmReviewSnapshot');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
