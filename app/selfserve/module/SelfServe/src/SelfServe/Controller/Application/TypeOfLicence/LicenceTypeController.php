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

            $licenceTypeElement = $fieldset->get('licenceType');

            $options = $licenceTypeElement->getValueOptions();

            unset($options['special-restricted']);

            $licenceTypeElement->setValueOptions($options);
        }

        return $form;
    }

    /**
     * Cache the data for the form
     *
     * @param int $id
     * @return array
     */
    protected function loadData($id)
    {
        unset($id);

        if (empty($this->data)) {

            $this->data = $this->getLicenceData();
        }

        return $this->data;
    }

    /**
     * Load data from id
     *
     * @param int $id
     */
    protected function load($id)
    {
        return array('data' => $this->loadData($id));
    }
}
