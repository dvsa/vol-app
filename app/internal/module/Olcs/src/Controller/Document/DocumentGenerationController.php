<?php

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;

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

        $selects = [
            'details' => [
                'category' => $this->getListData('Category', ['isDocCategory' => true], 'description', 'id', 'Please select'),
                'documentSubCategory' => $this->getListData('DocumentSubCategory', $filters, 'description', 'id', 'Please select'),
                'documentTemplate' => $this->getListData('DocTemplate', $filters, 'description', 'id', 'Please select')
            ]
        ];

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

        // @TODO obviously this is hideously inefficient but is
        // simply to prove the concept for now. Will tidy up
        // before finishing the story; we'll probably end up
        // creating a custom endpoint to fetch all paragraphs
        // by ID in one rest call
        $bookmarks = [];
        foreach ($data['bookmarks'] as $key => $ids) {
            if ($ids === null) {
                // all groups of bookmarks are optional
                continue;
            }
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

        // we've now got our raw content and our bookmarks, so can hand off
        // to our template service / doc gen service to generate the doc
        // pretend for now...
        $file = $this->getDocumentService()
            ->generateFromTemplate(
                $template['document']['identifier'],
                $bookmarks
            );

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

        return $this->redirect()->toRoute(
            'licence/documents/finalise',
            [
                'licence' => $this->params()->fromRoute('licence'),
                'tmpId'   => $filename
            ]
        );
    }
}
