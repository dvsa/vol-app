<?php

/**
 * Overview Section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model;

use Common\View\AbstractViewModel;

/**
 * Overview Section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewSection extends AbstractViewModel
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'overview_section';

    /**
     * Holds the section reference
     *
     * @var string
     */
    private $ref;

    public function __construct($ref, $applicationId, $mode = 'add')
    {
        $this->ref = $ref;

        $this->setVariable('applicationId', $applicationId);
        $this->setVariable('name', 'section.name.' . $ref);
        $this->setVariable('route', 'application/' . $ref);
    }

    /**
     * Setter for data
     *
     * @param array $data
     */
    public function setData($data)
    {

    }
}
