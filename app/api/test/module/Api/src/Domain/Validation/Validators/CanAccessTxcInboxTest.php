<?php

/**
 * Can Access TxcInbox Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Repository\Bus;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTxcInbox;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;

/**
 * Can Access TxcInbox Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CanAccessTxcInboxTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanAccessTxcInbox
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessTxcInbox();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidForOperator($canAccess, $expected)
    {
        $this->auth->shouldReceive('isGranted')->with(Permission::OPERATOR_USER, null)
            ->andReturn(true);

        $this->auth->shouldReceive('isGranted')->with(Permission::OPERATOR_ADMIN, null)
            ->andReturn(true);

        $entity = m::mock(Bus::class)->makePartial();
        $this->setIsValid('isOwner', [$entity], $canAccess);

        $repo = $this->mockRepo('Bus');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    public function testIsValidWithEmptyId()
    {
        $this->assertFalse($this->sut->isValid(null));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidForLocalAuthority($canAccess, $expected)
    {
        $this->auth->shouldReceive('isGranted')->with(Permission::OPERATOR_USER, null)
            ->andReturn(false);
        $this->auth->shouldReceive('isGranted')->with(Permission::OPERATOR_ADMIN, null)
            ->andReturn(false);
        $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)
            ->andReturn($canAccess);
        $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)
            ->andReturn($canAccess);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    public function testIsValidWithOtherUsers()
    {
        $this->auth->shouldReceive('isGranted')->with(m::type('String'), null)
            ->andReturn(false);

        $this->assertFalse($this->sut->isValid(5));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
