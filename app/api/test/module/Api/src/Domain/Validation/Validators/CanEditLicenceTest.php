<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanEditLicence;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

/**
 * Can Edit Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanEditLicenceTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanEditLicence
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanEditLicence();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($isOwner, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_EDIT, false);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);
        $entity = m::mock(Licence::class);

        $repo = $this->mockRepo('Licence');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], $isOwner);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidLicNo($isOwner, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_EDIT, false);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);
        $entity = m::mock(Licence::class);

        $repo = $this->mockRepo('Licence');
        $repo->shouldReceive('fetchByLicNo')->with('XY12345')->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], $isOwner);

        $this->assertEquals($expected, $this->sut->isValid('XY12345'));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidInternal($isOwner, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_EDIT, true);
        $entity = m::mock(Licence::class);

        $repo = $this->mockRepo('Licence');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], $isOwner);

        $this->assertEquals(true, $this->sut->isValid(111));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
