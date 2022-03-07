<?php

namespace Olcs\Controller\Operator;

use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Application\CreateApplication;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Controller\Traits;
use Olcs\Data\Mapper\OperatorTransfer as OperatorTransferMapper;
use Laminas\View\Model\ViewModel;

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorController extends AbstractController implements OperatorControllerInterface, LeftViewProvider
{
    use Traits\OperatorControllerTrait,
        Traits\ListDataTrait;

    /**
     * @var string
     */
    protected $subNavRoute;

    /**
     * @var string
     */
    protected $section;

    /**
     * Get Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    /**
     * Redirect to the first menu section
     *
     * @return \Laminas\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('operator/business-details', [], [], true);
    }

    /**
     * Process action - Application
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function newApplicationAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data['details']['receivedDate'] = $this->getServiceLocator()->get('Helper\Date')->getDateObject();
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        /** @var \Laminas\Form\FormInterface $form */
        $form = $formHelper->createForm('NewApplication');
        $form->setData($data);
        $this->alterForm($form, $data);

        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        if ($request->isPost() && $form->isValid()) {
            $data = $form->getData();

            $typeOfLicenceData = $data['type-of-licence'];
            $licenceTypeData = $typeOfLicenceData['licence-type'];
            $licenceType = $licenceTypeData['licence-type'];
            $vehicleType = null;
            $lgvDeclarationConfirmation = 0;

            if (isset($licenceTypeData['ltyp_siContent'])) {
                $siContentData = $licenceTypeData['ltyp_siContent'];
                $vehicleType = $siContentData['vehicle-type'];

                if (isset($siContentData['lgv-declaration']['lgv-declaration-confirmation'])) {
                    $lgvDeclarationConfirmation = $siContentData['lgv-declaration']['lgv-declaration-confirmation'];
                }
            }

            $params = [
                'organisation' => $this->params('organisation'),
                'receivedDate' => $data['details']['receivedDate'],
                'trafficArea' => $data['details']['trafficArea'],
                'appliedVia' => $data['appliedVia'],
                'licenceType' => $licenceType,
                'vehicleType' => $vehicleType,
                'lgvDeclarationConfirmation' => $lgvDeclarationConfirmation
            ];

            if ($data['details']['trafficArea'] === RefData::NORTHERN_IRELAND_TRAFFIC_AREA_CODE) {
                $params['niFlag'] = 'Y';
            } else {
                $params['niFlag'] = 'N';
                $params['operatorType'] = $typeOfLicenceData['operator-type'];
            }
            $dto = CreateApplication::create($params);

            $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);

            /** @var \Common\Service\Cqrs\Response $response */
            $response = $this->getServiceLocator()->get('CommandService')->send($command);

            if ($response->isOk()) {
                return $this->redirect()->toRouteAjax(
                    'lva-application/overview',
                    ['application' => $response->getResult()['id']['application']]
                );
            }

            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addErrorMessage('unknown-error');
        }

        $this->getServiceLocator()->get('Script')->loadFile('forms/type-of-licence-operator');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Create new application');
    }

    /**
     * Alter form
     *
     * @param \Laminas\Form\FormInterface $form Form
     * @param array                    $data Api/Form Data
     *
     * @return void
     */
    protected function alterForm($form, $data)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $formHelper->remove($form, 'type-of-licence->operator-location');
        $formHelper->remove($form, 'type-of-licence->difference');
        $organisationData = $this->getOrganisation($this->params()->fromRoute('organisation'));
        if (isset($organisationData['taValueOptions'])) {
            $form->get('details')
                ->get('trafficArea')
                ->setValueOptions($organisationData['taValueOptions']);
        }

        $isNi = isset($data['details']['trafficArea']) &&
            $data['details']['trafficArea'] === RefData::NORTHERN_IRELAND_TRAFFIC_AREA_CODE;

        if ($isNi) {
            $form->getInputFilter()->get('type-of-licence')->get('operator-type')->setRequired(false);
        }

        $fieldset = $form->get('type-of-licence');
        $licenceTypeFieldset = $fieldset->get('licence-type');

        $operatorType = $fieldset->get('operator-type')->getValue();
        $licenceType = $licenceTypeFieldset->get('licence-type')->getValue();
        $vehicleType = $licenceTypeFieldset->get('ltyp_siContent')->get('vehicle-type')->getValue();

        $isGoods = ($operatorType == RefData::LICENCE_CATEGORY_GOODS_VEHICLE || $isNi);
        if ($isGoods && $licenceType == RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL) {
            if ($vehicleType != RefData::APP_VEHICLE_TYPE_LGV) {
                $form->getInputFilter()->get('type-of-licence')
                    ->get('licence-type')
                    ->get('ltyp_siContent')
                    ->remove('lgv-declaration');
            }
        } else {
            $form->getInputFilter()->get('type-of-licence')->get('licence-type')->remove('ltyp_siContent');
        }
    }

    /**
     * Is Unlicensed
     *
     * @return bool
     */
    protected function isUnlicensed()
    {
        if (empty($this->params('organisation'))) {
            return false;
        }

        // need to determine if this is an unlicensed operator or not
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\Organisation::create(
                [
                    'id' => $this->params('organisation'),
                ]
            )
        );

        $organisation = $response->getResult();

        return $organisation['isUnlicensed'];
    }

    /**
     * Transfer associated entities from one Operator to another
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function mergeAction()
    {
        $organisationId = (int) $this->params()->fromRoute('organisation');

        $sl = $this->getServiceLocator();
        $sl->get(\Olcs\Service\Data\Licence::class)->setOrganisationId($organisationId);

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $organisationData = $this->getOrganisation($organisationId);
            if (!$organisationData) {
                return $this->notFoundAction();
            }

            $data = ['fromOperatorName' => $organisationData['name']];
        }

        $formHelper = $sl->get('Helper\Form');

        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('OperatorMerge');
        $form->setData($data);

        $formHelper->setFormActionFromRequest($form, $request);
        $form->get('toOperatorId')->setAttribute('data-lookup-url', $this->url()->fromRoute('operator-lookup'));

        if ($request->isPost() && $form->isValid()) {
            $formData = $form->getData();
            $toOperatorId = (int) $formData['toOperatorId'];
            $licenceIds   = $formData['licenceIds'];
            $response = $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Organisation\TransferTo::create(
                    [
                        'id' => $organisationId,
                        'receivingOrganisation' => $toOperatorId,
                        'licenceIds' => $licenceIds,
                    ]
                )
            );

            $messages = $response->getResult()['messages'];
            if ($response->isOk()) {
                $sl->get('Helper\FlashMessenger')->addSuccessMessage($messages[count($messages) - 1]);
                return $this->redirect()->toRouteAjax('operator/business-details', ['organisation' => $toOperatorId]);
            } else {
                OperatorTransferMapper::mapFromErrors($form, $messages);
            }
        }

        $sl->get('Script')->loadFile('operator-merge');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Merge operator');
    }

    /**
     * Get Organisation(Operator) data
     *
     * @param int $id operator(organisation) ID
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getOrganisation($id)
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\Organisation::create(['id' => $id])
        );
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting organisation');
        }

        return $response->getResult();
    }

    /**
     * Ajax lookup of organisation name
     *
     * @return \Laminas\View\Model\JsonModel
     */
    public function lookupAction()
    {
        $organisationId = (int) $this->params()->fromQuery('organisation');
        $view = new \Laminas\View\Model\JsonModel();

        $data = $this->getOrganisation($organisationId);
        if (!$data) {
            return $this->notFoundAction();
        }
        $view->setVariables(
            [
                'id' => $data['id'],
                'name' => $data['name'],
            ]
        );

        return $view;
    }
}
