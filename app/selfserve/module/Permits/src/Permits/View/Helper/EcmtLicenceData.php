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
        //$validFrom = date('d F Y', strtotime($stock[0]['validFrom']));
        //$validTo = date('d F Y', strtotime($stock[0]['validTo']));
        $validFrom = '01 January 2019';
        $validTo = '31 December 2019';

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

        $licences = $form->get('Fields')->get('EcmtLicence')->getValueOptions();
        $licenceCount = 0;
        foreach ($licences as $licence) {
            if ($licence['value'] !== '') {
                $licenceCount++;

                if (!empty($application)) {
                    if ($licence['value'] === $application['licence']['id']) {
                        $form->get('Fields')->get('EcmtLicence')->setValue($licence['value']);
                    }
                }
            }
        }

        if ($licenceCount === 1) {
            if (empty($application)) {
                $data['title'] = sprintf(
                    $this->view->translate('permits.page.ecmt.licence.question.one.licence'),
                    preg_replace("/<div(.*?)>(.*?)<\/div>/i", "", $licences[0]['label'])
                );

                // @todo: Refactor how we identify a restricted licence and pass to the view. We should not be defining html in a Common data service (EcmtLicence) and we do not consider multiple licence options or when a user selects a non-restricted licence which would likely need to be delivered by JavaScript.
                if (array_key_exists('html_elements', $licences[0])) {
                    $data['copy'] .= '<p>' . $this->view->translate('permits.form.ecmt-licence.restricted-licence.hint') . '</p>';
                }
            }
        }

        return $data;
    }
}
