<?php

/**
 * CaseConditionUndertaking Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\ConditionUndertaking;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Common\Service\Table\Formatter\Address;

/**
 * ConditionUndertaking Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ConditionUndertakingController extends OlcsController\CrudAbstract
    implements OlcsController\Interfaces\CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'condition';

    /**
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = null;

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'ConditionUndertakingForm';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case-section';

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/case-details-subsection';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'ConditionUndertaking';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_conditions_undertakings';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'case',
    ];

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    /**
     * Data map
     *
     * @var array
    */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
                'base',
            )
        )
    );

    /**
     * Holds the isAction
     *
     * @var boolean
     */
    protected $isAction = false;

    /**
     * Holds the Data Bundle
     *
     * @var array
    */
    protected $dataBundle = array(
        'properties' => 'ALL',
        'children' => array(
            'case' => array(
                'properties' => array('id')
            ),
            /**
             * @todo [OLCS-5306] check this, it appears to be an invalid part of the bundle
            'prohibitionType' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            ),
             */
            'attachedTo' => array(
                'properties' => array('id', 'description')
            ),
            'conditionType' => array(
                'properties' => array('id', 'description')
            ),
            'operatingCentre' => array(
                'properties' => array('id'),
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
                                'properties' => array(
                                'id'
                            )
                        )
                        )
                    )
                )
            ),
            'addedVia' => array(
                'properties' => array('id', 'description')
            ),
        )
    );

    const CONDITION_TYPE_CONDITION = 'cdt_con';
    const CONDITION_TYPE_UNDERTAKING = 'cdt_und';

    const ATTACHED_TO_LICENCE = 'cat_lic';
    const ATTACHED_TO_OPERATING_CENTRE = 'cat_oc';

    /**
     * Added extra method called after setting form data
     *
     * @param Form $form
     * @return Form
     */
    public function alterFormBeforeValidation($form)
    {
        return $this->configureFormForConditionType($form, $this->getCase()['licence']['id']);
    }

    /**
     * Method to extract all Operating Centre Addresses for a given licence
     * and reformat into array suitable for select options
     *
     * @param integer $licenceId
     * @return array address_id => [address details]
     */
    public function getOcAddressByLicence($licenceId)
    {
        $result = $this->makeRestCall(
            'OperatingCentre',
            'GET',
            array('licence' => $licenceId),
            $this->getOcAddressBundle()
        );

        if ($result['Count']) {
            foreach ($result['Results'] as $oc) {
                $operatingCentreAddresses[$oc['id']] = Address::format($oc['address']);
            }
        }
        // set up the group options required by Zend
        $options = array(
            'Licence' => array(
                'label' => 'Licence',
                'options' => array(
                    self::ATTACHED_TO_LICENCE => 'Licence ' . $licenceId
                ),
            ),
            'OC' => array(
                'label' => 'OC Address',
                'options' => $operatingCentreAddresses
            )
        );

        return $options;
    }

    /**
     * Method to return the bundle required for getting all operating centre
     * addresses for a given licence
     *
     * @return array
     */
    public function getOcAddressBundle()
    {
        return array(
            'properties' => array(
                'id',
                'address'
            ),
            'children' => array(
                'address' => array(
                    'properties' => array(
                        'id',
                        'addressLine1',
                        'addressLine2',
                        'addressLine3',
                        'addressLine4',
                        'town',
                        'postcode'
                    ),
                    'children' => array(
                        'countryCode' => array(
                            'properties' => array(
                                'id'
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        $data = parent::processLoad($data);

        $data = $this->determineFormAttachedTo($data);

        return $data;
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    public function processSave($data)
    {
        $data = $this->determineSavingAttachedTo($data);

        return parent::processSave($data);
    }

    /**
     * The attachedTo dropdown has values of either 'licence' or an OC id
     * However what is stored is either 'OC' or 'Licence' so this method
     * sets the value to the OC id in preparation for generating the edit form
     *
     * @param array $data
     * @return array
     */
    public function determineFormAttachedTo($data)
    {
        // for form
        if (isset($data['fields']['attachedTo']) && $data['fields']['attachedTo'] != self::ATTACHED_TO_LICENCE) {
            $data['fields']['attachedTo'] =
                isset($data['fields']['operatingCentre']) ? $data['fields']['operatingCentre'] : '';
        }

        $data['fields']['licence'] = $this->getCase()['licence']['id'];

        return $data;
    }

    /**
     * The attachedTo dropdown has values of either 'licence' or an OC id
     * However what is stored is either 'OC' or 'Licence' so this method
     * sets the value from OC id to the value 'OC' or 'Licence'
     * in preparation for saving the data
     *
     * @param array $data
     * @return array
     */
    private function determineSavingAttachedTo($data)
    {
        if (strtolower($data['fields']['attachedTo']) !== self::ATTACHED_TO_LICENCE) {
            $data['fields']['operatingCentre'] = $data['fields']['attachedTo'];
            $data['fields']['attachedTo'] = self::ATTACHED_TO_OPERATING_CENTRE;
        } else {
            $data['fields']['operatingCentre'] = null;
            $data['fields']['attachedTo'] = self::ATTACHED_TO_LICENCE;
        }

        return $data;
    }

    /**
     * Sets the notes field label accoring to the type of condition.
     * i.e. Undertaking or Condition
     * Also extracts the Operating Centre addresses for the licence and sets
     * up the group options for the attachedTo drop down
     *
     * @param \Zend\Form\Form $form
     * @param integer $licenceId
     * @param string $type
     * @return \Zend\Form\Form $form
     */
    public function configureFormForConditionType($form, $licenceId)
    {
        $ocAddressList = $this->getOcAddressByLicence($licenceId);

        // set form dependent aspects
        $form->setLabel($form->getLabel() . ' Conditions / Undertakings');

        $form->get('fields')
            ->get('attachedTo')
            ->setValueOptions($ocAddressList);

        return $form;

    }
}
