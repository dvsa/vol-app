<?php

declare(strict_types=1);

/**
 * Internal / Common Licence People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace OlcsTest\Controller\Lva\Adapters;

use Common\RefData;
use Common\Service\Cqrs\Response;
use Common\Service\Lva\PeopleLvaService;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\Licence\DeletePeopleViaVariation;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;
use Laminas\Form\Form;
use Laminas\ServiceManager\ServiceManager;

/**
 * Internal / Common Licence People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapterTest extends MockeryTestCase
{
    /** @var  LicencePeopleAdapter|m\Mock */
    protected $sut;
    /** @var  Form|m\Mock */
    protected $mockForm;
    /** @var  TableBuilder|m\Mock */
    protected $mockTbl;
    /** @var  ServiceManager|m\Mock */
    protected $mockContainer;
    /** @var  ContainerInterface|m\Mock */
    protected $mockPplSrv;

    public function setUp(): void
    {
        $this->mockForm = m::mock(Form::class);
        $this->mockTbl = m::mock(TableBuilder::class);

        $this->mockPplSrv = m::mock(PeopleLvaService::class);

        $this->mockContainer = m::mock(ContainerInterface::class);
        $this->mockContainer->allows('get')->with('Table')->andReturn($this->mockTbl);

        $this->sut = m::mock(LicencePeopleAdapter::class, [$this->mockContainer, $this->mockPplSrv])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testAlterFormForOrganisationCantModify(): void
    {
        $this->sut->shouldReceive('canModify')->andReturn(false);

        $this->mockPplSrv->shouldReceive('lockOrganisationForm')->once()->with($this->mockForm, $this->mockTbl);

        $this->mockTbl->shouldReceive('getAction')->never();

        $this->sut->alterFormForOrganisation($this->mockForm, $this->mockTbl);
    }

    public function testAlterFormForOrganisationCanModify(): void
    {
        $this->sut->shouldReceive('canModify')->once()->andReturn(true);
        $this->sut->shouldReceive('getOrganisationType')->andReturn(RefData::ORG_TYPE_REGISTERED_COMPANY);

        $this->mockPplSrv->shouldReceive('lockOrganisationForm')->never();

        $this->mockTbl->shouldReceive('getAction')->once()->with('add');
        $this->mockTbl->shouldReceive('removeAction')->once()->with('add');
        $this->mockTbl->shouldReceive('addAction')->once();

        $this->sut->alterFormForOrganisation($this->mockForm, $this->mockTbl);
    }

    /**
     *
     * @param $isExceptionalOrg
     * @param $expect
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCanModify')]
    public function testCanModify(bool $isExceptionalOrg, bool $expect): void
    {
        $this->sut->shouldReceive('isExceptionalOrganisation')->andReturn($isExceptionalOrg);

        static::assertEquals($expect, $this->sut->canModify());
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{array{isExceptionalOrg: true, expect: false}, array{isExceptionalOrg: false, expect: true}}
     */
    public static function dpTestCanModify(): array
    {
        return [
            [
                'isExceptionalOrg' => true,
                'expect' => false,
            ],
            [
                'isExceptionalOrg' => false,
                'expect' => true,
            ],
        ];
    }

    public function testGetDeleteCommand(): void
    {
        $this->sut->shouldReceive('getLicenceId')->andReturn(999);
        $this->sut->shouldReceive('handleCommand')
            ->once()
            ->andReturnUsing(
                function (DeletePeopleViaVariation $cmd) {
                    $this->assertEquals(['TEST_PERSON_ID_1', 'TEST_PERSON_ID_2'], $cmd->getPersonIds());
                    $this->assertEquals(999, $cmd->getId());
                    $result = m::mock(Response::class);
                    $result->shouldReceive('isOk')->andReturn(true);
                    return $result;
                }
            );

        $this->sut->delete(['TEST_PERSON_ID_1', 'TEST_PERSON_ID_2']);
    }
}
