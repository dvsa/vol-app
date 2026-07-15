<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Common\RefData;
use Common\Validator\MustRemainOperatorAdmin;

final class MustRemainOperatorAdminTest extends \PHPUnit\Framework\TestCase
{
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new MustRemainOperatorAdmin();
    }

    public function testIsValidTrue(): void
    {
        $this->assertTrue($this->sut->isValid(RefData::ROLE_OPERATOR_ADMIN));
        $this->assertArrayNotHasKey(MustRemainOperatorAdmin::NOT_OPERATOR_ADMIN, $this->sut->getMessages());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValidFalse')]
    public function testIsValidFalse(string $input): void
    {
        $this->assertFalse($this->sut->isValid($input));
        $this->assertArrayHasKey(MustRemainOperatorAdmin::NOT_OPERATOR_ADMIN, $this->sut->getMessages());
    }

    public static function dpIsValidFalse(): \Iterator
    {
        yield [RefData::ROLE_OPERATOR_USER];
        yield [RefData::ROLE_OPERATOR_TM];
        yield [RefData::ROLE_PARTNER_ADMIN];
        yield [RefData::ROLE_PARTNER_USER];
        yield [RefData::ROLE_LOCAL_AUTHORITY_ADMIN];
        yield [RefData::ROLE_LOCAL_AUTHORITY_USER];
        yield [RefData::ROLE_INTERNAL_LIMITED_READ_ONLY];
        yield [RefData::ROLE_INTERNAL_READ_ONLY];
        yield [RefData::ROLE_INTERNAL_CASE_WORKER];
        yield [RefData::ROLE_INTERNAL_ADMIN];
        yield [RefData::ROLE_SYSTEM_ADMIN];
        yield [RefData::ROLE_INTERNAL_IRHP_ADMIN];
        yield [RefData::ROLE_OPERATOR_TC];
        yield [RefData::ROLE_ANON];
    }
}
