<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Common\RefData;
use Common\Validator\MustRemainOperatorAdmin;

class MustRemainOperatorAdminTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @dataProvider dpIsValidFalse
     */
    public function testIsValidFalse(string $input): void
    {
        $this->assertFalse($this->sut->isValid($input));
        $this->assertArrayHasKey(MustRemainOperatorAdmin::NOT_OPERATOR_ADMIN, $this->sut->getMessages());
    }

    public function dpIsValidFalse(): array
    {
        return [
            [RefData::ROLE_OPERATOR_USER],
            [RefData::ROLE_OPERATOR_TM],
            [RefData::ROLE_PARTNER_ADMIN],
            [RefData::ROLE_PARTNER_USER],
            [RefData::ROLE_LOCAL_AUTHORITY_ADMIN],
            [RefData::ROLE_LOCAL_AUTHORITY_USER],
            [RefData::ROLE_INTERNAL_LIMITED_READ_ONLY],
            [RefData::ROLE_INTERNAL_READ_ONLY],
            [RefData::ROLE_INTERNAL_CASE_WORKER],
            [RefData::ROLE_INTERNAL_ADMIN],
            [RefData::ROLE_SYSTEM_ADMIN],
            [RefData::ROLE_INTERNAL_IRHP_ADMIN],
            [RefData::ROLE_OPERATOR_TC],
            [RefData::ROLE_ANON],
        ];
    }
}
