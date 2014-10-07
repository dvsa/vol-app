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
    /**
     * Holds the section reference
     *
     * @var string
     */
    private $ref;

    public function __construct($ref, $data, $mode = 'add')
    {
        $this->ref = $ref;

        $this->setVariable('applicationId', $data['id']);
        $this->setVariable('name', 'section.name.' . $ref);

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

        // @todo these need sorting out
        //$this->setVariable('route', 'application/' . $ref);
        $this->setVariable('route', 'application/type-of-licence');
        $this->setVariable('status', $statusText);
        $this->setVariable('link', 'section.link.' . $linkSuffix . '.' . $ref);
    }
}
