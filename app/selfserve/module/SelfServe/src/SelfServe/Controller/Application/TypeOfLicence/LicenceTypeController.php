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
    /**
     * Holds the data
     *
     * @var array
     */
    private $data;

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
     * Alter the type values
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        if (!$this->isPsv()) {

            $licenceTypeElement = $form->get('data')->get('licenceType');

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
