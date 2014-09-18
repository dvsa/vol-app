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
    const EMPTY_LABEL = 'Please select';

    protected function alterFormBeforeValidation($form)
    {
        $data = [];
        $filters = [];
        $subCategories = ['' => self::EMPTY_LABEL];
        $docTemplates = ['' => self::EMPTY_LABEL];

        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();
        } else if ($this->params('tmpId')) {
            $data = $this->fetchTmpData();
            $this->getUploader()->remove($this->getTmpPath());
        }

        $details = isset($data['details']) ? $data['details'] : [];

        if (isset($details['category'])) {
            $filters['category'] = $details['category'];
            $subCategories = $this->getListData(
                'DocumentSubCategory',
                $filters
            );
        }

        if (isset($details['documentSubCategory'])) {
            $filters['documentSubCategory'] = $details['documentSubCategory'];
            $docTemplates = $this->getListData(
                'DocTemplate',
                $filters
            );
        }

        $selects = [
            'details' => [
                'category' => $this->getListData(
                    'Category',
                    ['isDocCategory' => true]
                ),
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

        $redirectParams = $this->params()->fromRoute();
        $redirectParams['tmpId'] = $filename;

        return $this->redirect()->toRoute(
            $this->params('type') . '/documents/finalise',
            $redirectParams
        );
    }

    protected function getListData($entity, $filters = array(), $titleField = '', $keyField = '', $showAll = '')
    {
        return parent::getListData($entity, $filters, 'description', 'id', self::EMPTY_LABEL);
    }
}
