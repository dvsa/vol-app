<?php

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
    const ERR_NO_ENTITY_FOR_CATEGORY = 'ERR_NO_ENTITY_FOR_CATEGORY';
    const ERR_ENTITY_NAME_NOT_SETUP = 'ERR_ENTITY_NAME_NOT_SETUP';
    const ERR_NO_DESCRIPTION = 'ERR_NO_DESCRIPTION';

    use GenericRenderView;

    /**
     * Index page
     *
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $prg = $this->prg();

        // If have posted, and need to redirect to get
        if ($prg instanceof \Zend\Http\Response) {
            return $prg;
        }

        $data = $prg;

        //  there is not POST
        if ($prg === false) {
            $data = [
                'details' => [
                    'category' => CategoryDataService::CATEGORY_LICENSING,
                    'subCategory' => CategoryDataService::SCAN_SUB_CATEGORY_CHANGE_OF_ENTITY
                ]
            ];
        }

        $category    = $data['details']['category'];
        $subCategory = $data['details']['subCategory'];

        //  set parameters of Dinamyc Selectors
        $sm = $this->getServiceLocator();

        $sm->get(\Olcs\Service\Data\ScannerSubCategory::class)
            ->setCategory($category);

        $sm->get(\Olcs\Service\Data\SubCategoryDescription::class)
            ->setSubCategory($subCategory);

        //  create form
        $form = $this->createFormWithData($data);

        $sm->get('Script')->loadFile('forms/scanning');

        //  is POST
        if ($prg !== false) {
            $details = $data['details'];
            // if no sub category descriptions, then remove the "description" select element
            if (count($sm->get(\Olcs\Service\Data\SubCategoryDescription::class)->fetchListData()) === 0) {
                $sm->get('Helper\Form')->remove($form, 'details->description');
            } else {
                // else remove the "otherDescription" text element
                $sm->get('Helper\Form')->remove($form, 'details->otherDescription');
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
                    $this->processMessages($response, $form, $category);
                } else {
                    $sm->get('Helper\FlashMessenger')->addSuccessMessage('scanning.message.success');

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
                    $sm->get('Script')->loadFile('scanning-success');
                }
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Scanning');
    }

    /**
     * Process response messages
     *
     * @param \Common\Service\Cqrs\Response $response response
     * @param \Common\Form\Form             $form     form
     * @param int                           $category category
     *
     * @return void
     */
    private function processMessages($response, $form, $category)
    {
        $result = $response->getResult();
        $errors = [
            'details' => []
        ];

        if (isset($result['messages'])) {
            $messages = $result['messages'];

            if (array_key_exists(self::ERR_NO_ENTITY_FOR_CATEGORY, $messages)
                || array_key_exists(self::ERR_ENTITY_NAME_NOT_SETUP, $messages)
                // Temporary fix, if the response was a client error, assume the entity was not found
                || $response->isClientError()
            ) {
                $errors['details']['entityIdentifier'] = ['scanning.error.entity.' . $category];
            }

            if (array_key_exists(self::ERR_NO_DESCRIPTION, $messages)) {
                $errors['details']['description'] = ['scanning.error.description'];
            }
        }

        if (count($errors['details'])) {
            $form->setMessages($errors);
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }
    }

    /**
     * Create form with data
     *
     * @param array $data Data
     *
     * @return \Common\Form\Form
     */
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
