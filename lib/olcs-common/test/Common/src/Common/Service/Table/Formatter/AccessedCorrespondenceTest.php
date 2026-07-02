<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\AccessedCorrespondence;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Service\Table\Formatter\AccessedCorrespondence
 */
class AccessedCorrespondenceTest extends MockeryTestCase
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

    /**
     * @dataProvider formatProvider
     */
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
        static::assertEquals($expected, $sut->format($data, []));
    }


    /**
     * @return ((int|string|string[])[][]|bool|string)[][]
     *
     * @psalm-return list{array{data: array{correspondence: array{id: 1, accessed: 'N', document: array{description: 'Description', filename: 'filename.doc'}}}, isNew: true, expect: '<a class="govuk-link" href="LICENCE_URL"><b>Description (doc)</b></a><span class="status green">unit_New</span> '}, array{data: array{correspondence: array{id: 1, accessed: 'Y', document: array{description: 'Description', filename: 'filename.doc'}}}, isNew: false, expect: '<a class="govuk-link" href="LICENCE_URL"><b>Description (doc)</b></a>'}, array{data: array{correspondence: array{id: 1, accessed: 'Y', document: array{description: 'Description', filename: 'filename'}}}, isNew: false, expect: '<a class="govuk-link" href="LICENCE_URL"><b>Description</b></a>'}}
     */
    public function formatProvider(): array
    {
        return [
            [
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
                'expect' => '<a class="govuk-link" href="LICENCE_URL" target="_blank"><b>Description (doc)</b></a>' .
                    '<span class="status green">unit_New</span> ',
            ],
            [
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
                'expect' => '<a class="govuk-link" href="LICENCE_URL" target="_blank"><b>Description (doc)</b></a>',
            ],
            [
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
                'expect' => '<a class="govuk-link" href="LICENCE_URL" target="_blank"><b>Description</b></a>',
            ],
        ];
    }
}
