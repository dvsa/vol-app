<?php

/**
 * LicenceType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\TypeOfLicence;

/**
 * LicenceType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceTypeController extends TypeOfLicenceController
{
    /**
     * which fieldset of the form we represent
     *
     * @var string
     */
    protected $fieldset = 'licence-type';

    /**
     * return the relevant alteration options needed to determine how to manipulate
     * our form
     *
     * @return array
     */
    protected function getFormAlterationOptions()
    {
        return ['isPsv' => $this->isPsv(), 'fieldset' => 'licence-type'];
    }

    /**
     * Make form alterations
     *
     * This method enables the summary to apply the same form alterations. In this
     * case we ensurewe manipulate the form based on whether the license is PSV or not
     *
     * @param Form $form
     * @param array $options
     *
     * @return $form
     */
    public static function makeFormAlterations($form, $options = array())
    {
        // get rid of the special restricted licence if they're a goods operator
        if (!$options['isPsv']) {
            $fieldset = $form->get($options['fieldset']);

            $licenceTypeElement = $fieldset->get('licenceType');

            $options = $licenceTypeElement->getValueOptions();

            unset($options['special-restricted']);

            $licenceTypeElement->setValueOptions($options);
        }

        return $form;
    }
}
