<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\LongText;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\LongText as LongTextRepo;
use Dvsa\Olcs\Api\Entity\System\LongText as LongTextEntity;
use Dvsa\Olcs\Api\Service\EditorJs\LongTextConverterService;
use Dvsa\Olcs\Api\Service\LongText\LongTextTranslator;
use Laminas\I18n\Translator\Translator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LongTextTranslator::class)]
final class LongTextTranslatorTest extends MockeryTestCase
{
    private m\MockInterface $inner;

    private m\MockInterface $repo;

    private LongTextTranslator $sut;

    protected function setUp(): void
    {
        $this->inner = m::mock(Translator::class);
        $this->repo = m::mock(LongTextRepo::class);

        $this->sut = new LongTextTranslator(
            $this->inner,
            $this->repo,
            new LongTextConverterService(),
        );
    }

    public function testItRendersManagedContentInPlaceOfTheTranslation(): void
    {
        $entity = m::mock(LongTextEntity::class);
        $entity->shouldReceive('getContent')->andReturn([
            'blocks' => [['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'I declare that…']]],
        ]);

        $this->repo->shouldReceive('fetchByReferenceKey')
            ->once()
            ->with('application-undertakings-gv79', 'en_GB')
            ->andReturn($entity);

        $this->inner->shouldReceive('getLocale')->andReturn('en_GB');
        $this->inner->shouldNotReceive('translate');

        self::assertSame(
            '<p class="govuk-body">I declare that…</p>',
            $this->sut->translate('markup-application_undertakings_GV79'),
        );
    }

    public function testItFallsBackToTheExistingWordingWhenNothingIsManagedYet(): void
    {
        $this->repo->shouldReceive('fetchByReferenceKey')->once()->andThrow(new NotFoundException('nope'));
        $this->inner->shouldReceive('getLocale')->andReturn('en_GB');
        $this->inner->shouldReceive('translate')->once()->andReturn('<p>the wording in place today</p>');

        self::assertSame(
            '<p>the wording in place today</p>',
            $this->sut->translate('markup-application_undertakings_GV79'),
        );
    }

    public function testAnUnexpectedFailureFallsBackRatherThanBreakingThePage(): void
    {
        $this->repo->shouldReceive('fetchByReferenceKey')->once()->andThrow(new \RuntimeException('database gone'));
        $this->inner->shouldReceive('getLocale')->andReturn('en_GB');
        $this->inner->shouldReceive('translate')->once()->andReturn('<p>the wording in place today</p>');

        self::assertSame(
            '<p>the wording in place today</p>',
            $this->sut->translate('markup-application_undertakings_GV79'),
        );
    }

    public function testOrdinaryTranslationKeysNeverReachTheDatabase(): void
    {
        $this->repo->shouldNotReceive('fetchByReferenceKey');
        $this->inner->shouldReceive('translate')->once()->with('some.ordinary.key', 'default', null)->andReturn('Text');

        self::assertSame('Text', $this->sut->translate('some.ordinary.key'));
    }

    public function testTheSameKeyIsLookedUpOnlyOnce(): void
    {
        $this->repo->shouldReceive('fetchByReferenceKey')->once()->andThrow(new NotFoundException('nope'));
        $this->inner->shouldReceive('getLocale')->andReturn('en_GB');
        $this->inner->shouldReceive('translate')->twice()->andReturn('<p>fallback</p>');

        $this->sut->translate('markup-application_undertakings_GV79');
        $this->sut->translate('markup-application_undertakings_GV79');
    }
}
