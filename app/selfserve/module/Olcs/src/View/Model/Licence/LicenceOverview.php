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
class LicenceOverview extends LvaOverview
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'overview-licence';

    protected $sectionModel = 'Licence\\LicenceOverviewSection';

    /**
     * Set the overview data
     *
     * @param array $data
     * @param array $sections
     */
    public function __construct($data, array $sections = array(), $variables = array())
    {
        $this->setVariables($variables);

        $this->setVariable('licenceId', $data['licNo']);
        $this->setVariable('startDate', $data['inForceDate']);
        $this->setVariable('renewalDate', $data['expiryDate']);
        $this->setVariable('status', $data['status']['id']);

        parent::__construct($data, $sections);
    }
}
