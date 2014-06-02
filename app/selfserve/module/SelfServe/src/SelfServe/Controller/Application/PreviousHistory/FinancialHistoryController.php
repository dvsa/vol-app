<?php

/**
 * FinancialHistory Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\PreviousHistory;

/**
 * FinancialHistory Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialHistoryController extends PreviousHistoryController
{
    /**
     * Data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version',
            'bankrupt',
            'liquidation',
            'receivership',
            'administration',
            'disqualified',
            'insolvencyDetails',
            'insolvencyConfirmation'
        ),
        'children' => array(
            'documents' => array(
                'properties' => array(
                    'id',
                    'fileName',
                    'identifier',
                    'size'
                )
            )
        )
    );

    /**
     * Map the data
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            )
        )
    );

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Alter the form
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        $this->processFileUploads(array('data' => array('file' => 'processFinancialFileUpload')), $form);

        $fileList = $form->get('data')->get('file')->get('list');

        $fileData = $this->load($this->getIdentifier())['documents'];

        $fileList->setFiles($fileData, $this->url());

        $this->processFileDeletions(array('data' => array('file' => 'deleteFile')), $form);

        return $form;
    }

    /**
     * Handle the file upload
     *
     * @param array $file
     */
    protected function processFinancialFileUpload($file)
    {
        $uploader = $this->getUploader();

        $uploader->setFile($file);
        $uploader->upload();
        $file = $uploader->getFile();

        $fileData = $file->toArray();

        $licence = $this->getLicenceData();

        $fileData['fileName'] = $fileData['name'];
        $fileData['description'] = 'Insolvency document';
        $fileData['documentCategory'] = 1;
        $fileData['documentSubCategory'] = 1;
        $fileData['application'] = $this->getIdentifier();
        $fileData['licence'] = $licence['id'];

        unset($fileData['path']);
        unset($fileData['type']);
        unset($fileData['name']);

        $this->makeRestCall('Document', 'POST', $fileData);
    }

    /**
     * Process loading the data
     *
     * @param type $oldData
     */
    protected function processLoad($oldData)
    {
        return array('data' => $oldData);
    }
}
