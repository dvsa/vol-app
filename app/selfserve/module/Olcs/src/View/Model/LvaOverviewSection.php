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
    protected $variables = array(
        'enabled' => true
    );

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
    protected $template = 'partials/overview_section';

    protected $type;

    public function __construct($ref, $data, $mode)
    {
        $this->ref = $ref;
        $orgType = isset($data['licence']['organisation']['type']['id']) ?
            $data['licence']['organisation']['type']['id'] : $data['organisation']['type']['id'];

        $this->setVariable('identifier', $data['id']);
        $this->setVariable('identifierIndex', $data['idIndex']);
        $this->setVariable('name', 'section.name.' . $ref . (($ref === 'people') ? ('.' . $orgType) : ''));
        $this->setVariable('route', 'lva-' . $this->type . '/' . $ref);
        $this->setVariable('link', 'section.link.' . $mode . '.' . $ref);
        $this->setVariable('anchorRef', $ref);
    }
}
