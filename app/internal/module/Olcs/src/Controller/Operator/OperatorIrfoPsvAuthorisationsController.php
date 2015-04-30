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
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'irfoPsvAuthType',
            'status'
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
}
