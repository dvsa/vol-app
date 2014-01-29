<?php

namespace unit\Olcs\Controller;

use \OlcsCommon\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp($noConfig = false)
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/test/application.config.php'
        );
        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);
/*
        $this->assertModuleName('olcs');
        $this->assertControllerName('olcs_index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
*/    }
}
?>