<?php

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

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

        $dto = new \Dvsa\Olcs\Transfer\Command\Application\UpdateTypeOfLicence();
        $dto->exchangeArray(
            [
                'id' => $this->getIdentifier(),
                'version' => $formData['version'],
                'operatorType' => $formData['type-of-licence']['operator-type'],
                'licenceType' => $formData['type-of-licence']['licence-type'],
                'niFlag' => $formData['type-of-licence']['operator-location']
            ]
        );

        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createCommand($dto);

        //$this->getLvaEntityService()->save($data);
        //$this->postSave('type_of_licence');
        $response = $this->getServiceLocator()->get('CommandService')->send($command);

        if ($response->isOk()) {
            return $this->completeSection('type_of_licence');
        }

        if ($response->isClientError()) {
            // @todo
            $form->setMessages($response->getResult());
        }

        if ($response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return $this->renderIndex($form);
    }

    /**
     * @return \Common\Service\Cqrs\Response
     */
    protected function getTypeOfLicence()
    {
        $dto = new \Dvsa\Olcs\Transfer\Query\Application\Application();
        $dto->exchangeArray(['id' => $this->getIdentifier()]);

        $query = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createQuery($dto);

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

        $form = $this->getTypeOfLicenceForm();
        $form->get('form-actions')->remove('saveAndContinue')
            ->get('save')->setLabel('continue.button')->setAttribute('class', 'action--primary large');

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $form->setData($data);

            if ($form->isValid()) {

                $organisationId = $this->getCurrentOrganisationId();
                $ids = $this->getServiceLocator()->get('Entity\Application')->createNew($organisationId);

                $data = $this->formatDataForSave($data);

                $data['id'] = $ids['application'];
                $data['version'] = 1;

                $this->getServiceLocator()->get('Entity\Application')->save($data);

                $this->updateCompletionStatuses($ids['application'], 'type_of_licence');

                $adapter = $this->getAdapter();
                $adapter->createFee($ids['application']);

                return $this->goToOverview($ids['application']);
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('type-of-licence');

        return $this->renderCreateApplication('type_of_licence', $form);
    }
}
