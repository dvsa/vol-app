<?php

namespace Common\Controller\Lva\Adapters;

use Psr\Container\ContainerInterface;

class LicenceConditionsUndertakingsAdapter extends AbstractConditionsUndertakingsAdapter
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }
}
