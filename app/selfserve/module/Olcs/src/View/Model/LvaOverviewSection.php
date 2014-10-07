<?php

/**
 * Abstract Overview Section
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\View\Model;

use Common\View\AbstractViewModel;

/**
 * Abstract Overview Section
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class LvaOverviewSection extends AbstractViewModel
{
    /**
     * Holds the section reference
     *
     * @var string
     */
    private $ref;

    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'overview_section';

    protected $type;

    public function __construct($ref, $data, $mode)
    {
        $this->ref = $ref;

        $this->setVariable('identifier', $data['id']);
        $this->setVariable('name', 'section.name.' . $ref);
        $this->setVariable('route', $this->type . '/' . $this->refToRoute($ref));
        $this->setVariable('link', 'section.link.' . $mode . '.' . $ref);
    }

    protected function refToRoute($ref)
    {
        $filter = new \Zend\Filter\Word\UnderscoreToDash();
        return $filter->filter($ref);
    }

}
