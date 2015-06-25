<?php

/**
 * Bus Short Notice Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Short;

use Olcs\Controller\Bus\BusController;
use Dvsa\Olcs\Transfer\Query\Bus\ShortNoticeByBusReg as ShortNoticeDto;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateShortNotice as UpdateShortNoticeCommand;

/**
 * Bus Short Notice Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusShortController extends BusController
{
    protected $layoutFile = 'layout/wide-layout';
    protected $section = 'short';
    protected $subNavRoute = 'licence_bus_short';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-short-notice';

    public function editAction()
    {
        $id = $this->params()->fromRoute('busRegId');

        $dto = new ShortNoticeDto();
        $dto->exchangeArray(['id' => $id]);
        $response = $this->handleQuery($dto);

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
            $formData['fields'] = (isset($response->getResult()[0]) ? $response->getResult()[0] : []);

            foreach ($formData['fields'] as $key => $value) {
                if (isset($value['id'])) {
                    $formData['fields'][$key] = $value['id'];
                }
            }

            $form->setData($formData);

            if (!$this->isLatestVariation()) {
                $form->setOption('readonly', true);
            }
        }

        $view = $this->getView();

        $this->setPlaceholder('form', $form);

        $view->setTemplate('pages/crud-form');

        return $this->renderView($view);
    }

    /**
     * Method to save the form data, called when inserting or editing.
     *
     * @param array $data
     * @return array|mixed|\Zend\Http\Response
     */
    public function processSave($data)
    {
        $command = new UpdateShortNoticeCommand();
        $command->exchangeArray($data);
        return $this->handleCommand($command);
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
