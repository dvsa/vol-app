<?php

/**
 * Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\View\Model\Licence\LicenceOverview;

/**
 * Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceController extends AbstractLicenceController
{
    /**
     * Licence overview
     */
    public function indexAction()
    {
        $data = $this->getEntityService('Licence')->getOverview(
            $this->params('id')
        );

        $sections = $this->getHelperService('SectionAccessHelper')
            ->getAccessibleSections($data['goodsOrPsv']['id'], $data['licenceType']['id']);

        return new LicenceOverview($data, array_keys($sections));
    }
}
