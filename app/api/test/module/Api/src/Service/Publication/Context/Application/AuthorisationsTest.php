<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Application;

use ArrayObject;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\Application\Authorisations;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class AuthorisationsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AuthorisationsTest extends MockeryTestCase
{
    /**
     * @var Authorisations
     */
    private $sut;

    private $application;

    private $publicationLink;

    private $context;

    public function setUp(): void
    {
        $this->sut = new Authorisations(
            m::mock(QueryHandlerManager::class)
        );

        $this->application = m::mock(Application::class);

        $this->publicationLink = m::mock(PublicationLink::class);
        $this->publicationLink->shouldReceive('getApplication')
            ->withNoArgs()
            ->andReturn($this->application);

        $this->context = new ArrayObject();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSetContextWhenTotAuthLgvVehiclesNumeric')]
    public function testSetContextWhenTotAuthLgvVehiclesNumeric(mixed $totAuthLgvVehicles, mixed $expectedAuthorisationText): void
    {
        $this->application->shouldReceive('getTotAuthLgvVehicles')
            ->withNoArgs()
            ->andReturn($totAuthLgvVehicles);

        $this->assertSame(
            [
                'authorisation' => $expectedAuthorisationText
            ],
            $this->sut->provide($this->publicationLink, $this->context)->getArrayCopy()
        );
    }

    public static function dpSetContextWhenTotAuthLgvVehiclesNumeric(): array
    {
        return [
            [0, 'Authorisation: 0 Light goods vehicle(s).'],
            [1, 'Authorisation: 1 Light goods vehicle(s).'],
            [2, 'Authorisation: 2 Light goods vehicle(s).'],
        ];
    }

    public function testDoNothingWhenTotAuthLgvVehiclesNull(): void
    {
        $this->application->shouldReceive('getTotAuthLgvVehicles')
            ->withNoArgs()
            ->andReturnNull();

        $this->assertCount(
            0,
            $this->sut->provide($this->publicationLink, $this->context)
        );
    }
}
