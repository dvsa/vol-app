<?php

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class DocumentController extends AbstractController
{
    public $messages = null;

    /*
     * Generates a case list using the DataListPlugin
     */
    public function indexAction()
    {

        $allTemplates = $this->service('Olcs\Template')->get('list');

        $view = new ViewModel(['allTemplates' => $allTemplates]);
        $view->setTemplate('olcs/document/test-templates');
        return $view;
    }

    /**
     * Method to extract and return a template bookmarks
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getTemplateBookmarksAction()
    {

        $country = $this->params('country');
        $format = $this->params('format');
        $templateId = $this->params('template');
        $template_bookmarks = $this->service('Olcs\Template')
            ->get('bookmarks/' . $templateId . '/' . $format . '/' . $country);

        return new JsonModel($template_bookmarks);
    }

    public function generateDocumentAction()
    {
        $country = $this->params('country');
        $templateId = $this->params('templateId');
        $bookmarks = $_POST;
        $documentData = $this->sendPost(
            'Olcs\Document\Generate', array(
                'bookmarks' => $bookmarks,
                'country' => $country,
                'templateId' => $templateId
            )
        );

        return new JsonModel($documentData);
    }

    public function retrieveDocumentAction()
    {
        $filename = $this->params('filename');
        $country = $this->params('country');
        $format = $this->params('format');

        //$serviceData = $this->service('Olcs\Document\Retrieve')->get('retrieve/'.$filename.'/'.$format.'/'.$country);
        $documentData = $this->sendGet(
            'Olcs\Document\Retrieve',
            array(
                'filename' => $filename,
                'format' => $format,
                'country' => $country
            )
        );

        $filename = $documentData['filename'];
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

    public function generateAction()
    {
        $form = $this->getForm('generate-document');

        $filters = [];

        $selects = array(
            'details' => array(
                'category' => $this->getListData('Category', ['isDocCategory' => true], 'description'),
                'documentSubCategory' => $this->getListData('DocumentSubCategory', $filters, 'description')
            )
        );

        foreach ($selects as $fieldset => $inputs) {
            foreach ($inputs as $name => $options) {
                $form->get($fieldset)
                    ->get($name)
                    ->setValueOptions($options);
            }
        }

        $view = new ViewModel(
            [
                'form' => $form,
                'inlineScript' => $this->loadScripts(['generate-document'])
            ]
        );

        $view->setTemplate('task/add-or-edit');
        return $this->renderView($view, 'Generate letter');
    }

    public function finaliseAction()
    {
        // @TODO render a form with a link to the generated
        // document and handle a file upload POST to then update
        // the document we've stored in jack rabbit
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
                            'properties' => ['name'],
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
            $element->setLabel($bookmark['name']);
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
