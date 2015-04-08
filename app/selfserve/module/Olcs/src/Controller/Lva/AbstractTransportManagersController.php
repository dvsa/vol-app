<?php

/**
 * Abstract Transport Managers Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractTransportManagersController as CommonAbstractTmController;
use Common\Controller\Traits\GenericUpload;
use Zend\View\Model\ViewModel;

/**
 * Abstract Transport Managers Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTransportManagersController extends CommonAbstractTmController
{
    use GenericUpload;

    /**
     * Store the tmId
     */
    protected $tmId;

    /**
     * Details page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function detailsAction()
    {
        $request = $this->getRequest();
        $childId = $this->params('child_id');

        $transportManagerApplicationData = $this->getServiceLocator()->get('Entity\TransportManagerApplication')
            ->getTransportManagerDetails($childId);

        $this->tmId = $transportManagerApplicationData['transportManager']['id'];

        $postData = (array)$request->getPost();
        $formData = $this->formatFormData($transportManagerApplicationData, $postData);

        $form = $this->getDetailsForm()->setData($formData);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $hasProcessedAddressLookup = $formHelper->processAddressLookupForm($form, $request);

        $hasProcessedFiles = $this->processFiles(
            $form,
            'details->certificate',
            array($this, 'processCertificateUpload'),
            array($this, 'deleteFile'),
            array($this, 'getCertificates')
        );

        if (!$hasProcessedAddressLookup && !$hasProcessedFiles && $request->isPost()) {

            $submit = true;

            // If we are saving, but not submitting
            if ($this->isButtonPressed('save')) {
                $submit = false;
                $formHelper->disableValidation($form->getInputFilter());
            }

            if ($form->isValid()) {

                // Save the data

                // If submitting
                if ($submit) {
                    // Update status
                }
            }
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');

        $tmHeaderData = $this->getServiceLocator()->get('Entity\Application')->getTmHeaderData($this->getIdentifier());

        $params = [
            'subTitle' => $translationHelper
                ->translateReplace(
                    'markup-tm-details-sub-title',
                    [
                        $tmHeaderData['goodsOrPsv']['description'],
                        $tmHeaderData['licence']['licNo'],
                        $tmHeaderData['id']
                    ]
                )
        ];

        $layout = $this->render('transport_managers-details', $form, $params);

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details');

        return $layout;
    }

    /**
     * Get transport manager certificates
     *
     * @return array
     */
    public function getCertificates()
    {
        return $this->getServiceLocator()->get('Helper\TransportManager')->getCertificateFiles($this->tmId);
    }

    /**
     * Handle the upload of transport manager certificates
     *
     * @param array $file
     * @return array
     */
    public function processCertificateUpload($file)
    {
        $data = $this->getServiceLocator()->get('Helper\TransportManager')
            ->getCertificateFileData($this->tmId, $file);

        return $this->uploadFile($file, $data);
    }

    protected function formatFormData($data, $postData)
    {
        $contactDetails = $data['transportManager']['homeCd'];
        $person = $contactDetails['person'];

        if (!empty($postData)) {
            $formData = $postData;
        } else {
            $formData = [
                'details' => [
                    'emailAddress' => $contactDetails['emailAddress'],
                    'birthPlace' => $person['birthPlace']
                ],
                'homeAddress' => $contactDetails['address'],
                'workAddress' => $data['transportManager']['workCd']['address']
            ];
        }

        $formData['details']['name'] = $person['forename'] . ' ' . $person['familyName'];
        $formData['details']['birthDate'] = date('d/m/Y', strtotime($person['birthDate']));

        return $formData;
    }

    protected function getDetailsForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')->createForm('Lva\TransportManagerDetails');
    }

    /**
     * Awaiting signature page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function awaitingSignatureAction()
    {
        return $this->renderPlaceHolder();
    }

    /**
     * TM signed page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tmSignedAction()
    {
        return $this->renderPlaceHolder();
    }

    /**
     * Operator signed page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function operatorSignedAction()
    {
        return $this->renderPlaceHolder();
    }

    /**
     * Post Application page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function postalApplicationAction()
    {
        return $this->renderPlaceHolder();
    }

    /**
     * Render place holder page
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function renderPlaceHolder()
    {
        $view = new ViewModel();
        $view->setTemplate('pages/placeholder');

        return $this->renderView($view);
    }
}
