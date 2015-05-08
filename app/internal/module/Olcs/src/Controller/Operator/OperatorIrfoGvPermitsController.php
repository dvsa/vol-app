<?php

/**
 * Operator Irfo Gv Permits Controller
 */
namespace Olcs\Controller\Operator;

/**
 * Operator Irfo Gv Permits Controller
 */
class OperatorIrfoGvPermitsController extends OperatorController
{
    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'IrfoGvPermit';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'IrfoGvPermit';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'operator.irfo.gv-permits';

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
            'organisation',
            'irfoGvPermitType',
            'irfoPermitStatus'
        )
    );

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    /**
     * @var string
     */
    protected $section = 'irfo_gv_premits';

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

        if (empty($data['irfoPermitStatus'])) {
            // set status to pending by default
            $data['fields']['irfoPermitStatus'] = 'irfo_perm_s_pending';
        }

        if (!empty($data['createdOn'])) {
            // format createOn date
            $data['fields']['createdOnHtml'] = $this->getServiceLocator()->get('Helper\Date')
                ->getDateObject($data['createdOn'])
                ->format('d/m/Y');
        }

        if (!empty($data['id'])) {
            // set id for HTML element
            $data['fields']['idHtml'] = $data['id'];
        }

        return $data;
    }
}
