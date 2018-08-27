<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

/**
 * Generate the ECMT Licence Page title
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.co.uk>
 */
class EcmtLicenceData extends AbstractHelper
{

    /**
     * Determines which title to display for the Ecmt Licence page.
     *
     * If there are applications against all the organisation's licences then
     * the $licences should be empty and a warning/error message is displayed
     * instead of the question title.
     *
     * @param object $form
     * @param array $application
     * @return string
     */
    public function __invoke($form, $application = [])
    {

        $licences = $form->get('Fields')->get('EcmtLicence')->getValueOptions();

        $licenceCount = 0;
        foreach($licences as $licence){
            if ($licence['value'] !== '') {
                $licenceCount++;
            }
        }


        $data['empty'] = false;

        if ($licenceCount === 0) {
            $data['title'] = $this->view->translate('permits.page.ecmt.licence.saturated');
            $data['copy'] = sprintf($this->view->translate('markup-ecmt-licence-saturated'), '/permits');
            $data['empty'] = true;
            return $data;
        }

        if ($licenceCount > 1) {
            $data['title'] = $this->view->translate('permits.page.ecmt.licence.question');
            $data['copy'] = '';
        } else {
            $data['title'] = sprintf($this->view->translate('permits.page.ecmt.licence.question.one.licence'), $licences[0]['label']);
            $data['copy'] = '';
            if (array_key_exists('label_attributes',$licences[0])) {
                $data['copy'] = $this->view->translate('markup-ecmt-licence-restricted-info');
            }
        }
        if (!empty($application)) {
            $data['title'] = sprintf($this->view->translate('permits.page.ecmt.licence.question.one.licence'),
                $application['licence']['licNo'] . ' (' . $application['licence']['trafficArea']['name'] . ')');
        }


        return $data;
    }
}
