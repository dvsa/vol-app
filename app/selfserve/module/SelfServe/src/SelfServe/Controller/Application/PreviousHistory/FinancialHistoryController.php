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
                'data',
                'insolvencyDetails',
                'insolvencyConfirmation'
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
     * Process loading the data
     *
     * @param type $oldData
     */
    protected function processLoad($oldData)
    {
        $data['data'] = $oldData;

        unset($data['data']['insolvencyDetails']);
        unset($data['data']['insolvencyConfirmation']);

        $data['insolvencyDetails']['insolvencyDetails'] = $oldData['insolvencyDetails'];
        $data['insolvencyConfirmation']['insolvencyConfirmation'] = $oldData['insolvencyConfirmation'];

        return $data;
    }
}
