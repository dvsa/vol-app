<?php

/**
 * Safety Controller
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Traits\SafetySection;
use Olcs\Controller\Licence\Details\AbstractLicenceDetailsController;

/**
 * Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends AbstractLicenceDetailsController
{
    use SafetySection;

    /**
     * Set the form name
     *
     * @var string
     */
    protected $formName = 'application_vehicle-safety_safety';

    /**
     * Setup the section
     *
     * @var string
     */
    protected $section = 'safety';

    /**
     * Licence
     *
     * @var string
     */
    protected $service = 'Licence';

    /**
     * Data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version',
            'safetyInsVehicles',
            'safetyInsTrailers',
            'safetyInsVaries',
            'tachographInsName',
            'isMaintenanceSuitable',
        ),
        'children' => array(
            'goodsOrPsv' => array(
                'properties' => array('id')
            ),
            'tachographIns' => array(
                'properties' => array('id')
            ),
            'workshops' => array(
                'properties' => array(
                    'id',
                    'isExternal'
                ),
                'children' => array(
                    'contactDetails' => array(
                        'properties' => array(
                            'fao'
                        ),
                        'children' => array(
                            'address' => array(
                                'properties' => array(
                                    'addressLine1',
                                    'addressLine2',
                                    'addressLine3',
                                    'addressLine4',
                                    'town',
                                    'postcode'
                                ),
                                'children' => array(
                                    'countryCode' => array(
                                        'properties' => array('id')
                                    )
                                )
                            )
                        )
                    )
                )
            )
        )
    );

    /**
     * Save the form data
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        if ($this->isButtonPressed('cancel')) {

            $this->addInfoMessage('Your changes have been discarded');

            return $this->redirect()->toRoute(null, array(), array(), true);

        } else {

            $this->saveCrud($data);

            $this->addSuccessMessage('Your changes have been saved successfully');

            return $this->redirect()->toRoute(null, array(), array(), true);
        }
    }

    /**
     * Save crud
     *
     * @param array $data
     */
    protected function saveCrud($data)
    {
        $data = $this->formatSaveData($data);

        $data['licence']['isMaintenanceSuitable'] = $data['application']['isMaintenanceSuitable'];

        parent::save($data['licence'], 'Licence');
    }

    /**
     * Load the data for the form
     *
     * @param arary $data
     * @return array
     */
    protected function processLoad($data)
    {
        $data = array(
            'id' => null,
            'version' => null,
            'safetyConfirmation' => null,
            'isMaintenanceSuitable' => $data['isMaintenanceSuitable'],
            'licence' => $data
        );

        return $this->doProcessLoad($data);
    }

    /**
     * Get the form table data
     *
     * @param int $id
     * @param string $table
     */
    protected function getFormTableData($id, $table)
    {
        $data = $this->load($id)['workshops'];

        return $this->doGetFormTableData($data);
    }

    /**
     * Remove the trailer fields for PSV
     *
     * @param \Zend\Form\Fieldset $form
     * @return \Zend\Form\Fieldset
     */
    protected function alterForm($form)
    {
        $form = $this->doAlterForm(parent::alterForm($form), false, $this->isPsv());

        $form->get('application')->remove('safetyConfirmation');

        $this->setFieldsAsNotRequired($form->getInputFilter());

        return $form;
    }

    /**
     * Check if the licence is psv
     *
     * @return boolean
     */
    protected function isPsv()
    {
        $data = $this->load($this->getIdentifier());

        return (isset($data['goodsOrPsv']['id']) && $data['goodsOrPsv']['id'] == self::LICENCE_CATEGORY_PSV);
    }
}
