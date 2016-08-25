<?php

/**
 * Scanning Controller
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Common\Service\Data\CategoryDataService;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Common\Controller\Traits\GenericRenderView;

/**
 * Scanning Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ScanningController extends ZendAbstractActionController
{
    use GenericRenderView;

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();
        } else {
            $data = [
                'details' => [
                    'category' => CategoryDataService::CATEGORY_LICENSING,
                    'subCategory' => CategoryDataService::SCAN_SUB_CATEGORY_CHANGE_OF_ENTITY
                ]
            ];
        }

        $category    = $data['details']['category'];
        $subCategory = $data['details']['subCategory'];

        $this->getDataService('SubCategory')
            ->setCategory($category)
            ->setIsScanCategory('Y');

        $this->getDataService('SubCategoryDescription')
            ->setSubCategory($subCategory);

        $this->getDataService('Category')
            ->setIsScanCategory('Y');

        $form = $this->createFormWithData($data);

        $this->getServiceLocator()->get('Script')->loadFile('forms/scanning');

        if ($this->getRequest()->isPost()) {

            $details = $data['details'];

            if (isset($details['description'])) {
                $this->getServiceLocator()
                    ->get('Helper\Form')
                    ->remove($form, 'details->otherDescription');
            }

            if ($form->isValid()) {

                /* @var $response \Common\Service\Cqrs\Response */
                $response = $this->handleCommand(
                    \Dvsa\Olcs\Transfer\Command\Scan\CreateSeparatorSheet::create(
                        [
                            'categoryId' => $details['category'],
                            'subCategoryId' => $details['subCategory'],
                            'entityIdentifier' => $details['entityIdentifier'],
                            'descriptionId' => (isset($details['description'])) ? $details['description'] : null,
                            'description' => (isset($details['otherDescription'])) ?
                                $details['otherDescription'] : null,
                        ]
                    )
                );

                if (!$response->isOk()) {
                    $form->setMessages(
                        [
                            'details' => [
                                'entityIdentifier' => ['scanning.error.entity.' . $category]
                            ]
                        ]
                    );
                } else {
                    $this->getServiceLocator()
                        ->get('Helper\FlashMessenger')
                        ->addSuccessMessage('scanning.message.success');

                    // The AC says sub cat & description dropdowns should be reset to their defaults, but
                    // this presents an issue; description depends on sub category,
                    // but we don't know what the "default" sub category is in order
                    // to re-fetch the correct list of descriptions...
                    $form = $this->createFormWithData(
                        [
                            'details' => [
                                'category' => $details['category'],
                                'entityIdentifier' => $details['entityIdentifier']
                            ]
                        ]
                    );

                    // ... so we load in some extra JS which will fire off our cascade
                    // input, which in turn will populate the list of descriptions
                    $this->getServiceLocator()->get('Script')->loadFile('scanning-success');
                }
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Scanning');
    }

    private function getDataService($service)
    {
        return $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Olcs\Service\Data\\' . $service);
    }

    private function createFormWithData($data)
    {
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Scanning')
            ->setData($data);

        // @see https://jira.i-env.net/browse/OLCS-6565
        $form->get('form-actions')->remove('cancel');

        return $form;
    }
}
