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
        parent::__construct($ref, $data);

        $filter = new \Zend\Filter\Word\DashToCamelCase();
        $index = lcfirst($filter->filter(str_replace('_', '-', $ref)));

        $status = $data['applicationCompletions'][0][$index . 'Status'];

        switch ($status) {
            case 1:
                $linkSuffix = 'edit';
                $statusText = 'INCOMPLETE';
                break;
            case 2:
                $linkSuffix = 'edit';
                $statusText = 'COMPLETE';
                break;
            default:
                $linkSuffix = 'add';
                $statusText = 'NOT STARTED';
                break;
        }

        $this->setVariable('status', $statusText);
        $this->setVariable('link', 'section.link.' . $linkSuffix . '.' . $ref);
    }
}
