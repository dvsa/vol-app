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
class DocumentGenerationController extends DocumentController
{
    const EMPTY_LABEL = 'Please select';

    private $categoryMap = [
        // type (as set by the route) => default category name
        'licence' => 'Licensing'
    ];

    private function getDefaultCategory($categories)
    {
        $name = $this->categoryMap[$this->params('type')];
        return array_search($name, $categories);
    }

    protected function alterFormBeforeValidation($form)
    {
        $categories = $this->getListData(
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
            $this->getUploader()->remove($this->getTmpPath());
        }

        $data = array_merge($defaultData, $data);

        $details = isset($data['details']) ? $data['details'] : [];

        $filters['category'] = $details['category'];

        $subCategories = $this->getListData(
            'DocumentSubCategory',
            $filters
        );

        if (isset($details['documentSubCategory'])) {
            $filters['documentSubCategory'] = $details['documentSubCategory'];
            $docTemplates = $this->getListData(
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
            ['id' => $templateId],
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
            $file->getMimeType(),
            $file->getContent(),
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
         *    give us back a modified file object which we can then save
         */
        $content = $this->getDocumentService()->populateBookmarks(
            $file->getMimeType(),
            $file->getContent(),
            $result
        );

        $file->setContent($content);

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

        $file->setMetaData(new \ArrayObject([self::METADATA_KEY => $details]));

        $uploader = $this->getUploader();
        $uploader->setFile($file);
        $filename = $uploader->upload(self::TMP_STORAGE_PATH);

        /*
        $redirectParams = $this->params()->fromRoute();
        $redirectParams['tmpId'] = $filename;
         */

        return $this->redirect()->toRoute(
            $routeParams['type'] . '/documents/finalise',
            [
                'tmpId' => $filename
            ]
        );
    }

    protected function getListData(
        $entity,
        $filters = array(),
        $titleField = '',
        $keyField = '',
        $showAll = self::EMPTY_LABEL
    ) {
        return parent::getListData($entity, $filters, 'description', 'id', $showAll);
    }
}
