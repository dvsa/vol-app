<?php
namespace OlcsSelfserve\unit\View\Helper;

use PHPUnit_Framework_TestCase;
use OlcsSelfserve\View\Helper\ResourceHelper;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResourceHelperTest
 *
 * @author valtechuk
 */

class ResourceHelperTest extends PHPUnit_Framework_TestCase{

    
    /**
     * Trace error when exception is throwed in application
     * @var bool
     */
    protected $traceError = false;
    
    protected $resourceHelper;

    /**
     * Reset the application for isolation
     */
    protected function setUp()
    {
            $this->resourceHelper = new ResourceHelper(array());
    }
    
    public function testForGettingAValueFromResourceFile() {
        
        $result = $this->resourceHelper->__invoke("testProperty");
        $this->assertEquals($result, "");
    }

    /*
    public function testForGettingNonexistingValueFromResourceFile() {

        $result = $this->resourceHelper->__invoke("nonexistingFooProperty");
        $this->assertEquals($result, '');

    }
    
    public function testForGetttingValuesWithPropertyPlaceHolders() {
        $result  = $this->resourceHelper->__invoke('testPropertyPlaceHolder',
                                                    array("@placeHolder" => "changed",
                                                      "@here" => "changedAgain"));
        $this->assertEquals($result,"test changed is changedAgain");
        
    }
    
    public function testForGettingValuesWhenWrongPropertyPlaceHolderIsProvided() {
        $result  = $this->resourceHelper->__invoke('testPropertyPlaceHolder',
                                                    array("@placeHolderNotFound" => "changed",
                                                      "@hereNotFound" => "changedAgain"));
        $this->assertEquals($result,"test @placeHolder is @here");
        
    }
     */
}
?>