<?php

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerController extends AbstractController implements TransportManagerControllerInterface
{
    /**
     * @var string
     */
    protected $pageLayout = 'transport-manager-section';

    /**
     * Memoize TM details to prevent multiple backend calls with same id
     * @var array
     */
    protected $tmDetailsCache = [];

    /**
     * Redirect to the first menu section
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('transport-manager/details/details', [], [], true);
    }

    /**
     * Redirect to the first menu section
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexProcessingJumpAction()
    {
        return $this->redirect()->toRoute(
            'transport-manager/processing/notes',
            ['action' => null, 'id' => null, 'transportManager' => $this->params()->fromRoute('transportManager')],
            ['code' => '303'],
            false
        );
    }

    /**
     * Get view with TM
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function getViewWithTm($variables = [])
    {
        $tmId = $this->params()->fromRoute('transportManager');
        if ($tmId) {
            $variables['disable'] = false;
        } else {
            $this->pageTitle = $this->getServiceLocator()
                ->get('translator')
                ->translate('internal-transport-manager-new-transport-manager');

            $variables['disable'] = true;
        }

        $variables['section'] = $this->section;

        $view = $this->getView($variables);

        return $view;
    }

    public function getTmDetails($tmId, $bypassCache = false)
    {
        if ($bypassCache || !isset($this->tmDetailsCache[$tmId])) {
             $this->tmDetailsCache[$tmId] = $this->getServiceLocator()
                ->get('Entity\TransportManager')
                ->getTmDetails($tmId);
        }
        return $this->tmDetailsCache[$tmId];
    }

    /**
     * Merge a transport manager
     */
    public function mergeAction()
    {
        $transportManagerId = (int) $this->params()->fromRoute('transportManager');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $tmData = $this->getTransportManager($transportManagerId);
            if (!$tmData) {
                return $this->notFoundAction();
            }
            $data['fromTmName'] = $tmData['id'] .' '.
                $tmData['homeCd']['person']['forename'] .' '.
                $tmData['homeCd']['person']['familyName'];
            if (isset($tmData['users'][0])) {
                $data['fromTmName'] .= ' (associated user: '. $tmData['users'][0]['loginId'] .')';
            }
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('TransportManagerMerge');
        $form->setData($data);
        $formHelper->setFormActionFromRequest($form, $request);
        $form->get('toTmId')->setAttribute('data-lookup-url', $this->url()->fromRoute('transport-manager-lookup'));

        if ($request->isPost() && $form->isValid()) {
            $toTmId = (int) $form->getData()['toTmId'];
            /* @var $response \Common\Service\Cqrs\Response */
            $response = $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Tm\Merge::create(
                    ['id' => $transportManagerId, 'recipientTransportManager' => $toTmId]
                )
            );

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('form.tm-merge.success');

                return $this->redirect()->toRouteAjax('transport-manager', ['transportManager' => $transportManagerId]);
            } elseif ($response->isNotFound()) {
                $formMessages['toTmId'][] = 'form.tm-merge.to-tm-id.validation.not-found';
                $form->setMessages($formMessages);
            } else {
                if (isset($response->getResult()['messages'])) {
                    foreach (array_keys($response->getResult()['messages']) as $key) {
                        $formMessages['toTmId'][] = 'form.tm-merge.to-tm-id.validation.'. $key;
                    }
                    $form->setMessages($formMessages);
                } else {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addErrorMessage('unknown-error');
                }
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('tm-merge');

        // unset layout file
        $this->layoutFile = null;
        $this->pageLayout = null;

        $view = new \Zend\View\Model\ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        return $this->renderView($view, 'Merge transport manager');
    }

    /**
     * Get TransportManager data
     *
     * @param int $id TransportManager ID
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getTransportManager($id)
    {
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(['id' => $id])
        );
        if ($response->isNotFound()) {
            return null;
        }
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting TransportManager');
        }

        return $response->getResult();
    }

    /**
     * Ajax lookup of transport manager name
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function lookupAction()
    {
        $transportManagerId = (int) $this->params()->fromQuery('transportManager');
        $view = new \Zend\View\Model\JsonModel();

        $tmData = $this->getTransportManager($transportManagerId);
        if (!$tmData) {
            return $this->notFoundAction();
        }
        $name = $tmData['homeCd']['person']['forename'] .' '. $tmData['homeCd']['person']['familyName'];
        if (isset($tmData['users'][0])) {
            $name .= ' (associated user: '. $tmData['users'][0]['loginId'] .')';
        }
        $view->setVariables(
            [
                'id' => $tmData['id'],
                'name' => $name,
            ]
        );
        return $view;
    }
}
