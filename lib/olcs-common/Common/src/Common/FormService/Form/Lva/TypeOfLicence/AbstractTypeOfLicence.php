<?php

namespace Common\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\Form\Lva\AbstractLvaFormService;
use Common\FormService\FormServiceInterface;
use Common\RefData;
use Laminas\Form\Form;

/**
 * Abstract Type Of Licence Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTypeOfLicence extends AbstractLvaFormService
{
    public const ALLOWED_OPERATOR_LOCATION_NI = 'NI';

    public const ALLOWED_OPERATOR_LOCATION_GB = 'GB';

    /**
     * Get Form
     *
     * @param array $params parameters
     *
     * @return Form
     */
    public function getForm($params = [])
    {
        $form = $this->formHelper->createForm('Lva\TypeOfLicence');

        $this->alterForm($form, $params);

        return $form;
    }

    /**
     * Make changed in form
     *
     * @param Form  $form   Form
     * @param array $params parameters
     *
     * @return void
     */
    protected function alterForm(Form $form, $params = [])
    {
        // no op
    }

    /**
     * Make action when all elements are locked
     *
     * @param Form $form Form
     *
     * @return void
     */
    protected function allElementsLocked(Form $form)
    {
        // no op
    }

    /**
     * Lock Elements
     *
     * @param Form  $form   Form
     * @param array $params parameters
     *
     * @return void
     */
    protected function lockElements(Form $form, $params = [])
    {
        /** @var \Laminas\Form\Fieldset $typeOfLicenceFieldset */
        $typeOfLicenceFieldset = $form->get('type-of-licence');

        // Change labels
        $typeOfLicenceFieldset->get('operator-location')->setLabel('operator-location');
        $typeOfLicenceFieldset->get('operator-type')->setLabel('operator-type');
        $typeOfLicenceFieldset->get('licence-type')->setLabel('licence-type');

        // Add padlocks
        $this->formHelper->lockElement(
            $typeOfLicenceFieldset->get('operator-location'),
            'operator-location-lock-message'
        );
        $this->formHelper->lockElement(
            $typeOfLicenceFieldset->get('operator-type'),
            'operator-type-lock-message'
        );

        // Disable elements
        $this->formHelper->disableElement($form, 'type-of-licence->operator-location');
        $this->formHelper->disableElement($form, 'type-of-licence->operator-type');

        // Optional disable and lock type of licence
        if (!$params['canUpdateLicenceType']) {
            // Disable and lock type of licence
            $this->formHelper->disableElement($form, 'type-of-licence->licence-type->licence-type');
            $this->formHelper->disableElement(
                $form,
                'type-of-licence->licence-type->ltyp_siContent->vehicle-type'
            );
            $this->formHelper->disableElement(
                $form,
                'type-of-licence->licence-type->ltyp_siContent->lgv-declaration->lgv-declaration-confirmation'
            );
            $this->formHelper->lockElement(
                $typeOfLicenceFieldset->get('licence-type'),
                'licence-type-lock-message'
            );

            $this->allElementsLocked($form);
        }

        if (!$params['canBecomeSpecialRestricted']) {
            $this->formHelper->removeOption(
                $typeOfLicenceFieldset->get('licence-type')->get('licence-type'),
                RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
            );
        }
    }

    /**
     * Set and lock operator location
     *
     * @param Form   $form     Form
     * @param string $location Operator Location Code
     */
    public function setAndLockOperatorLocation($form, $location): void
    {
        /** @var \Laminas\Form\Fieldset $typeOfLicenceFieldset */
        $typeOfLicenceFieldset = $form->get('type-of-licence');

        $elmOperLoc = $typeOfLicenceFieldset->get('operator-location');

        $message = null;
        if ($location === self::ALLOWED_OPERATOR_LOCATION_NI) {
            $elmOperLoc->setValue('Y');
            $message = 'alternative-operator-location-lock-message-ni';
        } elseif ($location === self::ALLOWED_OPERATOR_LOCATION_GB) {
            $elmOperLoc->setValue('N');
            $message = 'alternative-operator-location-lock-message-gb';
        }

        $this->formHelper->disableElement($form, 'type-of-licence->operator-location');
        $this->formHelper->lockElement($elmOperLoc, $message);
    }

    public function disableLicenceType(Form $form): void
    {
        $this->formHelper->disableElement($form, 'type-of-licence->licence-type->licence-type');
    }

    /**
     * Alter form for NI applications
     *
     * @param Form $form Form
     */
    public function maybeAlterFormForNi($form): void
    {
        if ($form->get('type-of-licence')->get('operator-location')->getValue() === 'Y') {
            $form->getInputFilter()->get('type-of-licence')->get('operator-type')->setRequired(false);
        }
    }

    /**
     * Alter form for Goods/Standard International applications
     *
     * @param Form $form
     */
    public function maybeAlterFormForGoodsStandardInternational($form): void
    {
        $fieldset = $form->get('type-of-licence');
        $licenceTypeFieldset = $fieldset->get('licence-type');

        $operatorLocation = $fieldset->get('operator-location')->getValue();
        $operatorType = $fieldset->get('operator-type')->getValue();
        $licenceType = $licenceTypeFieldset->get('licence-type')->getValue();
        $vehicleType = $licenceTypeFieldset->get('ltyp_siContent')->get('vehicle-type')->getValue();

        $isGoods = $operatorLocation == 'Y' || $operatorType == RefData::LICENCE_CATEGORY_GOODS_VEHICLE;
        if ($isGoods && $licenceType == RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL) {
            if ($vehicleType != RefData::APP_VEHICLE_TYPE_LGV) {
                $form->getInputFilter()->get('type-of-licence')
                    ->get('licence-type')
                    ->get('ltyp_siContent')
                    ->remove('lgv-declaration');
            }
        } else {
            $form->getInputFilter()->get('type-of-licence')->get('licence-type')->remove('ltyp_siContent');
        }
    }
}
