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
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
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
}
