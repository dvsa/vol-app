<?php

/**
 * Bus Details Stop Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

/**
 * Bus Details Stop Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsStopController extends BusDetailsController
{
    protected $item = 'stop';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-reg-stop';

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
            'subsidised' => array(
                'id'
            )
        )
    );
}
