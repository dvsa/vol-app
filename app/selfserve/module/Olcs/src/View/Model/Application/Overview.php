<?php

/**
 * Application Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model\Application;

use Olcs\View\Model\LvaOverview;

/**
 * Application Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Overview extends LvaOverview
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'application/overview';

    /**
     * Set the overview data
     *
     * @param array $data
     * @param array $sections
     */
    public function __construct($data, array $sections = array())
    {
        $this->setVariable('applicationId', $data['id']);
        $this->setVariable('createdOn', date('d F Y', strtotime($data['createdOn'])));
        $this->setVariable('status', $data['status']['id']);

        parent::__construct($data, $sections);
    }
}
