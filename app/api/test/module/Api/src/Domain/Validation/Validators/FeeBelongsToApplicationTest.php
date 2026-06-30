<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\FeeBelongsToApplication;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Mockery as m;

/**
 * Fee Belongs To Application Test
 */
class FeeBelongsToApplicationTest extends AbstractValidatorsTestCase
{
    /**
     * @var FeeBelongsToApplication
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new FeeBelongsToApplication();

        parent::setUp();
    }

    public function testIsValidMatchingId(): void
    {
        $application = m::mock(Application::class);
        $application->expects('getId')->withNoArgs()->andReturn(5);

        $fee = m::mock(Fee::class);
        $fee->expects('getApplication')->withNoArgs()->andReturn($application);

        $this->assertTrue($this->sut->isValid($fee, 5));
    }

    public function testIsValidMismatchedId(): void
    {
        $application = m::mock(Application::class);
        $application->expects('getId')->withNoArgs()->andReturn(5);

        $fee = m::mock(Fee::class);
        $fee->expects('getApplication')->withNoArgs()->andReturn($application);

        $this->assertFalse($this->sut->isValid($fee, 999));
    }

    public function testIsValidEntityToEntity(): void
    {
        $application = m::mock(Application::class);
        $application->expects('getId')->withNoArgs()->andReturn(5);

        $parent = m::mock(Application::class);
        $parent->expects('getId')->withNoArgs()->andReturn(5);

        $fee = m::mock(Fee::class);
        $fee->expects('getApplication')->withNoArgs()->andReturn($application);

        $this->assertTrue($this->sut->isValid($fee, $parent));
    }

    public function testIsValidFeeHasNoApplication(): void
    {
        $fee = m::mock(Fee::class);
        $fee->expects('getApplication')->withNoArgs()->andReturnNull();

        $this->assertFalse($this->sut->isValid($fee, 5));
    }
}
