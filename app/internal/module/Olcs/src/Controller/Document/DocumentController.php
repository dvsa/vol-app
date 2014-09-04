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
    const TMP_STORAGE_PATH = 'tmp/documents';

    public function getContentStore()
    {
        return $this->getServiceLocator()->get('ContentStore');
    }

    public function getDocumentService()
    {
        return $this->getServiceLocator()->get('Document');
    }

    public function downloadAction()
    {
        $contentStore = $this->getContentStore();
        $doc = $contentStore->read($this->params()->fromRoute('path'));
        // @TODO render file response
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaders(
            array(
                "Content-type" => "application/rtf",
                "Content-Disposition: attachment; filename=" . $filename . ".rtf"
            )
        );

        $response->setContent($documentData['documentData']);
        return $response;
    }

    public function listTemplateBookmarksAction()
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
            ['id' => $this->params()->fromRoute('id')],
            $bundle
        );

        $bookmarks = $result['docTemplateBookmarks'];

        $form = new \Zend\Form\Form();

        $fieldset = new \Zend\Form\Fieldset();
        $fieldset->setLabel('documents.bookmarks');
        $fieldset->setName('bookmarks');

        $form->add($fieldset);

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

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form-simple');
        $view->setTerminal(true);

        return $view;
    }
}
