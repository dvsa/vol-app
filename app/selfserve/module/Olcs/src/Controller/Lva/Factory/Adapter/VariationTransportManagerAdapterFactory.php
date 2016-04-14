<?php

namespace Olcs\Controller\Lva\Factory\Adapter;

use Common\Controller\Lva\Factories\Adapter\AbstractTransportManagerAdapterFactory;
use Olcs\Controller\Lva\Adapters\VariationTransportManagerAdapter;

/**
 * Factory for creation Variation Transport Manager Adapter
 */
class VariationTransportManagerAdapterFactory extends AbstractTransportManagerAdapterFactory
{
    protected $adapterClass = VariationTransportManagerAdapter::class;
}
