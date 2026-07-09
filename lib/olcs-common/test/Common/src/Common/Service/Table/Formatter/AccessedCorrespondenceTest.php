<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\AccessedCorrespondence;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Service\Table\Formatter\AccessedCorrespondence
 */
final class AccessedCorrespondenceTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $translator;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslatorDelegator::class);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, $isNew, $expected): void
    {

        $this->urlHelper->shouldReceive('fromRoute')
            ->with(
                'correspondence/access',
                [
                    'correspondenceId' => $data['correspondence']['id'],
                ]
            )
            ->andReturn('LICENCE_URL');

        if ($isNew) {
            $this->translator->shouldReceive('translate')->once()->andReturn('unit_New');
        }

        $sut = new AccessedCorrespondence($this->urlHelper, $this->translator);
        $this->assertEquals($expected, $sut->format($data, []));
    }


    /**
     * @return \Iterator<(int | string), array<(array<array<(array<string> | int | string)>> | bool | string)>>
     *
     * @psalm-return list{array{data: array{correspondence: array{id: 1, accessed: 'N', document: array{description: 'Description', filename: 'filename.doc'}}}, isNew: true, expect: '<a class="govuk-link" href="LICENCE_URL"><b>Description (doc)</b></a><span class="status green">unit_New</span> '}, array{data: array{correspondence: array{id: 1, accessed: 'Y', document: array{description: 'Description', filename: 'filename.doc'}}}, isNew: false, expect: '<a class="govuk-link" href="LICENCE_URL"><b>Description (doc)</b></a>'}, array{data: array{correspondence: array{id: 1, accessed: 'Y', document: array{description: 'Description', filename: 'filename'}}}, isNew: false, expect: '<a class="govuk-link" href="LICENCE_URL"><b>Description</b></a>'}}
     */
    public static function formatProvider(): \Iterator
    {
        yield [
            'data' => [
                'correspondence' => [
                    'id' => 1,
                    'accessed' => 'N',
                    'document' => [
                        'description' => 'Description',
                        'filename' => 'filename.doc'
                    ],
                ],
            ],
            'isNew' => true,
            'expected' => '<a class="govuk-link" href="LICENCE_URL" target="_blank"><b>Description (doc)</b></a>' .
                '<span class="status green">unit_New</span> ',
        ];
        yield [
            'data' => [
                'correspondence' => [
                    'id' => 1,
                    'accessed' => 'Y',
                    'document' => [
                        'description' => 'Description',
                        'filename' => 'filename.doc'
                    ],
                ],
            ],
            'isNew' => false,
            'expected' => '<a class="govuk-link" href="LICENCE_URL" target="_blank"><b>Description (doc)</b></a>',
        ];
        yield [
            'data' => [
                'correspondence' => [
                    'id' => 1,
                    'accessed' => 'Y',
                    'document' => [
                        'description' => 'Description',
                        'filename' => 'filename'
                    ],
                ],
            ],
            'isNew' => false,
            'expected' => '<a class="govuk-link" href="LICENCE_URL" target="_blank"><b>Description</b></a>',
        ];
    }
}
