<?php

/**
 * Licence Overview View Model
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\View\Model\Licence;

use Olcs\View\Model\LvaOverview;

/**
 * Licence Overview View Model
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Overview extends LvaOverview
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'licence/overview';

    /**
     * Set the overview data
     *
     * @param array $data
     * @param array $sections
     */
    public function __construct($data, array $sections = array())
    {
        $this->setVariable('licenceId', $data['id']);
        $this->setVariable('grantedDate', date('d F Y', strtotime($data['grantedDate'])));
        $this->setVariable('expiryDate', date('d F Y', strtotime($data['expiryDate'])));
        $this->setVariable('status', $data['status']['id']);

        parent::__construct($data, $sections);
    }
}
