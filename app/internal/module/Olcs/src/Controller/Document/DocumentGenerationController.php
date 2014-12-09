<?php

/**
 * Document Generation Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;

/**
 * Document Generation Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentGenerationController extends AbstractDocumentController
{
    /**
     * Labels for empty select options
     */
    const EMPTY_LABEL = 'Please select';

    /**
     * how to map route param types to category names
     */
    private $categoryMap = [
        'licence' => 'Licensing',
        'application' => 'Licensing',
        'case' => 'Licensing',
    ];

    /**
     * Not the prettiest bundle, but what we ultimately want
     * are the all the DB paragraphs availabe for a given template,
     * grouped into bookmarks
     *
     * The relationships here involve two many-to-many relationships
     * to keep bookmarks and paragraphs decoupled from templates, which
     * translates into a fairly nested bundle query
     */
    private $templateBundle = [
        'properties' => ['docTemplateBookmarks'],
        'children' => [
            'docTemplateBookmarks' => [
                'properties' => ['docBookmark'],
                'children' => [
                    'docBookmark' => [
                        'properties' => ['name', 'description'],
                        'children' => [
                            'docParagraphBookmarks' => [
                                'properties' => ['docParagraph'],
                                'children' => [
                                    'docParagraph' => [
                                        'properties' => ['id', 'paraTitle']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    protected function alterFormBeforeValidation($form)
    {
        $categories = $this->getListDataFromBackend(
            'Category',
            ['isDocCategory' => true],
            'description',
            'id',
            false
        );

        $defaultData = [
            'details' => [
                'category' => $this->getDefaultCategory($categories)
            ]
        ];
        $data = [];
        $filters = [];
        $subCategories = ['' => self::EMPTY_LABEL];
        $docTemplates = ['' => self::EMPTY_LABEL];

        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();
        } elseif ($this->params('tmpId')) {
            $data = $this->fetchTmpData();
            $this->removeTmpData();
        }

        $data = array_merge($defaultData, $data);

        $details = isset($data['details']) ? $data['details'] : [];

        $filters['category'] = $details['category'];

        $subCategories = $this->getListDataFromBackend(
            'DocumentSubCategory',
            $filters
        );

        if (isset($details['documentSubCategory'])) {
            $filters['documentSubCategory'] = $details['documentSubCategory'];
            $docTemplates = $this->getListDataFromBackend(
                'DocTemplate',
                $filters
            );
        }

        $selects = [
            'details' => [
                'category' => $categories,
                'documentSubCategory' => $subCategories,
                'documentTemplate' => $docTemplates
            ]
        ];

        foreach ($selects as $fieldset => $inputs) {
            foreach ($inputs as $name => $options) {
                $form->get($fieldset)
                    ->get($name)
                    ->setValueOptions($options);
            }
        }

        if (isset($details['documentTemplate'])) {
            $this->addTemplateBookmarks(
                $details['documentTemplate'],
                $form->get('bookmarks')
            );
        }

        $form->setData($data);

        return $form;
    }

    public function generateAction()
    {
        $form = $this->generateForm('generate-document', 'processGenerate');

        $this->loadScripts(['generate-document']);

        $view = new ViewModel(['form' => $form]);

        $view->setTemplate('form-simple');
        return $this->renderView($view, 'Generate letter');
    }

    public function processGenerate($data)
    {
        $templateId = $data['details']['documentTemplate'];
        $template = $this->makeRestCall(
            'DocTemplate',
            'GET',
            $templateId,
            [
                'properties' => ['document'],
                'children' => [
                    'document' => [
                        'properties' => ['identifier']
                    ]
                ]
            ]
        );

        $identifier = $template['document']['identifier'];

        $routeParams = $this->params()->fromRoute();

        $queryData = array_merge(
            $data,
            $routeParams,
            [
                'user' => $this->getLoggedInUser()
            ]
        );

        // we need to link certain documents to multiple IDs
        switch ($routeParams['type']) {
            case 'application':
                $queryData['licence'] = $this->getLicenceIdForApplication();
                break;
            case 'case':
                $queryData['licence'] = $this->getLicenceIdForCase();
                break;
            default:
                break;
        }

        /**
         * 1) read the template from the content store
         */
        $file = $this->getContentStore()->read($identifier);

        /**
         * 2) Pass the file into the doc service to extract the relevant
         *    bookmarks out of the file data and return an array of queries
         *    we need to answer in order to populate those bookmarks
         */
        $query = $this->getDocumentService()->getBookmarkQueries(
            $file,
            $queryData
        );

        /**
         * 3) Pass those queries into a custom backend endpoint which knows how to
         *    fetch data for multiple different entities at once and respects the
         *    keys to which they relate (e.g. doesn't trash the bookmark keys)
         */
        $result = $this->makeRestCall('BookmarkSearch', 'GET', [], $query);

        /**
         * 4) We've now got all our dynamic data which we can feedback into
         *    our bookmarks to actually replace tokens with data. This will
         *    give us back a string of content which we can then save
         */
        $content = $this->getDocumentService()->populateBookmarks(
            $file,
            $result
        );

        /**
         * 5) All done; we can now persist our generated document
         *    to a temporary store. We also want to save some metadata
         *    so we can re-populate this form should we come back to it
         */
        $details = json_encode(
            [
                'details' => $data['details'],
                'bookmarks' => $data['bookmarks']
            ]
        );
        $meta = [self::METADATA_KEY => $details];

        $uploader = $this->getUploader();
        $uploader->setFile(
            [
                'content' => $content,
                'meta'    => $meta
            ]
        );

        $storedFile = $uploader->upload(self::TMP_STORAGE_PATH);

        // we don't know what params are needed to satisfy this type's
        // finalise route; so to be safe we supply them all
        $redirectParams = array_merge(
            $routeParams,
            [
                'tmpId' => $storedFile->getIdentifier()
            ]
        );

        return $this->redirectToDocumentRoute($routeParams['type'], 'finalise', $redirectParams);
    }

    public function listTemplateBookmarksAction()
    {
        $form = new \Zend\Form\Form();

        $fieldset = new \Zend\Form\Fieldset();
        $fieldset->setLabel('documents.bookmarks');
        $fieldset->setName('bookmarks');

        $form->add($fieldset);

        $this->addTemplateBookmarks(
            $this->params('id'),
            $fieldset
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form-simple');
        $view->setTerminal(true);

        return $view;
    }

    public function downloadTmpAction()
    {
        return $this->getUploader()->download(
            $this->params('id'),
            $this->params('filename'),
            self::TMP_STORAGE_PATH
        );
    }

    private function addTemplateBookmarks($id, $fieldset)
    {
        if (empty($id)) {
            return;
        }

        $result = $this->makeRestCall(
            'DocTemplate',
            'GET',
            $id,
            $this->templateBundle
        );

        $bookmarks = $result['docTemplateBookmarks'];

        foreach ($bookmarks as $bookmark) {

            $bookmark = $bookmark['docBookmark'];

            $element = new \Common\Form\Elements\InputFilters\MultiCheckboxEmpty;
            $element->setLabel($bookmark['description']);
            $element->setName($bookmark['name']);
            // user-supplied bookmarks are *all* optional
            $element->setOptions(['required' => false]);

            $options = [];
            foreach ($bookmark['docParagraphBookmarks'] as $paragraph) {

                $paragraph = $paragraph['docParagraph'];
                $options[$paragraph['id']] = $paragraph['paraTitle'];
            }
            $element->setValueOptions($options);

            $fieldset->add($element);
        }
    }

    private function getDefaultCategory($categories)
    {
        $name = $this->categoryMap[$this->params('type')];
        return array_search($name, $categories);
    }

    protected function getListDataFromBackend(
        $entity,
        $filters = array(),
        $titleField = '',
        $keyField = '',
        $showAll = self::EMPTY_LABEL
    ) {
        return parent::getListDataFromBackend($entity, $filters, 'description', 'id', $showAll);
    }
}
