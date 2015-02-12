<?php

/**
 * External Licence & Variation Business Type Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\LicenceVariationBusinessTypeAdapter;
use Common\Service\Entity\LicenceEntityService;
use OlcsTest\Bootstrap;

/**
 * External Licence & Variation Business Type Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceVariationBusinessTypeAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->controller = m::mock('\Zend\Mvc\Controller\AbstractController');

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = new LicenceVariationBusinessTypeAdapter();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAlterFormForOrganisation()
    {
        $this->sm->setService(
            'Lva\BusinessType',
            m::mock()
            ->shouldReceive('lockType')
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals(
            'foo',
            $this->sut->alterFormForOrganisation(m::mock('Zend\Form\Form'), 123)
        );
    }
}
