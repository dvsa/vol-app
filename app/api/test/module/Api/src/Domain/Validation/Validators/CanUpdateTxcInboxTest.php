<?php

/**
 * Can Access TxcInbox Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanUpdateTxcInbox;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;

/**
 * Can Update TxcInbox Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CanUpdateTxcInboxTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanUpdateTxcInbox
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanUpdateTxcInbox();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canUpdate, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)
            ->andReturn($canUpdate);

        $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)
            ->andReturn($canUpdate);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    public function testIsValidWithEmptyId()
    {
        $this->assertFalse($this->sut->isValid(null));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidInternal($canUpdate, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_USER, $canUpdate);
        $entity = m::mock(TxcInbox::class)->makePartial();

        if (!$canUpdate) {
            $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)
                ->andReturn($canUpdate);
            $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)
                ->andReturn($canUpdate);
        }
        $repo = $this->mockRepo('TxcInbox');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    public function testIsValidWithOtherUsers()
    {
        $this->auth->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);
        $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)
            ->andReturn(false);

        $this->auth->shouldReceive('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)
            ->andReturn(false);

        $this->assertFalse($this->sut->isValid(null));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
