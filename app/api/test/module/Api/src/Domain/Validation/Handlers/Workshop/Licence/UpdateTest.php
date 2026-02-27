<?php

declare(strict_types=1);

/**
 * Update test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Workshop\Licence;

use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Workshop\Licence\Update;
use Laminas\ServiceManager\ServiceManager;
use Dvsa\Olcs\Transfer\Command\Workshop\UpdateWorkshop as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\Workshop;

/**
 * Update test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateTest extends AbstractHandlerTestCase
{
    /**
     * @var Update
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Update();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('contextProvider')]
    public function testIsValidNoContextOrWrongContext(mixed $application, mixed $licence, mixed $id): void
    {
        $data = [
            'application' => $application,
            'licence' => $licence,
            'id' => $id
        ];
        if (!$licence && $application) {
            $this->mockGetLicenceFromApplication($licence, $application);
        }

        $dto = Cmd::create($data);
        $this->setIsValid('canAccessLicence', [$licence], false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public static function contextProvider(): array
    {
        return [
            [null, null, null],
            [null, 1, 123],
            [1, null, 123]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceProvider')]
    public function testIsValidWithOwnership(mixed $licenceId, mixed $expected): void
    {
        $data = [
            'id' => 111,
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

    public static function licenceProvider(): array
    {
        return [
            [123, true],
            [231, false]
        ];
    }

    public function getLicenceFromApplication(): mixed
    {
        $licence = m::mock(Licence::class);

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence')->andReturn($licence)->getMock();

        $mockApplicationRepo = $this->mockRepo('Application');
        $mockApplicationRepo->shouldReceive('fetchById')->with(222)->andReturn($application)->getMock();

        return $licence;
    }

    public function getWorkshops(): array
    {
        $workshop = m::mock(Workshop::class);

        $mockWorkshopRepo = $this->mockRepo('Workshop');
        $mockWorkshopRepo->shouldReceive('fetchById')->with(111)->andReturn($workshop);

        return [$workshop];
    }

    public function mockGetLicenceFromApplication(mixed $licence, mixed $application): void
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
