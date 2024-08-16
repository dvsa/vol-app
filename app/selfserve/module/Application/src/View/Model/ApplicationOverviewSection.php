<?php

namespace Dvsa\Olcs\Application\View\Model;

use Olcs\View\Model\LvaOverviewSection;

class ApplicationOverviewSection extends LvaOverviewSection
{
    protected $type = 'application';

    /**
     * ApplicationOverviewSection constructor.
     *
     * @param array|null|\Traversable $ref            Reference
     * @param array|null|\Traversable $data           Data array
     * @param array                   $sectionDetails Setion details
     */
    public function __construct($ref, $data, $sectionDetails)
    {
        $filter = new \Laminas\Filter\Word\DashToCamelCase();
        $index = lcfirst((string) $filter->filter(str_replace('_', '-', $ref)));

        $status = $data['applicationCompletion'][$index . 'Status'] ?? null;

        switch ($status) {
            case 1:
                $mode = 'edit';
                $statusText = 'Incomplete';
                $statusColour = 'orange';
                break;
            case 2:
                $mode = 'edit';
                $statusText = 'Complete';
                $statusColour = 'green';
                break;
            default:
                $mode = 'add';
                $statusText = 'Not started';
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
