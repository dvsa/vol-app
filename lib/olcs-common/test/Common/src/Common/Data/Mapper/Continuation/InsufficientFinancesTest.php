<?php

namespace CommonTest\Data\Mapper\Continuation;

use Common\Data\Mapper\Continuation\InsufficientFinances;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Data\Mapper\Continuation\InsufficientFinances
 */
class InsufficientFinancesTest extends MockeryTestCase
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

    /**
     * @dataProvider dataProviderTestMapFromResult
     */
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
     * @return (bool|null|string)[][]
     *
     * @psalm-return array{'financialEvidenceUploaded = null': list{null, null, null}, 'financialEvidenceUploaded = true': list{true, 'Y', 'upload'}, 'financialEvidenceUploaded = false': list{false, 'Y', 'send'}}
     */
    public function dataProviderTestMapFromResult(): array
    {
        return [
            'financialEvidenceUploaded = null' => [null, null, null],
            'financialEvidenceUploaded = true' => [true, 'Y', 'upload'],
            'financialEvidenceUploaded = false' => [false, 'Y', 'send'],
        ];
    }

    /**
     * @dataProvider dataProviderTestMapFromForm
     */
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
     * @return (bool|string)[][]
     *
     * @psalm-return array{'radio = upload': list{'upload', true}, 'radio = send': list{'send', false}}
     */
    public function dataProviderTestMapFromForm(): array
    {
        return [
            'radio = upload' => ['upload', true],
            'radio = send' => ['send', false],
        ];
    }
}
