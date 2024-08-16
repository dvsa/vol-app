<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessIrhpApplicationWithIrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessIrhpApplicationWithIrhpApplication
 */
class CanAccessIrhpApplicationWithIrhpApplicationTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanAccessIrhpApplicationWithIrhpApplication
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessIrhpApplicationWithIrhpApplication();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($isOwner, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);
        $entity = m::mock(IrhpApplication::class);

        $repo = $this->mockRepo('IrhpApplication');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], $isOwner);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    /**
     * @dataProvider provider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testIsValidInternal($isOwner, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $entity = m::mock(IrhpApplication::class);

        $repo = $this->mockRepo('IrhpApplication');
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
