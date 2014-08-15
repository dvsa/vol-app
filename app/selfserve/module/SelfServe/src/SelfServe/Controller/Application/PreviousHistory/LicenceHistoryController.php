<?php

/**
 * LicenceHistory Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\PreviousHistory;

use SelfServe\Controller\Application\ApplicationController;

/**
 * LicenceHistory Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceHistoryController extends ApplicationController
{
    /**
     * Set the service for the "Free" save behaviour
     *
     * @var string
     */
    protected $service = 'Application';

    /**
     * Set the action service
     *
     * @var string
     */
    protected $actionService = 'PreviousLicence';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'dataLicencesCurrent', 'dataLicencesApplied', 'dataLicencesRevoked',
                'dataLicencesRefused', 'dataLicencesDisqualified', 'dataLicencesPublicInquiry',
                'dataLicencesHeld'
            ),
        )
    );

    /**
     * Data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version',
            'currentLicence',
            'appliedForLicence',
            'refusedLicence',
            'revokedLicence',
            'disqualifiedLicence',
            'publicInquiryLicence',
            'heldLicence'
        )
    );

    /**
     * Holds the actionDataBundle
     *
     * @var array
     */
    protected $actionDataBundle = array(
        'properties' => array(
            'id',
            'version',
            'licNo',
            'holderName',
            'willSurrender',
            'purchaseDate',
            'disqualificationDate',
            'disqualificationLength',
            'previousLicenceType'
        ),
    );

    /**
     * Form tables name
     *
     * @var string
     */
    protected $formTables = array(
        'table-licences-current' => 'previous_licences_current',
        'table-licences-applied' => 'previous_licences_applied',
        'table-licences-refused' => 'previous_licences_refused',
        'table-licences-revoked' => 'previous_licences_revoked',
        'table-licences-public-inquiry' => 'previous_licences_public_inquiry',
        'table-licences-disqualified' => 'previous_licences_disqualified',
        'table-licences-held' => 'previous_licences_held'
    );

    /**
     * Licence type - current
     */
    const LICENCE_TYPE_CURRENT = 'CURRENT';

    /**
     * Licence type - applied
     */
    const LICENCE_TYPE_APPLIED = 'APPPLIED';

    /**
     * Licence type - refused
     */
    const LICENCE_TYPE_REFUSED = 'REFUSED';

    /**
     * Licence type - revoked
     */
    const LICENCE_TYPE_REVOKED = 'REVOKED';

    /**
     * Licence type - public inquiry
     */
    const LICENCE_TYPE_PUBLIC_INQUIRY = 'PUBLIC_INQUIRY';

    /**
     * Licence type - disqualified
     */
    const LICENCE_TYPE_DISQUALIFIED = 'DISQUALIFIED';

    /**
     * Licence type - held
     */
    const LICENCE_TYPE_HELD = 'HELD';

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Save method
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        $data['id'] = $this->getIdentifier();
        parent::save($data, $service);
    }

    /**
     * Get the form table data
     *
     * @param int $applicationId
     * @param string $tableName
     * @return array
     */
    protected function getFormTableData($applicationId, $tableName)
    {
        $bundle = array(
            'properties' => array(
                'id',
                'version',
                'licNo',
                'holderName',
                'willSurrender',
                'purchaseDate',
                'disqualificationDate',
                'disqualificationLength',
                'previousLicenceType'
            ),
        );

        switch ($tableName) {
            case 'table-licences-current':
                $previousLicenceType = self::LICENCE_TYPE_CURRENT;
                break;
            case 'table-licences-applied':
                $previousLicenceType = self::LICENCE_TYPE_APPLIED;
                break;
            case 'table-licences-refused':
                $previousLicenceType = self::LICENCE_TYPE_REFUSED;
                break;
            case 'table-licences-revoked':
                $previousLicenceType = self::LICENCE_TYPE_REVOKED;
                break;
            case 'table-licences-public-inquiry':
                $previousLicenceType = self::LICENCE_TYPE_PUBLIC_INQUIRY;
                break;
            case 'table-licences-disqualified':
                $previousLicenceType = self::LICENCE_TYPE_DISQUALIFIED;
                break;
            case 'table-licences-held':
                $previousLicenceType = self::LICENCE_TYPE_HELD;
                break;
        }

        $data = $this->makeRestCall(
            'PreviousLicence',
            'GET',
            array('application' => $applicationId, 'previousLicenceType' => $previousLicenceType),
            $bundle
        );
        return $data;
    }

    /**
     * Add custom validation logic
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        $post = (array)$this->getRequest()->getPost();

        $tables = [
            'table-licences-current', 'table-licences-applied', 'table-licences-refused',
            'table-licences-revoked', 'table-licences-public-inquiry', 'table-licences-disqualified',
            'table-licences-held'
        ];
        $fieldsets = [
            'dataLicencesCurrent', 'dataLicencesApplied', 'dataLicencesRefused',
            'dataLicencesRevoked', 'dataLicencesPublicInquiry', 'dataLicencesDisqualified',
            'dataLicencesHeld'
        ];
        $fields = [
            'currentLicence', 'appliedForLicence', 'refusedLicence',
            'revokedLicence', 'publicInquiryLicence', 'disqualifiedLicence',
            'heldLicence'
        ];
        $shouldAddValidators = true;
        for ($i = 0; $i < count($tables); $i++) {
            if (array_key_exists($tables[$i], $post) &&
                  array_key_exists('action', $post[$tables[$i]])) {
                $shouldAddValidators = false;
                break;
            }
        }
        if ($shouldAddValidators) {
            for ($i = 0; $i < count($tables); $i++) {
                $rows = $form->get($tables[$i])->get('rows')->getValue();
                $licenceValidator =
                    new \Common\Form\Elements\Validators\PreviousHistoryLicenceHistoryLicenceValidator();
                $licenceValidator->setRows($rows);
                $currentLicence = $form->getInputFilter()->get($fieldsets[$i])->get($fields[$i])->getValidatorChain();
                $currentLicence->attach($licenceValidator);
            }
        }
        return $form;
    }

    /**
     * Action save
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $applicationId = $this->getIdentifier();
        $data['application'] = $applicationId;
        if (array_key_exists('willSurrender', $data) !== false) {
            $data['willSurrender'] = ($data['willSurrender'] == 'Y') ? 1 : 0;
        }
        parent::actionSave($data, $service);

    }

    /**
     * Process action load
     * 
     * @param $data
     */
    protected function processActionLoad($data)
    {
        $data = parent::processActionLoad($data);

        switch ($this->getActionName()) {
            case 'table-licences-current-add':
                $data['previousLicenceType'] = self::LICENCE_TYPE_CURRENT;
                break;
            case 'table-licences-applied-add':
                $data['previousLicenceType'] = self::LICENCE_TYPE_APPLIED;
                break;
            case 'table-licences-refused-add':
                $data['previousLicenceType'] = self::LICENCE_TYPE_REFUSED;
                break;
            case 'table-licences-revoked-add':
                $data['previousLicenceType'] = self::LICENCE_TYPE_REVOKED;
                break;
            case 'table-licences-public-inquiry-add':
                $data['previousLicenceType'] = self::LICENCE_TYPE_PUBLIC_INQUIRY;
                break;
            case 'table-licences-disqualified-add':
                $data['previousLicenceType'] = self::LICENCE_TYPE_DISQUALIFIED;
                break;
            case 'table-licences-held-add':
                $data['previousLicenceType'] = self::LICENCE_TYPE_HELD;
                break;
            default:
                break;
        }

        if (array_key_exists('willSurrender', $data)) {
            if ($data['willSurrender'] === true) {
                $data['willSurrender'] = 'Y';
            } elseif ($data['willSurrender'] === false) {
                $data['willSurrender'] = 'N';
            }
        }

        $returnData = ($this->getActionName() != 'add') ? array('data' => $data) : $data;
        return $returnData;
    }

    protected function alterActionForm($form)
    {
        switch ($this->getActionName()) {
            case 'table-licences-current-add':
            case 'table-licences-current-edit':
                $form->get('data')->remove('disqualificationDate');
                $form->get('data')->remove('disqualificationLength');
                $form->get('data')->remove('purchaseDate');
                break;
            case 'table-licences-applied-add':
            case 'table-licences-applied-edit':
            case 'table-licences-refused-add':
            case 'table-licences-refused-edit':
            case 'table-licences-revoked-add':
            case 'table-licences-revoked-edit':
            case 'table-licences-public-inquiry-add':
            case 'table-licences-public-inquiry-edit':
                $form->get('data')->remove('willSurrender');
                $form->get('data')->remove('disqualificationDate');
                $form->get('data')->remove('disqualificationLength');
                $form->get('data')->remove('purchaseDate');
                break;
            case 'table-licences-disqualified-add':
            case 'table-licences-disqualified-edit':
                $form->get('data')->remove('willSurrender');
                $form->get('data')->remove('purchaseDate');
                break;
            case 'table-licences-held-add':
            case 'table-licences-held-edit':
                $form->get('data')->remove('willSurrender');
                $form->get('data')->remove('disqualificationDate');
                $form->get('data')->remove('disqualificationLength');
                break;
            default:
                break;
        }

        return $form;
    }

    /**
     * Process load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        $returnData = array(
            'id' => $data['id'],
        );
        $fieldsets = ['dataLicencesCurrent', 'dataLicencesApplied', 'dataLicencesRevoked', 'dataLicencesRefused',
            'dataLicencesPublicInquiry', 'dataLicencesDisqualified', 'dataLicencesHeld'];
        $fields = ['currentLicence', 'appliedForLicence', 'revokedLicence', 'refusedLicence',
            'publicInquiryLicence', 'disqualifiedLicence', 'heldLicence'];

        for ($i = 0; $i < count($fieldsets); $i++) {
            if ($data[$fields[$i]] == 'Y') {
                $returnData[$fieldsets[$i]][$fields[$i]] = 'Y';
            } elseif ($data[$fields[$i]] == 'N') {
                $returnData[$fieldsets[$i]][$fields[$i]] = 'N';
            } else {
                $returnData[$fieldsets[$i]][$fields[$i]] = '';
            }
        }

        $returnData['dataLicencesCurrent']['version'] = $data['version'];
        return $returnData;
    }

    /**
     * Add current licence
     */
    public function tableLicencesCurrentAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit current licence
     */
    public function tableLicencesCurrentEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete current licence
     */
    public function tableLicencesCurrentDeleteAction()
    {
        return $this->delete();
    }

    /**
     * Add applied licence
     */
    public function tableLicencesAppliedAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit applied licence
     */
    public function tableLicencesAppliedEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete applied licence
     */
    public function tableLicencesAppliedDeleteAction()
    {
        return $this->delete();
    }

    /**
     * Add refused licence
     */
    public function tableLicencesRefusedAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit refused licence
     */
    public function tableLicencesRefusedEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete refused licence
     */
    public function tableLicencesRefusedDeleteAction()
    {
        return $this->delete();
    }

    /**
     * Add revoked licence
     */
    public function tableLicencesRevokedAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit revoked licence
     */
    public function tableLicencesRevokedEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete revoked licence
     */
    public function tableLicencesRevokedDeleteAction()
    {
        return $this->delete();
    }

    /**
     * Add public inquiry licence
     */
    public function tableLicencesPublicInquiryAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit public inquiry licence
     */
    public function tableLicencesPublicInquiryEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete public inquiry licence
     */
    public function tableLicencesPublicInquiryDeleteAction()
    {
        return $this->delete();
    }

    /**
     * Add disqualified licence
     */
    public function tableLicencesDisqualifiedAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit disqualified licence
     */
    public function tableLicencesDisqualifiedEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete disqualified licence
     */
    public function tableLicencesDisqualifiedDeleteAction()
    {
        return $this->delete();
    }

    /**
     * Add held licence
     */
    public function tableLicencesHeldAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit held licence
     */
    public function tableLicencesHeldEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete held licence
     */
    public function tableLicencesHeldDeleteAction()
    {
        return $this->delete();
    }
}
