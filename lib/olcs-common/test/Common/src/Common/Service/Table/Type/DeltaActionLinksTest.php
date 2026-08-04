<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Type;

use Common\Service\Table\TableBuilder;
use Common\Util\Escape;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator as Translator;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Table\Type\DeltaActionLinks;

/**
 * DeltaActionLink Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class DeltaActionLinksTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    #[\Override]
    protected function setUp(): void
    {
        $this->sm = new ServiceManager();

        $table = m::mock(TableBuilder::class);
        $table->expects('getServiceLocator')
            ->andReturn($this->sm);

        $this->sut = new DeltaActionLinks($table);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('tableDataProvider')]
    public function testRender($data, $expected): void
    {
        $mockTranslate = $this->getTranslator($data['action'] ?? null);

        $this->sm->setService('translator', $mockTranslate);

        $this->assertEquals($expected, $this->sut->render($data, []));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(int | string)> | string)>>
     *
     * @psalm-return list{list{array{id: 123, action: 'A'}, string}, list{array{id: 456, action: 'D'}, string}, list{array{id: 789}, ''}}
     */
    public static function tableDataProvider(): \Iterator
    {
        $escapedAriaRemove = Escape::htmlAttr('Remove Aria (id 123)');
        $escapedAriaRestore = Escape::htmlAttr('Restore Aria (id 456)');
        yield [
            [
                'id' => 123,
                'action' => 'A'
            ],
            '<button data-prevent-double-click="true" data-module="govuk-button" type="submit" class="right-aligned govuk-button govuk-button--secondary trigger-modal" ' .
                'name="table[action][delete][123]" aria-label="' . $escapedAriaRemove . '">Remove</button>'
        ];
        yield [
            [
                'id' => 456,
                'action' => 'D'
            ],
            '<button data-prevent-double-click="true" data-module="govuk-button" type="submit" class="right-aligned govuk-button govuk-button--secondary" ' .
                'name="table[action][restore][456]" aria-label="' . $escapedAriaRestore . '">Restore</button>'
        ];
        yield [
            [
                'id' => 789
            ],
            ''
        ];
    }

    private function getTranslator(?string $action): m\MockInterface
    {
        if ($action === null) {
            $removeTimes = 0;
            $restoreTimes = 0;
        } elseif ($action === 'A') {
            $removeTimes = 1;
            $restoreTimes = 0;
        } else {
            $removeTimes = 0;
            $restoreTimes = 1;
        }

        $translator = m::mock(Translator::class);
        $translator->expects('translate')->with(DeltaActionLinks::KEY_ACTION_LINKS_REMOVE)->andReturn('Remove')->times($removeTimes);
        $translator->expects('translate')->with(DeltaActionLinks::KEY_ACTION_LINKS_REMOVE_ARIA)->andReturn('Remove Aria')->times($removeTimes);
        ;
        $translator->expects('translate')->with(DeltaActionLinks::KEY_ACTION_LINKS_RESTORE)->andReturn('Restore')->times($restoreTimes);
        ;
        $translator->expects('translate')->with(DeltaActionLinks::KEY_ACTION_LINKS_RESTORE_ARIA)->andReturn('Restore Aria')->times($restoreTimes);
        ;

        return $translator;
    }
}
