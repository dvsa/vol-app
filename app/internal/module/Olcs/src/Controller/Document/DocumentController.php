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
    const TMP_STORAGE_PATH  = 'tmp/documents';
    const FULL_STORAGE_PATH = 'documents';

    public function getContentStore()
    {
        return $this->getServiceLocator()->get('ContentStore');
    }

    public function getDocumentService()
    {
        return $this->getServiceLocator()->get('Document');
    }

    public function getUploader()
    {
        return $this->getServiceLocator()
            ->get('FileUploader')
            ->getUploader('ContentStore');
    }

    public function downloadTmpAction()
    {
        $filePath = $this->params()->fromRoute('path');
        $fullPath = self::TMP_STORAGE_PATH . '/' . $filePath;

        return $this->getUploader()->download($fullPath, $filePath . '.rtf'); // @TODO address this, inside the uploader I guess?
    }

    public function downloadAction()
    {
        $fileName = $this->params()->fromRoute('filename');
        $result = $this->makeRestCall(
            'Document',
            'GET',
            ['id' => $this->params()->fromRoute('id')],
            [
                'properties' => ['identifier', 'filename']
            ]
        );

        if ($fileName !== $result['filename']) {
            throw new \Exception('handle properly'); // @TODO
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
            $this->params()->fromRoute('id'),
            $fieldset
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form-simple');
        $view->setTerminal(true);

        return $view;
    }

    protected function addTemplateBookmarks($id, $fieldset)
    {
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

            $element = new \Zend\Form\Element\MultiCheckbox();
            $element->setLabel($bookmark['description']);
            $element->setName($bookmark['name']);

            $options = [];
            foreach ($bookmark['docParagraphBookmarks'] as $paragraph) {

                $paragraph = $paragraph['docParagraph'];
                $options[$paragraph['id']] = $paragraph['paraTitle'];
            }
            $element->setValueOptions($options);

            $fieldset->add($element);
        }
    }
}
