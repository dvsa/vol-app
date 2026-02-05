<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Service\Qa\QaContextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\SelfservePageGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStep as ApplicationStepQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Application step
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationStep extends AbstractQueryHandler
{
    /** @var QaContextGenerator */
    private $qaContextGenerator;

    /** @var SelfservePageGenerator */
    private $selfservePageGenerator;

    /**
     * Handle query
     *
     * @param QueryInterface|ApplicationStepQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $qaContext = $this->qaContextGenerator->generate(
            $query->getId(),
            $query->getIrhpPermitApplication(),
            $query->getSlug()
        );

        $selfservePage = $this->selfservePageGenerator->generate($qaContext);
        return $selfservePage->getRepresentation();
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationStep
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->qaContextGenerator = $container->get('QaContextGenerator');
        $this->selfservePageGenerator = $container->get('QaSelfservePageGenerator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
