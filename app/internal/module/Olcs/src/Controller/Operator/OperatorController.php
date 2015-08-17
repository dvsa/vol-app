<?php

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Command\Application\CreateApplication;
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits;
use Zend\View\Model\ViewModel;

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorController extends OlcsController\CrudAbstract implements
    OlcsController\Interfaces\OperatorControllerInterface
{
    use Traits\OperatorControllerTrait;

    /**
     * @var string
     */
    protected $pageLayout = 'operator-section';

    /**
     * @var string
     */
    protected $layoutFile = 'layout/operator-subsection';

    /**
     * @var string
     */
    protected $subNavRoute;

    /**
     * @var string
     */
    protected $section;

    /**
     * Redirect to the first menu section
     *
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('operator/business-details', [], [], true);
    }

    public function newApplicationAction()
    {
        $this->pageLayout = null;

        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data['receivedDate'] = $this->getServiceLocator()->get('Helper\Date')->getDateObject();
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('NewApplication');
        $form->setData($data);

        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        if ($request->isPost() && $form->isValid()) {

            $data = $form->getData();

            $dto = CreateApplication::create(
                [
                    'organisation' => $this->params('organisation'),
                    'receivedDate' => $data['receivedDate'],
                    'trafficArea' => $data['trafficArea']
                ]
            );

            $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);

            /** @var \Common\Service\Cqrs\Response $response */
            $response = $this->getServiceLocator()->get('CommandService')->send($command);

            if ($response->isOk()) {
                return $this->redirect()->toRouteAjax(
                    'lva-application/type_of_licence',
                    ['application' => $response->getResult()['id']['application']]
                );
            }

            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addErrorMessage('unknown-error');
        }

        // unset layout file
        $this->layoutFile = null;

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        return $this->renderView($view, 'Create new application');
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $organisationId = $this->params('organisation');

        if (!empty($organisationId)) {
            $this->pageLayout = $this->isUnlicensed() ? 'unlicensed-operator-section' : 'operator-section';
        }

        return parent::onDispatch($e);
    }

    protected function isUnlicensed()
    {
        if (empty($this->params('organisation'))) {
            return;
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
}
