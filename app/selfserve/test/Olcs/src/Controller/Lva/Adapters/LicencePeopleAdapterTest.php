<?php

/**
 * Internal / Common Licence People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace OlcsTest\Controller\Lva\Adapters;

use Common\Service\Table\TableBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;
use Common\Service\Entity\OrganisationEntityService;
use Zend\ServiceManager\ServiceManager;

/**
 * Internal / Common Licence People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapterTest extends MockeryTestCase
{
    /**
     * @var LicencePeopleAdapter
     */
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sm->setAllowOverride(true);
        $this->sut = new LicencePeopleAdapter();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAlterFormForOrganisation()
    {
        $form = m::mock('Zend\Form\Form');
        $table = m::mock();

        $this->sm->setService(
            'Lva\People',
            m::mock()
                ->shouldReceive('lockOrganisationForm')
                ->with($form, $table)
                ->getMock()
        );

        $this->sut->alterFormForOrganisation($form, $table, 123);
    }

    public function testCanModify()
    {
        $this->assertFalse($this->sut->canModify(123));
    }

    public function testCreateTableAltersLabel()
    {

        $mockTable = m::mock(TableBuilder::class);
        $mockTable->shouldReceive('prepareTable')->withAnyArgs()->andReturnSelf();
        $this->sm->shouldReceive('get')
            ->andReturn($mockTable);
        $this->sut->createTable();
    }
}
