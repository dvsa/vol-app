<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Domain\Repository\MasterTemplate as MasterTemplateRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterType;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Letter\MasterTemplateResolver;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * VOL-7305: MasterTemplateResolver picks the right MasterTemplate row for the
 * letter's region (isNi), falling back to the LetterType's base template if no
 * matching sibling exists.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(MasterTemplateResolver::class)]
class MasterTemplateResolverTest extends MockeryTestCase
{
    private m\MockInterface|MasterTemplateRepo $mockRepo;
    private MasterTemplateResolver $sut;

    protected function setUp(): void
    {
        $this->mockRepo = m::mock(MasterTemplateRepo::class);
        $this->sut = new MasterTemplateResolver($this->mockRepo);
    }

    public function testReturnsNullIfLetterTypeHasNoMasterTemplate(): void
    {
        $letterType = m::mock(LetterType::class);
        $letterType->shouldReceive('getMasterTemplate')->andReturn(null);

        $letter = $this->makeLetter($letterType, isNi: false);

        $this->assertNull($this->sut->resolve($letter));
    }

    public function testReturnsLetterTypeBaseWhenItAlreadyMatchesPreferredLocaleGb(): void
    {
        $base = $this->makeMasterTemplate('OTC Letter Chrome', MasterTemplate::LOCALE_EN_GB);
        $letterType = $this->makeLetterType($base);
        $letter = $this->makeLetter($letterType, isNi: false);

        // Already matches en_GB — no repo lookup needed
        $this->mockRepo->shouldNotReceive('findByNameAndLocale');

        $this->assertSame($base, $this->sut->resolve($letter));
    }

    public function testPivotsToNiSiblingWhenLicenceIsNi(): void
    {
        $base = $this->makeMasterTemplate('OTC Letter Chrome', MasterTemplate::LOCALE_EN_GB);
        $niSibling = $this->makeMasterTemplate('OTC Letter Chrome', MasterTemplate::LOCALE_EN_NI);

        $letterType = $this->makeLetterType($base);
        $letter = $this->makeLetter($letterType, isNi: true);

        $this->mockRepo->shouldReceive('findByNameAndLocale')
            ->with('OTC Letter Chrome', MasterTemplate::LOCALE_EN_NI)
            ->once()
            ->andReturn($niSibling);

        $this->assertSame($niSibling, $this->sut->resolve($letter));
    }

    public function testFallsBackToLetterTypeBaseWhenNoSiblingMatches(): void
    {
        $base = $this->makeMasterTemplate('OTC Letter Chrome', MasterTemplate::LOCALE_EN_GB);
        $letterType = $this->makeLetterType($base);

        // NI letter, but no en_NI sibling exists — should fall back to the base
        $letter = $this->makeLetter($letterType, isNi: true);

        $this->mockRepo->shouldReceive('findByNameAndLocale')
            ->with('OTC Letter Chrome', MasterTemplate::LOCALE_EN_NI)
            ->once()
            ->andReturn(null);

        $this->assertSame($base, $this->sut->resolve($letter));
    }

    public function testTreatsMissingLicenceAsGb(): void
    {
        $base = $this->makeMasterTemplate('OTC Letter Chrome', MasterTemplate::LOCALE_EN_GB);
        $letterType = $this->makeLetterType($base);

        // No licence at all — defaults to GB (isNi false)
        $letter = m::mock(LetterInstance::class);
        $letter->shouldReceive('getLetterType')->andReturn($letterType);
        $letter->shouldReceive('getLicence')->andReturn(null);

        $this->mockRepo->shouldNotReceive('findByNameAndLocale');

        $this->assertSame($base, $this->sut->resolve($letter));
    }

    // ----- helpers -----

    private function makeMasterTemplate(string $name, string $locale): MasterTemplate
    {
        $mt = new MasterTemplate();
        $mt->setName($name);
        $mt->setLocale($locale);
        return $mt;
    }

    private function makeLetterType(?MasterTemplate $base): m\MockInterface
    {
        $letterType = m::mock(LetterType::class);
        $letterType->shouldReceive('getMasterTemplate')->andReturn($base);
        return $letterType;
    }

    private function makeLetter(m\MockInterface $letterType, bool $isNi): m\MockInterface
    {
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('isNi')->andReturn($isNi);

        $letter = m::mock(LetterInstance::class);
        $letter->shouldReceive('getLetterType')->andReturn($letterType);
        $letter->shouldReceive('getLicence')->andReturn($licence);
        return $letter;
    }
}
