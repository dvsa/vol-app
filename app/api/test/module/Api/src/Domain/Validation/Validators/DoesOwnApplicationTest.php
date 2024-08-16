<?php

/**
 * Does Own Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\DoesOwnApplication;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Mockery as m;

/**
 * Does Own Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DoesOwnApplicationTest extends AbstractValidatorsTestCase
{
    /**
     * @var DoesOwnApplication
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DoesOwnApplication();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidTrue($isOwner, $expected)
    {
        $entity = m::mock(Application::class);

        $repo = $this->mockRepo('Application');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], $isOwner);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
