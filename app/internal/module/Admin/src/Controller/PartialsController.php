<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\Partial;
use Admin\Form\Model\Form\TranslationsFilter;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Dvsa\Olcs\Transfer\Query\PartialMarkup\GetList as ListDTO;
use Dvsa\Olcs\Transfer\Query\PartialMarkup\ById as ItemDTO;
use Dvsa\Olcs\Transfer\Command\PartialMarkup\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Query\Language\GetList as GetSupportedLanguages;
use Admin\Data\Mapper\Partial as PartialMapper;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Partial Controller
 */
class PartialsController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/content-management/partials';
    protected $tableName = 'admin-partials';
    protected $tableViewTemplate = 'pages/editable-translations/results-table';
    protected $formClass = Partial::class;
    protected $filterForm = TranslationsFilter::class;

    protected $mapperClass = PartialMapper::class;

    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $updateCommand = UpdateDTO::class;

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions', 'editable-translation-search', 'forms/filter'],
        'editAction' => ['forms/translation-key-modal'],
        'detailsAction' => ['table-actions'],
    ];

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('jsonBaseUrl', $this->url()->fromRoute('admin-dashboard/admin-partials'));
        $this->placeholder()->setPlaceholder('resultsTableTitle', 'Partials');
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
                'navigationTitle' => 'Partials'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');
        return $view;
    }

    /**
     *  Display modal form, or consume POST data for edit translation key form
     *
     * @return array|ViewModel
     */
    public function editAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $commandData = $this->mapperClass::mapFromForm((array)$request->getPost());
            $response = $this->handleCommand(UpdateDTO::create($commandData));
            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($this->editSuccessMessage);
                return $this->redirectTo($response->getResult());
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('Error saving translations');
            }
        }

        $form = $this->getForm(Partial::class);
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
                    'admin-dashboard/admin-partials'
                )
            );

        $form->get('resultsKey')->setValue('partialMarkups');
        $form->get('translationVar')->setValue('markup');

        $this->placeholder()->setPlaceholder('pageTitle', 'Edit Partial');
        $view->setTemplate('pages/editable-translations/edit-translation-key-form');

        return $this->viewBuilder()->buildView($view);
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

    /**
     * Custom details action
     *
     * @return Response|ViewModel
     */
    public function detailsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            if ($postData['action'] == 'Editpartial') {
                return $this->redirect()->toRoute(
                    'admin-dashboard/admin-partials',
                    [
                        'action' => 'edit',
                        'id' => $this->params()->fromRoute('id')
                    ]
                );
            }
        }

        $this->placeholder()->setPlaceholder('contentTitle', 'Partial Details');
        $query = $this->itemDto::create(['id' => $this->params()->fromRoute('id')]);

        $response = $this->handleQuery($query);
        if ($response->isOk()) {
            $data = $response->getResult();
            if (isset($data)) {
                $this->placeholder()->setPlaceholder('details', $data);

                $partialMarkups = is_array($data['partialMarkups']) ? $data['partialMarkups'] : [];
                $markupsTable = $this->table()->buildTable('admin-partial-details', $partialMarkups, []);
                $this->placeholder()->setPlaceholder(
                    'markupsTable',
                    $markupsTable->render()
                );
            } else {
                throw new \RuntimeException('Error loading partial data');
            }
        } elseif ($response->isClientError() || $response->isServerError()) {
            $this->handleErrors($response->getResult());
        }

        return $this->viewBuilder()->buildViewFromTemplate('pages/partials/partial-details');
    }
}
