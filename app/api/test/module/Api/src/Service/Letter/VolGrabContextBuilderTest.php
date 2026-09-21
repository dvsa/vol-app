<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\Letter\VolGrabContextBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class VolGrabContextBuilderTest extends MockeryTestCase
{
    private VolGrabContextBuilder $sut;

    public function setUp(): void
    {
        $this->sut = new VolGrabContextBuilder();
    }

    public function testBuildsEntityIdsFromTheInstanceRelations(): void
    {
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(10);
        $licence->shouldReceive('isNi')->andReturn(false);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(20);

        $user = m::mock(User::class)->makePartial();
        $user->setId(30);

        $case = m::mock(Cases::class)->makePartial();
        $case->setId(40);

        $organisation = m::mock(Organisation::class)->makePartial();
        $organisation->setId(50);

        $instance = new LetterInstance();
        $instance->setLicence($licence);
        $instance->setApplication($application);
        $instance->setCreatedBy($user);
        $instance->setCase($case);
        $instance->setOrganisation($organisation);

        $this->assertSame(
            [
                'licence' => 10,
                'application' => 20,
                'user' => 30,
                'case' => 40,
                'organisation' => 50,
                'isNi' => false,
            ],
            $this->sut->build($instance)
        );
    }

    public function testOmitsMissingRelationsButAlwaysCarriesIsNi(): void
    {
        $this->assertSame(['isNi' => false], $this->sut->build(new LetterInstance()));
    }

    public function testIsNiComesFromTheLicence(): void
    {
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(10);
        $licence->shouldReceive('isNi')->andReturn(true);

        $instance = new LetterInstance();
        $instance->setLicence($licence);

        $this->assertTrue($this->sut->build($instance)['isNi']);
    }

    public function testIsNiOverrideBeatsTheLicence(): void
    {
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(10);
        $licence->shouldReceive('isNi')->andReturn(true);

        $instance = new LetterInstance();
        $instance->setLicence($licence);

        $this->assertFalse($this->sut->build($instance, false)['isNi']);
    }
}
