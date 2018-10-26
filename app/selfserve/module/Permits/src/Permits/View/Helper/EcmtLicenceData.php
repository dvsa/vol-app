<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;

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
     * @param object $form
     * @param array $application
     * @return string
     */
    public function __invoke($form, $stock, $application = [])
    {
        $permitType = isset($application['permitType']['description']) ? $application['permitType']['description'] : 'ECMT';
        $validFrom = date('d F Y', strtotime($stock['validFrom']));
        $validTo = date('d F Y', strtotime($stock['validTo']));
        $translator = $this->view->getHelperPluginManager()->getServiceLocator()->get('Helper\Translation');

        $data['title'] = $this->view->translate('permits.page.ecmt.licence.question');
        // @todo: Remove custom styling, and markup should only be defined in the view template.
        $data['copy'] = '<p class="guidance-blue extra-space large">' .
            sprintf(
                $this->view->translate('permits.page.ecmt.licence.info'),
                $permitType,
                $validFrom,
                $validTo
            )
        . '</p>';

        $EcmtLicence = $form->get('Fields')->get('EcmtLicence');
        $licences = $EcmtLicence->getValueOptions();

        if (count($licences) === 1) {
            if ($licences[0]['value'] !== '' && $licences[0]['hasActiveEcmtApplication']) {
                $data['title'] = $this->view->translate('permits.page.ecmt.licence.saturated');
                $data['copy'] = $translator->translateReplace('markup-ecmt-licence-saturated', [$this->view->url('permits')]);
                $data['empty'] = true;
                $form->remove('Submit');

                return $data;
            }
        } else {
            foreach ($licences as $key => $licence) {
                if ($licence['value'] !== '' && $licence['hasActiveEcmtApplication']) {
                    if (!empty($application)) {
                        if ($licence['value'] === $application['licence']['id']) {
                            $EcmtLicence->setValue($licence['value']);
                        } else {
                            $EcmtLicence->unsetValueOption($key);
                        }
                    } else {
                        $EcmtLicence->unsetValueOption($key);


                    }
                }
            }
        }

        $licencesFiltered = array_values($form->get('Fields')->get('EcmtLicence')->getValueOptions());

        if (count($licencesFiltered) === 1) {
            $data['title'] = sprintf(
                $this->view->translate('permits.page.ecmt.licence.question.one.licence'),
                preg_replace("/<div(.*?)>(.*?)<\/div>/i", "", $licencesFiltered[0]['label'])
            );

            // @todo: Refactor how we identify a restricted licence and pass to the view. We should not be defining html in a Common data service (EcmtLicence) and we do not consider multiple licence options or when a user selects a non-restricted licence which would likely need to be delivered by JavaScript.
            if (array_key_exists('html_elements', $licencesFiltered[0])) {
                $data['copy'] .= '<p>' . $this->view->translate('permits.form.ecmt-licence.restricted-licence.hint') . '</p>';
            }
        }

        return $data;
    }
}
