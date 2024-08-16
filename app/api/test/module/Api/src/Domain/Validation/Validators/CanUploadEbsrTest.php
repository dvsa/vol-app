<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanUploadEbsr;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;

/**
 * Can upload ebsr test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CanUploadEbsrTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanUploadEbsr
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanUploadEbsr();

        parent::setUp();
    }

    /**
     * Test that the result of isValid is the same as hasActiveLicences, whether the result is true or false
     *
     * @dataProvider hasActiveLicencesProvider
     */
    public function testIsValid($hasActiveLicences)
    {
        $entityId = 111;

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('hasActiveLicences')
            ->once()
            ->with(Licence::LICENCE_CATEGORY_PSV)
            ->andReturn($hasActiveLicences);

        $repo = $this->mockRepo('Organisation');
        $repo->shouldReceive('fetchById')->once()->with($entityId)->andReturn($organisation);

        $this->assertEquals($hasActiveLicences, $this->sut->isValid($entityId));
    }

    /**
     * @return array
     */
    public function hasActiveLicencesProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Test that if there is no organisation we return false
     */
    public function testIsValidWithNullEntity()
    {
        $this->assertFalse($this->sut->isValid(null));
    }
}
