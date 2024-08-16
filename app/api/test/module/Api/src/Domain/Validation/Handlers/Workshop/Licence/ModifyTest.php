<?php

/**
 * Modify test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Workshop\Licence;

use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Workshop\Licence\Modify;
use Laminas\ServiceManager\ServiceManager;
use Dvsa\Olcs\Transfer\Command\Workshop\DeleteWorkshop as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\Workshop;

/**
 * Modify test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ModifyTest extends AbstractHandlerTestCase
{
    /**
     * @var Modify
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Modify();

        parent::setUp();
    }

    /**
     * @dataProvider contextProvider
     */
    public function testIsValidNoContextOrWrongContext($application, $licence, $ids)
    {
        $data = [
            'application' => $application,
            'licence' => $licence,
            'ids' => $ids
        ];
        if (!$licence && $application) {
            $this->mockGetLicenceFromApplication($licence, $application);
        }

        $dto = Cmd::create($data);
        $this->setIsValid('canAccessLicence', [$licence], false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function contextProvider()
    {
        return [
            [null, null, null],
            [null, 1, [123]],
            [1, null, [123]]
        ];
    }

    /**
     * @dataProvider licenceProvider
     */
    public function testIsValidWithOwnership($licenceId, $expected)
    {
        $data = [
            'ids' => [111],
            'licence' => 123,
        ];

        $dto = Cmd::create($data);

        $this->setIsValid('canAccessLicence', [123], true);

        $workshops = $this->getWorkshops();

        $workshops[0]->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn($licenceId)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->assertEquals($expected, $this->sut->isValid($dto));
    }

    public function licenceProvider()
    {
        return [
            [123, true],
            [231, false]
        ];
    }

    public function getLicenceFromApplication()
    {
        $licence = m::mock(Licence::class);

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence')->andReturn($licence)->getMock();

        $mockApplicationRepo = $this->mockRepo('Application');
        $mockApplicationRepo->shouldReceive('fetchById')->with(222)->andReturn($application)->getMock();

        return $licence;
    }

    public function getWorkshops()
    {
        $workshop = m::mock(Workshop::class);

        $mockWorkshopRepo = $this->mockRepo('Workshop');
        $mockWorkshopRepo->shouldReceive('fetchByIds')->with([111])->andReturn([$workshop]);

        return [$workshop];
    }

    public function mockGetLicenceFromApplication($licence, $application)
    {
        $mockApplication = m::mock(Application::class)
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn($licence)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $mockApplicationRepo = $this->mockRepo('Application');
        $mockApplicationRepo->shouldReceive('fetchById')
            ->with($application)
            ->andReturn($mockApplication)
            ->getMock();
    }
}
