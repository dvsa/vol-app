<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Service\Lva\Application\GrantValidationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Grant
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Grant extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    /**
     * @var GrantValidationService
     */
    private $grantValidationService;

    public function handleQuery(QueryInterface $query)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($query);

        $messages = $this->grantValidationService->validate($application);
        $canGrant = empty($messages);

        return $this->result(
            $application,
            [],
            [
                'canGrant' => $canGrant,
                'reasons' => $messages,
                'canHaveInspectionRequest' => $canGrant && $this->canHaveInspectionRequest($application)
            ]
        );
    }

    protected function canHaveInspectionRequest(ApplicationEntity $application)
    {
        return !$application->isVariation() && !$application->isSpecialRestricted();
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Grant
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->grantValidationService = $container->get('ApplicationGrantValidationService');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
