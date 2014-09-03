<?php

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Dvsa\Jackrabbit\Data\Object\File;

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class DocumentController extends AbstractController
{
    const TMP_STORAGE_PATH = 'tmp/documents';

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

    protected function alterFormBeforeValidation($form)
    {
        $data = (array)$this->getRequest()->getPost();
        $filters = [];

        if (isset($data['category'])) {
            $filters['category'] = $data['category'];
        }

        if (isset($data['category'])) {
            $filters['documentSubCategory'] = $data['category'];
        }

        $selects = array(
            'details' => array(
                'category' => $this->getListData('Category', ['isDocCategory' => true], 'description'),
                'documentSubCategory' => $this->getListData('DocumentSubCategory', $filters, 'description'),
                'documentTemplate' => $this->getListData('DocTemplate', $filters, 'description')
            )
        );

        foreach ($selects as $fieldset => $inputs) {
            foreach ($inputs as $name => $options) {
                $form->get($fieldset)
                    ->get($name)
                    ->setValueOptions($options);
            }
        }

        return $form;
    }

    public function generateAction()
    {
        $form = $this->generateForm('generate-document', 'processGenerate');

        $form = $this->alterFormBeforeValidation($form);
        $view = new ViewModel(
            [
                'form' => $form,
                'inlineScript' => $this->loadScripts(['generate-document'])
            ]
        );

        // @TODO obviously, don't re-use this template; make a generic one if appropriate
        $view->setTemplate('task/add-or-edit');
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

        // @TODO obviously this is hideously inefficient but is
        // simply to prove the concept for now. Will tidy up
        // before finishing the story; we'll probably end up
        // creating a custom endpoint to fetch all paragraphs
        // by ID in one rest call
        $bookmarks = [];
        foreach ($data['bookmarks'] as $key => $ids) {
            $paragraph = '';
            foreach ($ids as $id) {
                $result = $this->makeRestCall(
                    'DocParagraph',
                    'GET',
                    ['id' => $id],
                    ['properties' => ['paraText']]
                );
                $paragraph .= $result['paraText'];
            }
            $bookmarks[$key] = $paragraph;
        }

        // we've now got our concatenated 'static' bookmarks we can
        // dump into the template. Let's fetch the actual raw template
        // data and do that
        $contentStore = $this->getServiceLocator()->get('ContentStore');
        $template = $contentStore->read($template['document']['identifier']);

        // we've now got our raw content and our bookmarks, so can hand off
        // to our template service / doc gen service to generate the doc
        // pretend for now...
        $generator = $this->getServiceLocator()
            ->get('Document')
            // @NOTE: I really want to make the getGenerator just take a File
            // object, but then it would have to know about the Jackrabbit module...
            // One to ponder
            ->getGenerator($template->getMimeType());

        $contents = $generator->generate($template->getContent(), $bookmarks);

        // write the file to a tmp store

        $tmp = new File();
        $tmp->setContent($contents);
        $tmp->setMimeType($template->getMimeType());

        $response = $contentStore->write(self::TMP_STORAGE_PATH . '/foo', $tmp);

        return $this->redirect()->toRoute(
            'licence/documents/finalise',
            [
                'licence' => $this->params()->fromRoute('licence'),
                'tmpId'   => 'foo'
            ]
        );
    }

    public function finaliseAction()
    {
        $contentStore = $this->getServiceLocator()->get('ContentStore');
        $doc = $contentStore->read(self::TMP_STORAGE_PATH . $this->params()->fromRoute('tmpId'));

        $data = [
            'category' => 'A Category',
            'link' => '<a href=/fooo>Foo</a>'
        ];
        $form = $this->generateFormWithData(
            'finalise-document',
            'processUpload',
            $data
        );
        $view = new ViewModel(
            [
                'form' => $form
            ]
        );
        // @TODO obviously, don't re-use this template; make a generic one if appropriate
        $view->setTemplate('task/add-or-edit');
        return $this->renderView($view, 'Generate letter');
    }

    public function downloadAction()
    {
        $contentStore = $this->getServiceLocator()->get('ContentStore');
        $doc = $contentStore->read($this->params()->fromRoute('path'));
        // @TODO render file response
    }

    public function processUpload($data)
    {
        var_dump($data); die();
        // later...
        /*
        $this->makeRestCall(
            'Document',
            'POST'
        );
        */
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
