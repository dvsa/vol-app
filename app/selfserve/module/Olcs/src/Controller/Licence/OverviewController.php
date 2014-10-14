<?php

/**
 * Licence Overview Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\View\Model\Licence\LicenceOverview;

/**
 * Licence Overview Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractLicenceController
{
    /**
     * Licence overview
     */
    public function indexAction()
    {
        $data = $this->getServiceLocator()->get('Entity\Licence')->getOverview($this->getLicenceId());

        return new LicenceOverview($data, $this->getAccessibleSections());
    }
}
