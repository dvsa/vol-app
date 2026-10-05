<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\LongText;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\LongText as LongTextRepo;
use Dvsa\Olcs\Api\Entity\System\LongText as LongTextEntity;
use Dvsa\Olcs\Api\Service\EditorJs\LongTextConverterService;
use Dvsa\Olcs\Api\Service\LongText\LongTextTranslator;
use Dvsa\Olcs\Utils\Translation\Replacements;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\I18n\Translator\Translator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use Psr\Log\LoggerInterface;
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

    public function testItUsesTheLocaleFromTheProductionTranslatorWrapper(): void
    {
        $entity = m::mock(LongTextEntity::class);
        $entity->shouldReceive('getContent')->andReturn([
            'blocks' => [['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'Datganiad']]],
        ]);

        $this->inner->shouldReceive('getLocale')->once()->andReturn('cy_NI');
        $selectedLocale = null;
        $this->repo->shouldReceive('fetchByReferenceKey')
            ->once()
            ->withArgs(function (string $referenceKey, string $locale) use (&$selectedLocale): bool {
                $selectedLocale = $locale;

                return $referenceKey === 'application-undertakings-gv79';
            })
            ->andReturn($entity);

        $sut = new LongTextTranslator(
            new TranslatorDelegator($this->inner, new Replacements([])),
            $this->repo,
            new LongTextConverterService(),
        );

        self::assertSame(
            '<p class="govuk-body">Datganiad</p>',
            $sut->translate('markup-application_undertakings_GV79'),
        );
        self::assertSame('cy_NI', $selectedLocale);
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

    public function testAnUnexpectedFailureStopsThePageRatherThanServingStaleWording(): void
    {
        Logger::setLogger(m::mock(LoggerInterface::class)->shouldIgnoreMissing());

        $this->repo->shouldReceive('fetchByReferenceKey')->once()->andThrow(new \RuntimeException('database gone'));
        $this->inner->shouldReceive('getLocale')->andReturn('en_GB');
        $this->inner->shouldNotReceive('translate');

        $this->expectException(\RuntimeException::class);

        $this->sut->translate('markup-application_undertakings_GV79');
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

    public function testContentThatIsSimplyNotManagedYetIsNotReportedAsAFault(): void
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldNotReceive('error');
        $logger->shouldReceive('debug')->zeroOrMoreTimes();
        Logger::setLogger($logger);

        $this->repo->shouldReceive('fetchByReferenceKey')->andThrow(new NotFoundException('nope'));
        $this->inner->shouldReceive('getLocale')->andReturn('en_GB');
        $this->inner->shouldReceive('translate')->andReturn('<p>today</p>');

        $this->sut->translate('markup-application_undertakings_GV79');
    }

    public function testAnythingElseIsReportedLoudlyEnoughToSeeInProduction(): void
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
            ->withArgs(fn(string $message): bool => str_contains($message, 'markup-application_undertakings_GV79')
                && str_contains($message, 'database gone'));
        Logger::setLogger($logger);

        $this->repo->shouldReceive('fetchByReferenceKey')->andThrow(new \RuntimeException('database gone'));
        $this->inner->shouldReceive('getLocale')->andReturn('en_GB');

        try {
            $this->sut->translate('markup-application_undertakings_GV79');
        } catch (\RuntimeException) {
            // reported before it is rethrown
        }
    }
}
