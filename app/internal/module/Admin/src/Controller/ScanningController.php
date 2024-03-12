<?php

namespace Admin\Controller;

use Common\Controller\Traits\GenericRenderView;
use Common\Service\Data\CategoryDataService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;
use Laminas\View\Model\ViewModel;
use Olcs\Service\Data\ScannerSubCategory;
use Olcs\Service\Data\SubCategoryDescription;

class ScanningController extends LaminasAbstractActionController
{
    use GenericRenderView;

    public const ERR_NO_ENTITY_FOR_CATEGORY = 'ERR_NO_ENTITY_FOR_CATEGORY';
    public const ERR_ENTITY_NAME_NOT_SETUP = 'ERR_ENTITY_NAME_NOT_SETUP';
    public const ERR_NO_DESCRIPTION = 'ERR_NO_DESCRIPTION';

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected FormHelperService $formHelper;
    protected ScannerSubCategory $scannerSubCategoryDataService;
    protected SubCategoryDescription $subCategoryDescriptionDataService;
    protected ScriptFactory $scriptFactory;

    public function __construct(
        FlashMessengerHelperService $flashMessengerHelper,
        FormHelperService $formHelper,
        ScannerSubCategory $scannerSubCategoryDataService,
        SubCategoryDescription $subCategoryDescriptionDataService,
        ScriptFactory $scriptFactory
    ) {
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->formHelper = $formHelper;
        $this->scannerSubCategoryDataService = $scannerSubCategoryDataService;
        $this->subCategoryDescriptionDataService = $subCategoryDescriptionDataService;
        $this->scriptFactory = $scriptFactory;
    }

    /**
     * Index page
     *
     * @return array|\Laminas\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $prg = $this->prg();

        // If have posted, and need to redirect to get
        if ($prg instanceof \Laminas\Http\Response) {
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

        $this->scannerSubCategoryDataService
            ->setCategory($category);

        $this->subCategoryDescriptionDataService
            ->setSubCategory($subCategory);

        //  create form
        $form = $this->createFormWithData($data);

        $this->scriptFactory->loadFile('forms/scanning');

        //  is POST
        if ($prg !== false) {
            $details = $data['details'];
            // if no sub category descriptions, then remove the "description" select element
            if (count($this->subCategoryDescriptionDataService->fetchListData()) === 0) {
                $this->formHelper->remove($form, 'details->description');
            } else {
                // else remove the "otherDescription" text element
                $this->formHelper->remove($form, 'details->otherDescription');
            }

            // received date not applicable and shouldn't be validated if not a back scan
            $isBackScan = $details['backScan'] == true;
            if (!$isBackScan) {
                $inputs = $form->getInputFilter()->get('details');
                $inputs->remove('dateReceived');
            }

            if ($form->isValid()) {
                $dateReceived = null;
                if ($isBackScan) {
                    $dateReceived = $form->get('details')->get('dateReceived')->getValue();
                }

                /* @var $response \Common\Service\Cqrs\Response */
                $response = $this->handleCommand(
                    \Dvsa\Olcs\Transfer\Command\Scan\CreateSeparatorSheet::create(
                        [
                            'categoryId' => $details['category'],
                            'subCategoryId' => $details['subCategory'],
                            'entityIdentifier' => $details['entityIdentifier'],
                            'descriptionId' => $details['description'] ?? null,
                            'description' => $details['otherDescription'] ?? null,
                            'dateReceived' => $dateReceived,
                        ]
                    )
                );

                if (!$response->isOk()) {
                    $this->processMessages($response, $form, $category);
                } else {
                    $this->flashMessengerHelper->addSuccessMessage('scanning.message.success');

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
                    $this->scriptFactory->loadFile('scanning-success');
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

            if (
                array_key_exists(self::ERR_NO_ENTITY_FOR_CATEGORY, $messages)
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
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
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
        $form = $this->formHelper
            ->createForm('Scanning')
            ->setData($data);

        // @see https://jira.i-env.net/browse/OLCS-6565
        $form->get('form-actions')->remove('cancel');

        return $form;
    }
}
