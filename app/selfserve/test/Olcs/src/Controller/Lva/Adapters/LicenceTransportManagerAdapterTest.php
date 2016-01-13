<?php

/**
 * External Transport Managers Adapter Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * External Transport Managers Adapter Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceTransportManagerAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Lva\Adapters\LicenceTransportManagerAdapter();

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager');
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAddMessages()
    {
        $this->sm->shouldReceive('get->addVariationMessage')->once()->with(612, 'transport_managers');
        $this->sut->addMessages(612);
    }
}
