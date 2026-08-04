<?php

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Continuation;

use Common\Data\Mapper\Continuation\InsufficientFinances;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Data\Mapper\Continuation\InsufficientFinances::class)]
final class InsufficientFinancesTest extends MockeryTestCase
{
    /**
     * @var InsufficientFinances
     */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new InsufficientFinances();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestMapFromResult')]
    public function testMapFromResult($financialEvidenceUploaded, $expectedYesNo, $expectedRadio): void
    {
        $data = [
            'version' => 99,
            'financialEvidenceUploaded' => $financialEvidenceUploaded,
        ];

        $expected = [
            'version' => 99,
            'insufficientFinances' => [
                'yesNo' => $expectedYesNo,
                'yesContent' => [
                    'radio' => $expectedRadio,
                ],
            ],
        ];

        $this->assertSame($expected, $this->sut->mapFromResult($data));
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string | null)>>
     *
     * @psalm-return array{'financialEvidenceUploaded = null': list{null, null, null}, 'financialEvidenceUploaded = true': list{true, 'Y', 'upload'}, 'financialEvidenceUploaded = false': list{false, 'Y', 'send'}}
     */
    public static function dataProviderTestMapFromResult(): \Iterator
    {
        yield 'financialEvidenceUploaded = null' => [null, null, null];
        yield 'financialEvidenceUploaded = true' => [true, 'Y', 'upload'];
        yield 'financialEvidenceUploaded = false' => [false, 'Y', 'send'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestMapFromForm')]
    public function testMapFromForm($radio, $expectedFinancialEvidenceUploaded): void
    {
        $formData = [
            'version' => 99,
            'insufficientFinances' => [
                'yesNo' => 'FOO',
                'yesContent' => [
                    'radio' => $radio,
                ],
            ],
        ];

        $expected = [
            'version' => 99,
            'financialEvidenceUploaded' => $expectedFinancialEvidenceUploaded,
        ];

        $this->assertSame($expected, $this->sut->mapFromForm($formData));
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string)>>
     *
     * @psalm-return array{'radio = upload': list{'upload', true}, 'radio = send': list{'send', false}}
     */
    public static function dataProviderTestMapFromForm(): \Iterator
    {
        yield 'radio = upload' => ['upload', true];
        yield 'radio = send' => ['send', false];
    }
}
