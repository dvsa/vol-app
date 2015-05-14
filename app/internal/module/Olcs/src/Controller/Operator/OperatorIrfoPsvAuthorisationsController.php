<?php

/**
 * Operator Irfo Psv Authorisations Controller
 */
namespace Olcs\Controller\Operator;

/**
 * Operator Irfo Psv Authorisations Controller
 */
class OperatorIrfoPsvAuthorisationsController extends OperatorController
{
    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'IrfoPsvAuth';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'IrfoPsvAuth';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'operator.irfo.psv-authorisations';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'organisation'
    ];

    /**
     * Data map
     *
     * @var array
    */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
            )
        )
    );

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'irfoPsvAuthType',
            'status',
            'journeyFrequency',
            'countrys',
        )
    );

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    /**
     * @var string
     */
    protected $section = 'irfo_psv_authorisations';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_irfo';

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        $data = parent::processLoad($data);

        if (empty($data['organisation'])) {
            // link to the organisation
            $data['fields']['organisation'] = $this->getFromRoute('organisation');
        }

        if (empty($data['status'])) {
            // set status to pending by default
            $data['fields']['status'] = 'irfo_auth_s_pending';
        }

        if (!empty($data['createdOn'])) {
            // format createOn date
            $data['fields']['createdOnHtml'] = $this->getServiceLocator()->get('Helper\Date')
                ->getDateObject($data['createdOn'])
                ->format('d/m/Y');
        }

        return $data;
    }
}
