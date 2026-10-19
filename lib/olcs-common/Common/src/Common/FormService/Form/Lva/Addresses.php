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

    private const array ESTABLISHMENT_ADDRESS_LABELS = [
        'addressLine1' => 'lva.establishment-address.address-line-1',
        'addressLine2' => 'lva.establishment-address.address-line-2',
        'addressLine3' => 'lva.establishment-address.address-line-3',
        'addressLine4' => 'lva.establishment-address.address-line-4',
        'town' => 'lva.establishment-address.town-city',
        'postcode' => 'lva.establishment-address.postcode',
        'countryCode' => 'lva.establishment-address.country',
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
        $this->labelEstablishmentAddress($form);
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

    /**
     * Name the establishment address in its field labels, so they can be told apart from the
     * correspondence address on the same page. The shared address fieldset has generic labels.
     *
     * @param \Laminas\Form\Form $form Form
     */
    protected function labelEstablishmentAddress(\Laminas\Form\Form $form): void
    {
        if (!$form->has('establishment_address')) {
            return;
        }

        $address = $form->get('establishment_address');

        foreach (self::ESTABLISHMENT_ADDRESS_LABELS as $name => $label) {
            $address->get($name)->setLabel($label);
        }
    }
}
