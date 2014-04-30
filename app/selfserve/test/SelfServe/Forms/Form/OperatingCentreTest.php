<?php

namespace SelfServe\test\Forms\Form;

class OperatingCentreTest extends \PHPUnit_Framework_TestCase
{

    public function testConfigFormPsvDependency()
    {
        $config = include('./module/SelfServe/src/SelfServe/Form/Forms/operating-centre-authorisation.form.php');
        $this->assertArrayHasKey('operating-centre-authorisation', $config);
        $this->assertArrayHasKey('totAuthTrailers', $config['operating-centre-authorisation']['fieldsets'][0]['elements']);
    }

}