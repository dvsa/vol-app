<?php

/**
 * Dashboard View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model;

use Common\View\AbstractViewModel;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Dashboard View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Dashboard extends AbstractViewModel
{
    /**
     * Holds the applications
     *
     * @var array
     */
    private $applications = array();

    /**
     * Holds the variations
     *
     * @var array
     */
    private $variations = array();

    /**
     * Holds the licences
     *
     * @var array
     */
    private $licences = array();

    /**
     * Set the template for the dashboard
     *
     * @var string
     */
    protected $template = 'dashboard';

    /**
     * Restrict the types of licence we display
     */
    private $displayLicenceStatus = array(
        LicenceEntityService::LICENCE_STATUS_VALID,
        LicenceEntityService::LICENCE_STATUS_CURTAILED,
        LicenceEntityService::LICENCE_STATUS_SUSPENDED
    );


    /**
     * Set the application data
     *
     * @param array $data
     */
    public function setApplications(array $data)
    {
        $this->applications = array();
        $this->variations = array();
        $this->licences = array();

        if (isset($data['licences']) && !empty($data['licences'])) {

            foreach ($data['licences'] as $licence) {

                if (in_array($licence['status']['id'], $this->displayLicenceStatus)) {
                    $licence['status'] = $licence['status']['id'];
                    $licence['type'] = $licence['licenceType']['id'];
                    $this->licences[$licence['id']] = $licence;
                }

                foreach ($licence['applications'] as $application) {
                    $newRow = $application;
                    $newRow['licNo'] = $licence['licNo'];
                    $newRow['status'] = (string)$application['status']['id'];

                    $skipStatuses = [
                        ApplicationEntityService::APPLICATION_STATUS_VALID
                    ];

                    if (!in_array($newRow['status'], $skipStatuses)) {
                        if ($application['isVariation']) {
                            $this->variations[$newRow['id']] = $newRow;
                        } else {
                            $this->applications[$newRow['id']] = $newRow;
                        }
                    }
                }
            }

            krsort($this->licences);
            krsort($this->variations);
            krsort($this->applications);
        }

        $this->setVariable('licences', $this->getTable('dashboard-licences', $this->licences));
        $this->setVariable('variations', $this->getTable('dashboard-variations', $this->variations));
        $this->setVariable('applications', $this->getTable('dashboard-applications', $this->applications));
    }
}
