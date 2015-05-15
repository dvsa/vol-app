<?php

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Dvsa\Olcs\Transfer\Command\Application\CreateApplication;
use Dvsa\Olcs\Transfer\Command\Application\UpdateTypeOfLicence;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Zend\Form\Form;
use Common\View\Model\Section;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Zend\Http\Response;
use Common\Data\Mapper\Lva\TypeOfLicence as TypeOfLicenceMapper;

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends Lva\AbstractTypeOfLicenceController
{
    use ApplicationControllerTrait;

    protected $location = 'external';
    protected $lva = 'application';

    /**
     * Application type of licence section
     *
     * @return Response
     */
    public function indexAction()
    {
        $prg = $this->prg();

        // If have posted, and need to redirect to get
        if ($prg instanceof Response) {
            return $prg;
        }

        $form = $this->getServiceLocator()->get('FormServiceManager')
            ->get('lva-application-type-of-licence')
            ->getForm();

        // If we have no data (not posted)
        if ($prg === false) {

            $response = $this->getTypeOfLicence();

            if ($response->isNotFound()) {
                return $this->notFoundAction();
            }

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isOk()) {
                $mapper = new TypeOfLicenceMapper();
                $form->setData($mapper->mapFromResult($response->getResult()));
            }

            return $this->renderIndex($form);
        }

        // If we have posted and have data
        $form->setData($prg);

        // If the form is invalid, render the errors
        if (!$form->isValid()) {
            return $this->renderIndex($form);
        }

        $formData = $form->getData();

        $dto = UpdateTypeOfLicence::create(
            [
                'id' => $this->getIdentifier(),
                'version' => $formData['version'],
                'operatorType' => $formData['type-of-licence']['operator-type'],
                'licenceType' => $formData['type-of-licence']['licence-type'],
                'niFlag' => $formData['type-of-licence']['operator-location']
            ]
        );

        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);

        $response = $this->getServiceLocator()->get('CommandService')->send($command);

        if ($response->isOk()) {
            return $this->completeSection('type_of_licence');
        }

        if ($response->isClientError()) {

            // This means we need confirmation
            if (isset($response->getResult()['messages']['AP-TOL-5'])) {

                $query = $formData['type-of-licence'];
                $query['version'] = $formData['version'];

                return $this->redirect()->toRoute(
                    null,
                    ['action' => 'confirmation'],
                    ['query' => $query],
                    true
                );
            }

            $this->mapErrors($form, $response->getResult()['messages']);
        }

        if ($response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return $this->renderIndex($form);
    }

    public function confirmationAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $query = (array)$this->params()->fromQuery();

            $dto = UpdateTypeOfLicence::create(
                [
                    'id' => $this->getIdentifier(),
                    'version' => $query['version'],
                    'operatorType' => $query['operator-type'],
                    'licenceType' => $query['licence-type'],
                    'niFlag' => $query['operator-location'],
                    'confirm' => true
                ]
            );


            $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')
                ->createCommand($dto);

            $response = $this->getServiceLocator()->get('CommandService')->send($command);

            if ($response->isOk()) {
                return $this->redirect()->toRouteAjax(
                    'lva-application',
                    ['application' => $response->getResult()['id']['application']]
                );
            }

            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addErrorMessage('unknown-error');

            return $this->redirect()->toRouteAjax(null, ['action' => null], [], true);
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('GenericConfirmation');
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        return $this->render(
            'application_type_of_licence_confirmation',
            $form,
            ['sectionText' => 'application_type_of_licence_confirmation_subtitle']
        );
    }

    /**
     * @return \Common\Service\Cqrs\Response
     */
    protected function getTypeOfLicence()
    {
        $query = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createQuery(Application::create(['id' => $this->getIdentifier()]));

        return $this->getServiceLocator()->get('QueryService')->send($query);
    }

    protected function renderIndex($form)
    {
        $this->getServiceLocator()->get('Script')->loadFile('type-of-licence');

        return $this->render('type_of_licence', $form);
    }

    /**
     * Render the section
     *
     * @param string $titleSuffix
     * @param \Zend\Form\Form $form
     * @return \Common\View\Model\Section
     */
    protected function renderCreateApplication($titleSuffix, Form $form = null)
    {
        return new Section(
            [
                'title' => 'lva.section.title.' . $titleSuffix, 'form' => $form,
                'stepX' => '1',
            ]
        );
    }

    /**
     * Create application action
     */
    public function createApplicationAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRouteAjax('dashboard');
        }

        $request = $this->getRequest();

        $form = $this->getServiceLocator()->get('FormServiceManager')
            ->get('lva-application-type-of-licence')
            ->getForm();

        $form->get('form-actions')->remove('saveAndContinue')
            ->get('save')->setLabel('continue.button')->setAttribute('class', 'action--primary large');

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $form->setData($data);

            if ($form->isValid()) {

                $dto = CreateApplication::create(
                    [
                        'organisation' => $this->getCurrentOrganisationId(),
                        'niFlag' => $data['type-of-licence']['operator-location'],
                        'operatorType' => $data['type-of-licence']['operator-type'],
                        'licenceType' => $data['type-of-licence']['licence-type']
                    ]
                );

                $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);

                /** @var \Common\Service\Cqrs\Response $response */
                $response = $this->getServiceLocator()->get('CommandService')->send($command);

                if ($response->isOk()) {
                    return $this->goToOverview($response->getResult()['id']['application']);
                }

                if ($response->isClientError()) {
                    $this->mapErrors($form, $response->getResult()['messages']);
                } else {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addErrorMessage('unknown-error');
                }
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('type-of-licence');

        return $this->renderCreateApplication('type_of_licence', $form);
    }

    protected function mapErrors($form, array $errors)
    {
        $formMessages = [];

        if (isset($errors['licenceType'])) {

            foreach ($errors['licenceType'][0] as $key => $message) {
                $formMessages['type-of-licence']['licence-type'][] = $key;
            }

            unset($errors['licenceType']);
        }

        if (isset($errors['goodsOrPsv'])) {

            foreach ($errors['goodsOrPsv'][0] as $key => $message) {
                $formMessages['type-of-licence']['operator-type'][] = $key;
            }

            unset($errors['licenceType']);
        }

        // @todo might need tweaking
        if (!empty($errors)) {
            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }
}
