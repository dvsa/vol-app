<?php

/**
 * Variation Overview Section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model\Variation;

use Olcs\View\Model\LvaOverviewSection;

/**
 * Variation Overview Section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOverviewSection extends LvaOverviewSection
{
    protected $type = 'variation';

    public function __construct($ref, $data)
    {
        parent::__construct($ref, $data, 'update');
    }
}
