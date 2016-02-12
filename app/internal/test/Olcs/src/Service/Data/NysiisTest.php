<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Nysiis;
use Mockery as m;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * Class IrfoPsvAuthType Test
 * @package CommonTest\Service
 */
class NysiisTest extends AbstractDataServiceTestCase
{
    /**
     *
     */
    public function testGetNysiisSearchKeys()
    {
        $source = [
            'nysiisForename' => 'fn',
            'nysiisFamilyname' => 'ln'
        ];
        $soapClient = 'soapclient';
        $config = ['foo' => 'bar'];

        $sut = new Nysiis($soapClient, $config);

        $this->assertEquals($source, $sut->getNysiisSearchKeys($source));
        $this->assertEquals($soapClient, $sut->getSoapClient());
        $this->assertEquals($config, $sut->getNysiisConfig());
    }
}
