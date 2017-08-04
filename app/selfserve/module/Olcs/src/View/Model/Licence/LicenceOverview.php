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
     * @param array $data      Data for the view
     * @param array $sections  Sections?
     * @param array $variables Variables?
     */
    public function __construct($data, array $sections = array(), $variables = array())
    {
        $this->setVariables($variables);

        $this->setVariable('licenceId', $data['licNo']);
        $this->setVariable('startDate', $data['inForceDate']);
        $this->setVariable('renewalDate', $data['expiryDate']);
        $this->setVariable('status', $data['status']['id']);
        $this->setVariable('expiryDate', $data['expiryDate']);
        $this->setVariable('showExpiryWarning', $data['showExpiryWarning']);
        if (!empty($data['continuationMarker']['id'])) {
            $this->setVariable('continuationDetailId', $data['continuationMarker']['id']);
        }
        // If either isExpired or isExpiring flags are set then override the displayed status
        if (isset($data['isExpiring']) && $data['isExpiring'] === true) {
            $this->setVariable('status', 'licence.status.expiring');
        }
        if (isset($data['isExpired']) && $data['isExpired'] === true) {
            $this->setVariable('isExpired', $data['isExpired']);
            $this->setVariable('status', 'licence.status.expired');
        }

        parent::__construct($data, $sections);
    }
}
