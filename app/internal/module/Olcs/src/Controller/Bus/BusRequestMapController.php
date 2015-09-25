<?php

namespace Olcs\Controller\Bus;

use Olcs\Service\Data\RequestMap;
use Zend\View\Model\ViewModel;

/**
 * Class BusRequestMapController
 * @package Olcs\Controller\Bus
 */
class BusRequestMapController extends BusController
{
    /**
     * @var string
     */
    protected $section = 'processing';
    /**
     * @var string
     */
    protected $subNavRoute = 'licence_bus_processing';

    public function getLeftView()
    {
        return null;
    }

    /**
     * @return \Zend\Http\Response
     */
    public function requestMapAction()
    {
        /** @var \Zend\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('BusRequestMap', $this->getRequest());

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {

                $data = $form->getData()['fields'];

                /** @var RequestMap $ds */
                $ds = $this->getServiceLocator()->get('DataServiceManager')->get(RequestMap::class);
                $ds->requestMap($this->params()->fromRoute('busRegId'), $data['scale']);

                $this->flashMessenger()->addSuccessMessage('Map created successfully');

                return $this->redirectToRouteAjax('licence/bus-docs', [], [], true);
            }
        }

        $this->setPlaceholder('form', $form);
        $this->setPlaceholder('contentTitle', 'Request Map');

        $view = $this->getView();
        $view->setTemplate('pages/crud-form');

        /** @var static $ctrl */
        $ctrl = $this->getServiceLocator()->get('ControllerManager')->get('BusRequestMapController');

        return $ctrl->renderView($view);
    }
}
