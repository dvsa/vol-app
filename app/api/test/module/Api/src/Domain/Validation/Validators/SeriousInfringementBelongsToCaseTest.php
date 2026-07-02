<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\SeriousInfringementBelongsToCase;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement;
use Mockery as m;

/**
 * Serious Infringement Belongs To Case Test
 */
class SeriousInfringementBelongsToCaseTest extends AbstractValidatorsTestCase
{
    /**
     * @var SeriousInfringementBelongsToCase
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new SeriousInfringementBelongsToCase();

        parent::setUp();
    }

    public function testIsValidMatchingId(): void
    {
        $case = m::mock(Cases::class);
        $case->expects('getId')->withNoArgs()->andReturn(4);

        $si = m::mock(SeriousInfringement::class);
        $si->expects('getCase')->withNoArgs()->andReturn($case);

        $this->assertTrue($this->sut->isValid($si, 4));
    }

    public function testIsValidMismatchedId(): void
    {
        $case = m::mock(Cases::class);
        $case->expects('getId')->withNoArgs()->andReturn(4);

        $si = m::mock(SeriousInfringement::class);
        $si->expects('getCase')->withNoArgs()->andReturn($case);

        $this->assertFalse($this->sut->isValid($si, 999));
    }

    public function testIsValidEntityToEntity(): void
    {
        $case = m::mock(Cases::class);
        $case->expects('getId')->withNoArgs()->andReturn(4);

        $parent = m::mock(Cases::class);
        $parent->expects('getId')->withNoArgs()->andReturn(4);

        $si = m::mock(SeriousInfringement::class);
        $si->expects('getCase')->withNoArgs()->andReturn($case);

        $this->assertTrue($this->sut->isValid($si, $parent));
    }

    public function testIsValidSiHasNoCase(): void
    {
        $si = m::mock(SeriousInfringement::class);
        $si->expects('getCase')->withNoArgs()->andReturnNull();

        $this->assertFalse($this->sut->isValid($si, 4));
    }

    public function testIsValidResolvesSiById(): void
    {
        $case = m::mock(Cases::class);
        $case->expects('getId')->withNoArgs()->andReturn(4);

        $si = m::mock(SeriousInfringement::class);
        $si->expects('getCase')->withNoArgs()->andReturn($case);

        $repo = $this->mockRepo('SeriousInfringement');
        $repo->expects('fetchById')->with(206)->andReturn($si);

        $this->assertTrue($this->sut->isValid(206, 4));
    }
}
