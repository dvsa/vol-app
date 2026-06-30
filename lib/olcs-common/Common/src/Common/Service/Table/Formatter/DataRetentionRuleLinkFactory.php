<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DataRetentionRuleLinkFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return DataRetentionRuleLink
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $urlHelper = $container->get('Helper\Url');
        return new DataRetentionRuleLink($urlHelper);
    }
}
