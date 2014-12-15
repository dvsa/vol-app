<?php
/**
 * Scanning Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Service\Data\CategoryDataService;

/**
 * Scanning Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ScanningController extends AbstractActionController
{
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
            ->setCategory($category);

        $this->getDataService('SubCategoryDescription')
            ->setSubCategory($subCategory);

        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Scanning')
            ->setData($data);

        $this->getServiceLocator()->get('Script')->loadFile('forms/scanning');

        if ($this->getRequest()->isPost()) {

            $details = $data['details'];

            if (isset($details['description'])) {
                $this->getServiceLocator()
                    ->get('Helper\Form')
                    ->remove($form, 'details->otherDescription');
            }

            if ($form->isValid()) {

                $entity = $this->getServiceLocator()
                    ->get('Processing\Entity')
                    ->findEntityForCategory(
                        $details['category'],
                        $details['entityIdentifier']
                    );

                if ($entity === false) {
                    $form->setMessages(
                        [
                            'details' => [
                                'entityIdentifier' => ['scanning.error.entity.' . $category]
                            ]
                        ]
                    );
                } else {
                    $licNo = isset($entity['licNo']) ? $entity['licNo'] : null;

                    $categoryName = $this->getDataService('Category')
                        ->getDescriptionFromId($details['category']);

                    $subCategoryName = $this->getDataService('SubCategory')
                        ->getDescriptionFromId($details['subCategory']);

                    if (isset($details['description'])) {
                        $description = $this->getDataService('SubCategoryDescription')
                            ->getDescriptionFromId($details['description']);
                    } else {
                        $description = $details['otherDescription'];
                    }

                    $entityType = $this->getServiceLocator()
                        ->get('Processing\Entity')
                        ->findEntityNameForCategory($details['category']);

                    $knownValues = [
                        'DOC_CATEGORY_ID_SCAN'        => $details['category'],
                        'DOC_CATEGORY_NAME_SCAN'      => $categoryName,
                        'LICENCE_NUMBER_SCAN'         => $licNo,
                        'LICENCE_NUMBER_REPEAT_SCAN'  => $licNo,
                        'ENTITY_ID_TYPE_SCAN'         => $entityType,
                        'ENTITY_ID_SCAN'              => $entity['id'],
                        'ENTITY_ID_REPEAT_SCAN'       => $entity['id'],
                        'DOC_SUBCATEGORY_ID_SCAN'     => $details['subCategory'],
                        'DOC_SUBCATEGORY_NAME_SCAN'   => $subCategoryName,
                        'DOC_DESCRIPTION_SCAN'        => $description,
                        'DOC_DESCRIPTION_REPEAT_SCAN' => $description
                    ];

                    $docService = $this->getServiceLocator()->get('Helper\DocumentGeneration');

                    $content = $docService->generateFromTemplate(
                        'Scanning_SeparatorSheet',
                        [],
                        $knownValues
                    );

                    $storedFile = $docService->uploadGeneratedContent(
                        $content,
                        'documents',
                        'Scanning Separator Sheet'
                    );

                    $this->getServiceLocator()
                        ->get('PrintScheduler')
                        ->enqueueFile($storedFile, 'Scanning Separator Sheet');

                    $this->getServiceLocator()
                        ->get('Helper\FlashMessenger')
                        ->addSuccessMessage('scanning.message.success');

                    // The AC says sub cat & description dropdowns should be reset to their defaults, but
                    // this presents an issue; description depends on sub category,
                    // but we don't know what the "default" sub category is in order
                    // to re-fetch the correct list of descriptions...
                    $form = $this->getServiceLocator()
                        ->get('Helper\Form')
                        ->createForm('Scanning')
                        ->setData(
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
        $view->setTemplate('form');
        return $this->renderView($view, 'Scanning');
    }

    private function getDataService($service)
    {
        return $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Olcs\Service\Data\\' . $service);
    }
}
