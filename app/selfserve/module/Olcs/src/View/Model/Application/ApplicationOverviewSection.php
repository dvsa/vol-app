<?php

/**
 * Application Overview Section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model\Application;

use Olcs\View\Model\LvaOverviewSection;

/**
 * Application Overview Section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOverviewSection extends LvaOverviewSection
{
    protected $type = 'application';

    public function __construct($ref, $data)
    {
        $filter = new \Zend\Filter\Word\DashToCamelCase();
        $index = lcfirst($filter->filter(str_replace('_', '-', $ref)));

        $status = $data['applicationCompletions'][0][$index . 'Status'];
        $statusColour = '';

        switch ($status) {
            case 1:
                $mode = 'edit';
                $statusText = 'INCOMPLETE';
                $statusColour = 'orange';
                break;
            case 2:
                $mode = 'edit';
                $statusText = 'COMPLETE';
                $statusColour = 'green';
                break;
            default:
                $mode = 'add';
                $statusText = 'NOT STARTED';
                $statusColour = 'grey';
                break;
        }

        $this->setVariable('status', $statusText);
        $this->setVariable('statusColour', $statusColour);

        parent::__construct($ref, $data, $mode);
    }
}
