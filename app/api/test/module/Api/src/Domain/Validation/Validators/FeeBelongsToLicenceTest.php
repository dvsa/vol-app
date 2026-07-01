<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\FeeBelongsToLicence;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;

/**
 * Fee Belongs To Licence Test
 */
class FeeBelongsToLicenceTest extends AbstractValidatorsTestCase
{
    /**
     * @var FeeBelongsToLicence
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new FeeBelongsToLicence();

        parent::setUp();
    }

    public function testIsValidMatchingId(): void
    {
        $licence = m::mock(Licence::class);
        $licence->expects('getId')->withNoArgs()->andReturn(7);

        $fee = m::mock(Fee::class);
        $fee->expects('getLicence')->withNoArgs()->andReturn($licence);

        $this->assertTrue($this->sut->isValid($fee, 7));
    }

    public function testIsValidMismatchedId(): void
    {
        $licence = m::mock(Licence::class);
        $licence->expects('getId')->withNoArgs()->andReturn(7);

        $fee = m::mock(Fee::class);
        $fee->expects('getLicence')->withNoArgs()->andReturn($licence);

        $this->assertFalse($this->sut->isValid($fee, 999));
    }

    public function testIsValidEntityToEntity(): void
    {
        $licence = m::mock(Licence::class);
        $licence->expects('getId')->withNoArgs()->andReturn(7);

        $parent = m::mock(Licence::class);
        $parent->expects('getId')->withNoArgs()->andReturn(7);

        $fee = m::mock(Fee::class);
        $fee->expects('getLicence')->withNoArgs()->andReturn($licence);

        $this->assertTrue($this->sut->isValid($fee, $parent));
    }

    public function testIsValidFeeHasNoLicence(): void
    {
        $fee = m::mock(Fee::class);
        $fee->expects('getLicence')->withNoArgs()->andReturnNull();

        $this->assertFalse($this->sut->isValid($fee, 7));
    }

    public function testIsValidNullParent(): void
    {
        $fee = m::mock(Fee::class);
        $fee->expects('getLicence')->withNoArgs()->andReturn(m::mock(Licence::class));

        $this->assertFalse($this->sut->isValid($fee, null));
    }

    public function testIsValidResolvesFeeById(): void
    {
        $licence = m::mock(Licence::class);
        $licence->expects('getId')->withNoArgs()->andReturn(7);

        $fee = m::mock(Fee::class);
        $fee->expects('getLicence')->withNoArgs()->andReturn($licence);

        $repo = $this->mockRepo('Fee');
        $repo->expects('fetchById')->with(123)->andReturn($fee);

        $this->assertTrue($this->sut->isValid(123, 7));
    }
}
