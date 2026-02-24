<?php

declare(strict_types=1);

/**
 * Modify test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Workshop\Application;

use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Workshop\Application\Modify;
use Laminas\ServiceManager\ServiceManager;
use Dvsa\Olcs\Transfer\Command\Application\DeleteWorkshop as Cmd;
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

    #[\PHPUnit\Framework\Attributes\DataProvider('contextProvider')]
    public function testIsValidNoContextOrWrongContext(mixed $application, mixed $ids): void
    {
        $data = [
            'application' => $application
        ];
        if ($ids) {
            $data['ids'] = $ids;
        }

        $dto = Cmd::create($data);
        $this->setIsValid('canAccessApplication', [$application], false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public static function contextProvider(): array
    {
        return [
            [null, null],
            [1, [123]]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceProvider')]
    public function testIsValidWithOwnership(mixed $licenceId, mixed $expected): void
    {
        $data = [
            'ids' => [111],
            'application' => 222
        ];

        $licence = $this->getLicenceFromApplication();
        $licence->shouldReceive('getId')->andReturn(123);

        $dto = Cmd::create($data);

        $this->setIsValid('canAccessApplication', [222], true);

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
        $mockWorkshopRepo->shouldReceive('fetchByIds')->with([111])->andReturn([$workshop]);

        return [$workshop];
    }
}
