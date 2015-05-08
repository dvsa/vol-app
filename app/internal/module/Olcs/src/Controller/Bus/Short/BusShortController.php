<?php

/**
 * Bus Short Notice Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Short;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Short Notice Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusShortController extends BusController
{
    protected $layoutFile = 'layout/wide-layout';
    protected $section = 'short';
    protected $subNavRoute = 'licence_bus_short';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-short-notice';

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'busRegId';

    /**
     * Identifier key
     *
     * @var string
     */
    protected $identifierKey = 'busReg';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'BusShortNotice';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
                'base'
            )
        )
    );

    /**
     * Load data for the form
     *
     * This method should be overridden
     *
     * @param int $id
     * @return array
     */

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array();

    public function processLoad($data)
    {
        $data = (isset($data['Results'][0]) ? $data['Results'][0] : []);
        return parent::processLoad($data);
    }

    public function alterForm($form)
    {
        if (!$this->isLatestVariation()) {
            $form->setOption('readonly', true);
        }
        return $form;
    }

    /**
     * Method to save the form data, called when inserting or editing.
     *
     * @param array $data
     * @return array|mixed|\Zend\Http\Response
     */
    public function processSave($data)
    {
        // link to the Bus Reg
        $data['fields'] = array_merge(
            $data['fields'],
            ['busReg' => $this->getIdentifier()]
        );
        return parent::processSave($data);
    }

    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            null,
            ['action'=>'edit'],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }
}
