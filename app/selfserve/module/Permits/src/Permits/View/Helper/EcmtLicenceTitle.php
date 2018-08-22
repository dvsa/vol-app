<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

/**
 * Generate the ECMT Licence Page title
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.co.uk>
 */
class EcmtLicenceTitle extends AbstractHelper
{

    /**
     * Determines which title to display for the Ecmt Licence page.
     *
     * If there are applications against all the organisation's licences then
     * the $licences should be empty and a warning/error message is displayed
     * instead of the question title.
     *
     * @param array $licences
     * @param array $application
     * @return string
     */
    public function __invoke(array $application, $translator, array $licences = array())
    {
        if (empty($licences)) {
            return ''; //Throw exception?
        }

        if (isset($application['licence'])) {
            //there is an existing application
            //So put the licence that the application is for in the title
            $licenceForDisplay = $this->formatLicenceForDisplay(
                $application['licence']['licNo'],
                $application['licence']['trafficArea']['name']
            );

            return $translator->translateReplace('permits.page.ecmt.licence.question.one.licence', [$licenceForDisplay]);
        }

        //Determine if user already has an application for all relevant licences
        if ($licences['singleWithApplication']) {
            return $this->view->translate('permits.page.ecmt.licence.saturated.one.licence');
        }

        //Determine if user already has an application for all relevant licences
        if ($licences['multipleWithApplications']) {
            return $this->view->translate('permits.page.ecmt.licence.saturated');
        }

        if (count($licences['result']) == 1) {
            $licenceForDisplay = $this->formatLicenceForDisplay(
                $licences['result'][0]['licNo'],
                $licences['result'][0]['trafficArea']
            );
            return $translator->translateReplace('permits.page.ecmt.licence.question.one.licence', [$licenceForDisplay]);
        }

        return $this->view->translate('permits.page.ecmt.licence.question');
    }


    private function formatLicenceForDisplay($licNo, $trafficArea)
    {
        return $licNo . ' (' . $trafficArea . ')';
    }
}
