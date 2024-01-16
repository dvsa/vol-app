<?php

namespace Permits\Data\Mapper;

use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CandidatePermitSelectionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return CandidatePermitSelection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CandidatePermitSelection
    {
        return new CandidatePermitSelection(
            $container->get(HtmlAdder::class),
            $container->get(TableFactory::class)
        );
    }
}
