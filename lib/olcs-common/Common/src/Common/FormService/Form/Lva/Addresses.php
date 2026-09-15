<?php

namespace Common\FormService\Form\Lva;

use Common\RefData;
use Common\Service\Helper\FormHelperService;

/**
 * Addresses Form
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Addresses
{
    private static $establishmentAllowedLicTypes = [
        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
    ];

    public function __construct(protected FormHelperService $formHelper)
    {
    }

    /**
     * Return form
     *
     * @param array $params Parameters
     *
     * @return \Laminas\Form\Form
     */
    public function getForm(array $params)
    {
        $form = $this->formHelper->createForm('Lva\Addresses');

        $this->alterForm($form, $params);

        return $form;
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form   Form
     * @param array           $params Parameters
     *
     * @return void
     */
    protected function alterForm(\Laminas\Form\Form $form, array $params)
    {
        $this->removeEstablishment($form, $params['typeOfLicence']['licenceType']);
    }

    /**
     * Remove Establishment Fields
     *
     * @param \Laminas\Form\Form $form        Form
     * @param string          $licenceType Licence type
     *
     * @return void
     */
    protected function removeEstablishment(\Laminas\Form\Form $form, $licenceType)
    {
        if (!in_array($licenceType, self::$establishmentAllowedLicTypes, true)) {
            $this->formHelper
                ->remove($form, 'establishment')
                ->remove($form, 'establishment_address');
        }
    }
}
