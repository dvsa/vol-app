<?php

namespace Admin\Controller;

use Admin\Data\Mapper\EditableTranslation as EditableTranslationMapper;
use Admin\Form\Model\Form\TranslationKey;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Create as CreateCommand;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Delete as DeleteCommand;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Update as UpdateCommand;
use Dvsa\Olcs\Transfer\Command\TranslationKeyText\Delete as DeleteTranslatedTextCommand;
use Dvsa\Olcs\Transfer\Query\Language\GetList as GetSupportedLanguages;
use Dvsa\Olcs\Transfer\Query\TranslationKey\ById as ItemDTO;
use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList as ListDTO;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

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
    protected $createCommand = CreateCommand::class;
    protected $deleteCommand = DeleteCommand::class;
    protected $formClass = TranslationKey::class;

    protected $mapperClass = EditableTranslationMapper::class;

    protected $detailsViewTemplate = 'pages/editable-translations/translation-key-details';
    protected $detailsViewPlaceholderName = 'details';
    protected $translationsTablePlaceholderName = 'translationsTable';
    protected $locationsTablePlaceholderName = 'locationsTable';
    protected $translationsTableName = 'translation-key-texts';
    protected $detailsContentTitle = 'Editable Translations';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' =>    ['editable-translation-search', 'forms/filter', 'table-actions'],
        'detailsAction' =>  ['table-actions'],
        'editkeyAction' =>  ['forms/translation-key-modal'],
        'addAction' =>      ['forms/translation-key-modal']
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
     * @return \Laminas\Http\Response|ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('translationSearch', urldecode((string) $this->params()->fromQuery('translationSearch')));
        $this->placeholder()->setPlaceholder('resultsTableTitle', 'Editable Translations');
        $this->placeholder()->setPlaceholder('jsonBaseUrl', $this->url()->fromRoute('admin-dashboard/admin-editable-translations'));
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
     * @param  array $parameters
     * @return array
     */
    #[\Override]
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['translationSearch'] = urldecode((string) $this->params()->fromQuery('translationSearch'));
        return $parameters;
    }

    /**
     *  Display modal form, or consume POST data for edit translation key form
     *
     * @return array|ViewModel
     */
    public function editkeyAction($addedit = 'edit')
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $commandData = $this->mapperClass::mapFromForm((array)$request->getPost());
            $cmdHandler = $addedit == 'edit' ? UpdateCommand::class : CreateCommand::class;

            $response = $this->handleCommand($cmdHandler::create($commandData));

            $result = $response->getResult();
            if ($response->isOk()) {
                $this->flashMessengerHelperService->addSuccessMessage($this->editSuccessMessage);
                return $this->redirectTo($response->getResult());
            } else {
                $message = isset($result['messages']) ? implode('<br />', $result['messages']) : 'Error saving translations';
                $this->flashMessengerHelperService->addErrorMessage($message);
            }
        }

        $form = $this->setupAddEditForm($addedit);

        $view = new ViewModel(
            [
                'form' => $form
            ]
        );
        $view->setTemplate('pages/editable-translations/edit-translation-key-form');

        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Setup form for add/edit dialog
     *
     * @param  $addEdit
     * @return mixed
     */
    private function setupAddEditForm($addEdit)
    {
        $form = $this->getForm(TranslationKey::class);
        if ($addEdit == 'edit') {
            $form->get('fields')->get('id')->setValue($this->params()->fromRoute('id'));
        }

        $form->get('jsonUrl')
            ->setValue(
                $this->url()->fromRoute(
                    'admin-dashboard/admin-editable-translations'
                )
            );

        $form->get('resultsKey')->setValue('translationKeyTexts');
        $form->get('translationVar')->setValue('translatedText');
        $form->get('addedit')->setValue($addEdit);

        $this->placeholder()->setPlaceholder('pageTitle', ucfirst((string) $addEdit) . ' Translation Key');

        return $form;
    }

    #[\Override]
    public function addAction()
    {
        return $this->editkeyAction('add');
    }


    /**
     * @return \Olcs\Mvc\Controller\Plugin\Confirm|Response|ViewModel
     */
    public function subdeleteAction()
    {
        $confirm = $this->confirm($this->deleteConfirmMessage, false, $this->params()->fromRoute('subid'), null, null);
        if ($confirm instanceof ViewModel) {
            $this->placeholder()->setPlaceholder('pageTitle', "Delete Translation Key Text?");
            return $this->viewBuilder()->buildView($confirm);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            $response = $this->handleCommand(DeleteTranslatedTextCommand::create(['id' => $postData['custom']]));
            if (!$response->isOk()) {
                $this->handleErrors($response->getResult());
            }
            return $this->redirect()->toRouteAjax(
                'admin-dashboard/admin-editable-translations',
                [
                    'action' => 'details',
                    'id' => $this->params()->fromRoute('id')
                ]
            );
        }

        return $confirm;
    }

    /**
     * Custom details action to set dual tables
     *
     * @return Response|ViewModel
     */
    #[\Override]
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
            } elseif ($postData['action'] == 'DeleteText') {
                return $this->redirect()->toRoute(
                    'admin-dashboard/admin-editable-translations',
                    [
                        'action' => 'subdelete',
                        'id' => $this->params()->fromRoute('id'),
                        'subid' => $postData['id']
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
