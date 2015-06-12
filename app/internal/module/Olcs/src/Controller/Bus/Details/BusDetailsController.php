<?php

/**
 * Bus Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsController extends BusController
{
    protected $section = 'details';
    protected $subNavRoute = 'licence_bus_details';

    public function editAction()
    {
        $request = $this->getRequest();
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm(
            $this->normaliseFormName($this->formName, true)
        );

        if ($request->isPost()) {
            $data = $request->getPost();

            $form->setData($data);

            if ($form->isValid()) {
                $this->processSave($form->getData()['fields']);
                $this->redirectToIndex();
            } else {
                if (method_exists($this, 'onInvalidPost')) {
                    $this->onInvalidPost($form);
                }
            }
        } else {
            $formData['fields'] = $this->getBusReg();

            foreach ($formData['fields'] as $key => $value) {
                if (isset($value['id'])) {
                    $formData['fields'][$key] = $value['id'];
                }
            }

            $form->setData($formData);

            if ($this->isFromEbsr() || !$this->isLatestVariation()) {
                $form->setOption('readonly', true);
            }
        }

        $view = $this->getView();

        $this->setPlaceholder('form', $form);

        $view->setTemplate('pages/crud-form');

        return $this->renderView($view);
    }

    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            null,
            ['action'=>'edit'],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }
}
