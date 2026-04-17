<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessApplication;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

/**
 * CanAccessTransportManagerApplicationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessTransportManagerApplicationTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanAccessApplication
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTransportManagerApplication();

        parent::setUp();
    }

    public function testIsValidSameTm(): void
    {
        $entity = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication::class);
        $entity->shouldReceive('getTransportManager')->with()->once()->andReturn('TM');

        $repo = $this->mockRepo('TransportManagerApplication');
        $repo->shouldReceive('fetchById')->with(212)->andReturn($entity);

        $user = $this->mockUser();
        $user->shouldReceive('getTransportManager')->with()->once()->andReturn('TM');

        $this->assertEquals(true, $this->sut->isValid(212));
    }

    public function testIsValidNotSameTm(): void
    {
        $entity = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication::class);
        $entity->shouldReceive('getTransportManager')->with()->once()->andReturn('TM');

        $repo = $this->mockRepo('TransportManagerApplication');
        $repo->shouldReceive('fetchById')->with(212)->andReturn($entity);

        $user = $this->mockUser();
        $user->shouldReceive('getTransportManager')->with()->once()->andReturn('TM DIFFERENT');

        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $this->assertEquals(true, $this->sut->isValid(212));
    }
}
