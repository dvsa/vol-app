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
                    'version',
                    'filename',
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

        $options = array(
            'fieldset' => 'data',
            'data'     => $this->loadCurrent(),
        );
        $form = static::makeFormAlterations($form, $this, $options);

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
        $this->uploadFile(
            $file,
            array(
                'description' => 'Insolvency document',
                // @todo Add a better way to find the category id
                'category' => 1,
                'documentSubCategory' => 1
            )
        );
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

    /**
     * Make any relevant form alterations before rendering. In this case
     * we don't show the insolvency details data if it's empty and we're
     * on a review page
     */
    public static function makeFormAlterations($form, $context, $options)
    {
        $isReview = isset($options['isReview']) && $options['isReview'];
        $data = $options['data'];
        $fieldset = $form->get($options['fieldset']);

        if ($isReview && empty($data['insolvencyDetails'])) {
            $fieldset->remove('insolvencyDetails');
        }

        $fileList = $fieldset->get('file')->get('list');

        $fileList->setFiles($data['documents'], $context->url());

        return $form;
    }
}
