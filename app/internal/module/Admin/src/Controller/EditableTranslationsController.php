<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\TranslationKey;
use Admin\Form\Model\Form\TranslationsFilter;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList as ListDTO;
use Dvsa\Olcs\Transfer\Query\TranslationKey\ById as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Language\GetList as GetSupportedLanguages;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Update as UpdateCommand;
use Admin\Data\Mapper\EditableTranslation as EditableTranslationMapper;

/**
 * Editable Translations Controller
 */
class EditableTranslationsController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/content-management/editable-translations';

    protected $tableName = 'admin-editable-translations';
    protected $tableViewTemplate = 'pages/editable-translations/results-table';
    protected $hasMultiDelete = false;

    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id'];

    protected $updateCommand = UpdateCommand::class;
    protected $formClass = TranslationKey::class;
    protected $filterForm = TranslationsFilter::class;

    protected $mapperClass = EditableTranslationMapper::class;

    protected $detailsViewTemplate = 'pages/editable-translations/translation-key-details';
    protected $detailsViewPlaceholderName = 'details';
    protected $translationsTablePlaceholderName = 'translationsTable';
    protected $locationsTablePlaceholderName = 'locationsTable';
    protected $translationsTableName = 'translation-key-texts';
    protected $locationsTableName = 'translation-key-details';
    protected $detailsContentTitle = 'Editable Translations';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['editable-translation-search', 'forms/filter'],
        'detailsAction' => ['table-actions'],
        'editkeyAction' => ['forms/translation-key-modal']
    ];

    protected $redirectConfig = [
        'editkey' => [
            'action' => 'details',
            'routeMap' => [
                'id' => 'id'
            ],
            'reUseParams' => true
        ],
    ];

    /**
     * Index override to set search term placeholder
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('translationSearch', urldecode($this->params()->fromQuery('translationSearch')));
        return parent::indexAction();
    }

    /**
     * @return JsonModel
     */
    public function xhrsearchAction()
    {
        $response = $this->handleQuery(
            ListDTO::create(
                [
                    'limit' => 10,
                    'order' => 'asc',
                    'page' => 1,
                    'sort' => 'id',
                    'translationSearch' => $this->params()->fromQuery('translationSearch'),
                    'category' => $this->params()->fromQuery('category'),
                    'subCategory' => $this->params()->fromQuery('subCategory'),
                ]
            )
        );
        return new JsonModel($response->getResult());
    }

    /**
     * Left View setting
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/content-management',
                'navigationTitle' => 'Editable Translations'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');
        return $view;
    }

    /**
     * Add search term to list DTO
     *
     * @param array $parameters
     * @return array
     */
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['translationSearch'] = urldecode($this->params()->fromQuery('translationSearch'));
        return $parameters;
    }

    /**
     *  Display modal form, or consume POST data for edit translation key form
     *
     * @return array|ViewModel
     */
    public function editkeyAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $commandData = $this->mapperClass::mapFromForm((array)$request->getPost());
            $response = $this->handleCommand(UpdateCommand::create($commandData));
            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($this->editSuccessMessage);
                return $this->redirectTo($response->getResult());
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('Error saving translations');
            }
        }

        $form = $this->getForm(TranslationKey::class);
        $view = new ViewModel(
            [
                'form' => $form
            ]
        );

        $response = $this->handleQuery(
            ItemDTO::create(
                [
                    'id' => $this->params()->fromRoute('id'),
                ]
            )
        );

        $returnData = $response->getResult();
        if (isset($returnData['error'])) {
            $this->getResponse()->setStatusCode(422);
            unset($returnData['error']);
        }

        $form->get('fields')->get('id')->setValue($this->params()->fromRoute('id'));

        $form->get('jsonUrl')
            ->setValue(
                $this->url()->fromRoute(
                    'admin-dashboard/admin-editable-translations'
                )
            );

        $this->placeholder()->setPlaceholder('pageTitle', 'Edit Translation Key');
        $view->setTemplate('pages/editable-translations/edit-translation-key-form');

        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Custom details action to set dual tables
     *
     * @return Response|ViewModel
     */
    public function detailsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            if ($postData['action'] == 'Edittexts') {
                return $this->redirect()->toRoute(
                    'admin-dashboard/admin-editable-translations',
                    [
                        'action' => 'editkey',
                        'id' => $this->params()->fromRoute('id')
                    ]
                );
            }
        }

        $this->placeholder()->setPlaceholder('contentTitle', $this->detailsContentTitle);
        $query = $this->itemDto::create(['id' => $this->params()->fromRoute('id')]);

        $response = $this->handleQuery($query);
        if ($response->isOk()) {
            $data = $response->getResult();
            if (isset($data)) {
                $this->placeholder()->setPlaceholder($this->detailsViewPlaceholderName, $data);

                $translationKeyTexts = is_array($data['translationKeyTexts']) ? $data['translationKeyTexts'] : [];
                $translationsTable = $this->table()->buildTable($this->translationsTableName, $translationKeyTexts, []);
                $this->placeholder()->setPlaceholder(
                    $this->translationsTablePlaceholderName,
                    $translationsTable->render()
                );

                $translationKeyCategoryLinks = is_array($data['translationKeyCategoryLinks']) ? $data['translationKeyCategoryLinks'] : [];
                $locationsTable = $this->table()->buildTable($this->locationsTableName, $translationKeyCategoryLinks, []);
                $this->placeholder()->setPlaceholder(
                    $this->locationsTablePlaceholderName,
                    $locationsTable->render()
                );
            } else {
                throw new \RuntimeException('Error loading translation key data');
            }
        } elseif ($response->isClientError() || $response->isServerError()) {
            $this->handleErrors($response->getResult());
        }

        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }

    /**
     * @return JsonModel
     */
    public function gettextAction()
    {
        $response = $this->handleQuery(
            ItemDTO::create(
                [
                    'id' => $this->params()->fromRoute('id'),
                ]
            )
        );
        return new JsonModel($response->getResult());
    }

    /**
     * @return JsonModel
     */
    public function languagesAction()
    {
        $supportedLanguages = $this->handleQuery(
            GetSupportedLanguages::create([])
        );

        return new JsonModel($supportedLanguages->getResult());
    }
}
