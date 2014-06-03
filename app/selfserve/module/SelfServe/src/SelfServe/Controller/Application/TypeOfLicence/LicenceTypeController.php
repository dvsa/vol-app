<?php

/**
 * LicenceType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\TypeOfLicence;

/**
 * LicenceType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceTypeController extends TypeOfLicenceController
{
    protected $fieldset = 'licence-type';

    protected function getFormAlterationOptions()
    {
        return ['isPsv' => $this->isPsv(), 'fieldset' => 'licence-type'];
    }

    public static function makeFormAlterations($form, $options = array())
    {
        // get rid of the special restricted licence if they're a goods operator
        if (!$options['isPsv']) {
            $fieldset = $form->get($options['fieldset']);

            if (!$fieldset->has('licenceType')) {
                return $form;
            }

            $licenceTypeElement = $fieldset->get('licenceType');

            $options = $licenceTypeElement->getValueOptions();

            unset($options['special-restricted']);

            $licenceTypeElement->setValueOptions($options);
        }

        return $form;
    }
}
