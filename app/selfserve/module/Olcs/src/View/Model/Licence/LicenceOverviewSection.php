<?php

/**
 * Licence Overview Section
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\View\Model\Licence;

use Olcs\View\Model\OverviewSection;

/**
 * Licence Overview Section
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceOverviewSection extends OverviewSection
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

        // @todo these need sorting out
        //$this->setVariable('route', 'application/' . $ref);
        //$this->setVariable('route', 'application/type-of-licence');
        //$this->setVariable('status', $statusText);
        $this->setVariable('link', 'todo');
    }
}
