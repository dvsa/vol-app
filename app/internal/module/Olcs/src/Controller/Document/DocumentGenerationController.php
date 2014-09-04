<?php

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;

use Dvsa\Jackrabbit\Data\Object\File;

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class DocumentGenerationController extends DocumentController
{
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

        if (isset($data['details']['documentTemplate'])) {
            $this->addTemplateBookmarks(
                $data['details']['documentTemplate'],
                $form->get('bookmarks')
            );
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

        $details = json_encode(
            [
                'details' => $data['details'],
                'bookmarks' => $data['bookmarks']
            ]
        );
        $tmp = new File();
        $tmp->setContent($contents);
        $tmp->setMimeType($template->getMimeType());
        $tmp->setMetaData(new \ArrayObject(['data' => $details]));

        $filename = uniqid('doc_');

        $path = self::TMP_STORAGE_PATH . '/' . $filename;

        $response = $contentStore->write($path, $tmp);

        return $this->redirect()->toRoute(
            'licence/documents/finalise',
            [
                'licence' => $this->params()->fromRoute('licence'),
                'tmpId'   => $filename
            ]
        );
    }
}
