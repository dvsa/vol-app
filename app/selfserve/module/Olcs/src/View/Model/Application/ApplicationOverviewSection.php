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

    public function __construct($ref, $data, $sectionDetails)
    {
        $filter = new \Zend\Filter\Word\DashToCamelCase();
        $index = lcfirst($filter->filter(str_replace('_', '-', $ref)));

        $status = isset($data['applicationCompletion'][$index . 'Status'])
            ? $data['applicationCompletion'][$index . 'Status']
            : null;

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

        $this->setVariable('enabled', $sectionDetails['enabled']);
        $this->setVariable('status', $statusText);
        $this->setVariable('statusColour', $statusColour);
        if (isset($data['sectionNumber'])) {
            $this->setVariable('sectionNumber', $data['sectionNumber']);
        }

        parent::__construct($ref, $data, $mode);
    }
}
