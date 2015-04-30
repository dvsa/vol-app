<?php

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

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

            $created = $this->getServiceLocator()->get('Entity\Application')
                ->createNew(
                    $this->params('organisation'),
                    array('receivedDate' => $data['receivedDate']),
                    $data['trafficArea']
                );

            return $this->redirect()->toRouteAjax(
                'lva-application/type_of_licence',
                ['application' => $created['application']]
            );
        }

        // unset layout file
        $this->layoutFile = null;

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        return $this->renderView($view, 'Create new application');
    }
}
