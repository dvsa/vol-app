<?php

/**
 * Authorisation Controller
 *
 * External - Licence section
 */
namespace Olcs\Controller\Licence\Details\OperatingCentres;

use Common\Controller\Traits;

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AuthorisationController extends OperatingCentresController
{
    use Traits\GenericEditAction;

    protected $sectionServiceName = 'OperatingCentre\\ExternalLicenceAuthorisation';

    protected $bespokeSubActions = array('add');

    /**
     * Set the form name
     *
     * @var string
     */
    protected $formName = 'application_operating-centres_authorisation';

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->addVariationInfoMessage();

        return $this->renderSection();
    }

    /**
     * Add operating centre
     */
    public function addAction()
    {
        $this->viewTemplateName = 'licence/add-authorisation';

        return $this->renderSection();
    }

    /**
     * Delete sub action
     *
     * @return Response
     */
    public function deleteAction()
    {
        if ($this->getSectionService()->getOperatingCentresCount() === 1
            && $this->getActionId()
        ) {
            $this->getSectionService('TrafficArea')->setTrafficArea(null);
        }

        return $this->delete();
    }

    /**
     * Get the action data bundle
     *
     * @return array
     */
    protected function getActionDataBundle()
    {
        return $this->getSectionService()->getActionDataBundle();
    }

    /**
     * Get the action data map
     *
     * @return array
     */
    protected function getActionDataMap()
    {
        return $this->getSectionService()->getActionDataMap();
    }

    /**
     * Get action service
     *
     * @return string
     */
    protected function getActionService()
    {
        return $this->getSectionService()->getActionService();
    }

    /**
     * Get data map
     *
     * @return array
     */
    protected function getDataMap()
    {
        return $this->getSectionService()->getDataMap();
    }

    /**
     * Get form tables
     *
     * @return array
     */
    protected function getFormTables()
    {
        return $this->getSectionService()->getFormTables();
    }

    /**
     * Get data bundle
     *
     * @return array
     */
    protected function getDataBundle()
    {
        return $this->getSectionService()->getDataBundle();
    }

    /**
     * Get service
     *
     * @return type
     */
    protected function getService()
    {
        return $this->getSectionService()->getService();
    }

    /**
     * Get data for table
     *
     * @param string $id
     */
    protected function getFormTableData($id, $table)
    {
        return $this->getSectionService()->getFormTableData($id);
    }

    /**
     * Alter action form
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function alterActionForm($form)
    {
        return $this->getSectionService()->alterActionForm($form);
    }

    /**
     * Save method
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        return $this->getSectionService()->save($data, $service);
    }

    /**
     * Method called after set form data
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function postSetFormData($form)
    {
        return $this->getSectionService()->postSetFormData($form);
    }

    /**
     * Action save
     *
     * @param array $data
     * @param string $service
     * @return mixed
     */
    protected function actionSave($data, $service = null)
    {
        return $this->getSectionService()->actionSave($data, $service);
    }

    /**
     * Extend the generic process load method
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        return $this->getSectionService()->processLoad($data);
    }

    /**
     * Alter the form
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function alterForm($form)
    {
        return $this->getSectionService()->alterForm(parent::alterForm($form));
    }

    /**
     * Add variation info message
     */
    protected function addVariationInfoMessage()
    {
        $this->addCurrentMessage(
            $this->formatTranslation(
                '%s <a href="' . $this->url()->fromRoute('application-variation') . '">%s</a>',
                array(
                    'variation-application-text',
                    'variation-application-link-text'
                )
            ),
            'info'
        );
    }

    /**
     * Process the action load data
     *
     * @param array $oldData
     */
    protected function processActionLoad($oldData)
    {
        return $this->getSectionService()->processActionLoad($oldData);
    }
}
