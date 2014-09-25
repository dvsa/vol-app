<?php

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;
use Olcs\Controller\AbstractController;

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentController extends AbstractController
{
    /**
     * Where to store any temporarily generated documents
     */
    const TMP_STORAGE_PATH = 'tmp/documents';

    /**
     * Where to store finalised documents
     */
    const FULL_STORAGE_PATH = 'documents';

    /**
     * the keyspace where we store our extra metadata about
     * each document in jackrabbit
     */
    const METADATA_KEY = 'data';

    protected $tmpData = [];

    protected function getContentStore()
    {
        return $this->getServiceLocator()->get('ContentStore');
    }

    protected function getDocumentService()
    {
        return $this->getServiceLocator()->get('Document');
    }

    /**
     * @NOTE: declared public in abstract controller, can't reduce
     * visibility
     */
    public function getUploader()
    {
        return $this->getServiceLocator()
            ->get('FileUploader')
            ->getUploader('ContentStore');
    }

    public function downloadTmpAction()
    {
        $fileName = $this->params('filename');
        $fullPath = self::TMP_STORAGE_PATH . '/' . $this->params('id');

        return $this->getUploader()->download($fullPath, $fileName);
    }

    public function downloadAction()
    {
        $fileName = $this->params('filename');
        $result = $this->makeRestCall(
            'Document',
            'GET',
            ['id' => $this->params('id')],
            [
                'properties' => ['identifier', 'filename']
            ]
        );

        if (!$result || $fileName !== $result['filename']) {
            return $this->notFoundAction();
        }

        $fullPath = self::FULL_STORAGE_PATH . '/' . $result['identifier'];

        return $this->getUploader()->download($fullPath, $fileName);
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

    protected function addTemplateBookmarks($id, $fieldset)
    {
        if (empty($id)) {
            return;
        }

        /**
         * Not the prettiest bundle, but what we ultimately want
         * are the all the DB paragraphs availabe for a given template,
         * grouped into bookmarks
         *
         * The relationships here involve two many-to-many relationships
         * to keep bookmarks and paragraphs decoupled from templates, which
         * translates into a fairly nested bundle query
         */
        $bundle = [
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

        $result = $this->makeRestCall(
            'DocTemplate',
            'GET',
            ['id' => $id],
            $bundle
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

    protected function getTmpPath()
    {
        return self::TMP_STORAGE_PATH . '/' . $this->params('tmpId');
    }

    protected function fetchTmpData()
    {
        if (empty($this->tmpData)) {
            $path = $this->getTmpPath();
            $meta = $this->getContentStore()
                ->readMeta($path);

            if ($meta['exists'] === true) {
                $key = 'meta:' . self::METADATA_KEY;

                $this->tmpData = json_decode(
                    $meta['metadata'][$key],
                    true
                );
            }
        }
        return $this->tmpData;
    }

    protected function formatFilename($input)
    {
        return str_replace([' ', '/'], '_', $input);
    }
}
