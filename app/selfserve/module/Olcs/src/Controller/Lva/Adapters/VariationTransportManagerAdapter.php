<?php

namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\VariationTransportManagerAdapter as CommonAdapter;

/**
 * Variation Transport Manager Adapter
 * 
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class VariationTransportManagerAdapter extends CommonAdapter
{
    protected $tableSortMethod = self::SORT_LAST_FIRST_NAME_NEW_AT_END;
}
