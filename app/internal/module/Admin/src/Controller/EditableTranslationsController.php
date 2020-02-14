<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\EditableTranslationSearch as EditableTranslationSearchForm;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList as ListDTO;
use Dvsa\Olcs\Transfer\Query\TranslationKey\ById as ItemDTO;
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

    protected $searchFormClass = EditableTranslationSearchForm::class;
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
        'indexAction' => ['table-actions'],
    ];

    /**
     * Index override to set search term placeholder
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('translatedText', urldecode($this->params()->fromQuery('translatedText')));
        return parent::indexAction();
    }

    /**
     * Search action - display form, and redirect to index action on POST
     *
     * @return ViewModel|Response
     */
    public function searchAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            return $this->redirect()->toRoute(
                'admin-dashboard/admin-editable-translations',
                [
                    'action' => 'index'
                ],
                [
                    'query' => [
                        'translatedText' => urlencode($postData['fields']['translatedText'])
                    ]
                ]
            );
        }

        $form = $this->getForm($this->searchFormClass);
        $view = new ViewModel(
            [
                'form' => $form
            ]
        );
        $this->placeholder()->setPlaceholder('pageTitle', $this->detailsContentTitle);
        $view->setTemplate('pages/editable-translations/search-content-form');
        return $this->viewBuilder()->buildView($view);
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
        $parameters['translatedText'] = urldecode($this->params()->fromQuery('translatedText'));
        return $parameters;
    }

    /**
     * Custom details action to set dual tables
     *
     * @return array|ViewModel
     */
    public function detailsAction()
    {
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
}
