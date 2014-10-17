<?php

/**
 * Document Upload Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;
use Common\Service\File\Exception as FileException;
use Olcs\Controller\Traits\DocumentUploadTrait;

/**
 * Document Generation Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentUploadController extends AbstractDocumentController
{
    use DocumentUploadTrait;

    /**
     * Labels for empty select options
     */
    const EMPTY_LABEL = 'Please select';

    /**
     * how to map route param types to category names
     */
    private $categoryMap = [
        'licence' => 'Licensing'
    ];

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
        }

        $data = array_merge($defaultData, $data);

        $details = isset($data['details']) ? $data['details'] : [];

        $filters['category'] = $details['category'];

        $subCategories = $this->getListData(
            'DocumentSubCategory',
            $filters
        );

        $selects = [
            'details' => [
                'category' => $categories,
                'documentSubCategory' => $subCategories
            ]
        ];

        foreach ($selects as $fieldset => $inputs) {
            foreach ($inputs as $name => $options) {
                $form->get($fieldset)
                    ->get($name)
                    ->setValueOptions($options);
            }
        }

        $form->setData($data);

        return $form;
    }

    public function uploadAction()
    {
        $form = $this->generateForm('upload-document', 'processUpload');

        $this->loadScripts(['upload-document']);

        $view = new ViewModel(['form' => $form]);

        $view->setTemplate('form-simple');
        return $this->renderView($view, 'Upload Document');
    }

    public function processUpload($data)
    {
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        $files = $this->getRequest()->getFiles()->toArray();
        $files=$files['details'];

        if (!isset($files['file']) || $files['file']['error'] !== UPLOAD_ERR_OK) {
            // @TODO this needs to be handled better; by the time we get here we
            // should *know* that our files are valid
            $this->addErrorMessage('Sorry; there was a problem uploading the file. Please try again.');
            return $this->redirect()->toRoute(
                $type . '/documents/upload',
                $routeParams
            );
        }
        $uploader = $this->getUploader();
        $uploader->setFile($files['file']);

        try {
            $file = $uploader->upload();
        } catch (FileException $ex) {
            $this->addErrorMessage('The document store is unavailable. Please try again later');
            return $this->redirect()->toRoute(
                $type . '/documents/upload',
                $routeParams
            );
        }

        // we don't know what params are needed to satisfy this type's
        // finalise route; so to be safe we supply them all
        $routeParams = array_merge(
            $routeParams,
            [
                'tmpId' => $file->getIdentifier()
            ]
        );

        // AC specifies this timestamp format...
        $fileName = date('YmdHi')
            . '_' . $this->formatFilename($files['file']['name'])
            . '.' . $file->getExtension();
        $data = [
            'identifier'          => $file->getIdentifier(),
            'description'         => $data['details']['description'],
            'filename'            => $fileName,
            'fileExtension'       => 'doc_' . $file->getExtension(),
            'category'            => $data['details']['category'],
            'documentSubCategory' => $data['details']['documentSubCategory'],
            'isDigital'           => true,
            'isReadOnly'          => true,
            'issuedDate'          => date('Y-m-d H:i:s'),
            'size'                => $file->getSize()
        ];

        $data[$type] = $routeParams[$type];

        $this->makeRestCall(
            'Document',
            'POST',
            $data
        );

        $this->removeTmpData();

        return $this->redirect()->toRoute(
            $type . '/documents',
            $routeParams
        );

    }
}
