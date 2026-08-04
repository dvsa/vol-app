<?php

namespace Common\Controller\Lva\Adapters;

use Psr\Container\ContainerInterface;

class VariationFinancialEvidenceAdapter extends ApplicationFinancialEvidenceAdapter
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }
}
