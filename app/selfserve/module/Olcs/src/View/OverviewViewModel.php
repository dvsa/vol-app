<?php

/**
 * Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View;

use Common\View\AbstractViewModel;

/**
 * Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewViewModel extends AbstractViewModel
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'application/overview';

    /**
     * Holds the data
     *
     * @var array
     */
    private $data;

    /**
     * Set the overview data
     *
     * @param array $data
     */
    public function __construct($data)
    {
        print '<pre>';
        print_r($data);
        exit;

        $this->setVariable('applicationId', $data['id']);
        $this->setVariable('createdOn', $data['createdOn']);
        $this->setVariable('status', $data['status']['id']);
    }
}
