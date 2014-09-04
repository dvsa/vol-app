<?php

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Dvsa\Jackrabbit\Data\Object\File;

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class DocumentController extends AbstractDocumentController
{
    const TMP_STORAGE_PATH = 'tmp/documents';

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

        // @NOTE: yes, this will be called automagically when POSTing the
        // form, but we also need it when rendering via a GET too because
        // it actually populates our default category / sub cat / template
        // values as well
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
        $contentStore = $this->getContentStore();
        $template = $contentStore->read($template['document']['identifier']);

        // we've now got our raw content and our bookmarks, so can hand off
        // to our template service / doc gen service to generate the doc
        // pretend for now...
        $generator = $this->getDocumentService()
            // @NOTE: I really want to make the getGenerator just take a File
            // object, but then it would have to know about the Jackrabbit module...
            // One to ponder; perhaps push it into common anyway?
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
    public function downloadAction()
    {
        $contentStore = $this->getServiceLocator()->get('ContentStore');
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
