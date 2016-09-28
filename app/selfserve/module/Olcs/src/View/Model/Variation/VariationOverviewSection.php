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

    public function __construct($ref, $data, $sectionDetails)
    {
        // @NOTE Can we replace this with UnderscoreToCamelCase
        $filter = new \Zend\Filter\Word\DashToCamelCase();
        $index = lcfirst($filter->filter(str_replace('_', '-', $ref)));

        switch ($sectionDetails['status']) {
            case 1:
                $statusText = 'REQUIRES ATTENTION';
                $statusColour = 'orange';
                break;
            case 2:
                $statusText = 'UPDATED';
                $statusColour = 'green';
                break;
            default:
                $statusText = '';
                $statusColour = '';
                break;
        }

        if (isset($sectionDetails['enabled'])) {
            $this->setVariable('enabled', $sectionDetails['enabled']);
        }
        $this->setVariable('status', $statusText);
        $this->setVariable('statusColour', $statusColour);

        parent::__construct($ref, $data, 'update');
    }
}
