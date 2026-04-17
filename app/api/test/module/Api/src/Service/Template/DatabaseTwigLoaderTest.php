<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Template;

use DateTime;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Service\Template\DatabaseTemplateFetcher;
use Dvsa\Olcs\Api\Service\Template\DatabaseTwigLoader;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Twig\Error\LoaderError;
use Twig\Source;

/**
 * DatabaseTwigLoaderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DatabaseTwigLoaderTest extends MockeryTestCase
{
    /** @var DatabaseTwigLoader */
    private $sut;

    /** @var DatabaseTemplateFetcher */
    private $databaseTemplateFetcher;

    public function setUp(): void
    {
        $this->databaseTemplateFetcher = m::mock(DatabaseTemplateFetcher::class);
        $this->sut = new DatabaseTwigLoader($this->databaseTemplateFetcher);
    }

    public function testGetSourceContext(): void
    {
        $name = 'en_GB/plain/send-ecmt-successful';
        $code = '{{var1}} test {{var2}}';

        $template = m::mock(Template::class);
        $template->shouldReceive('getSource')
            ->andReturn($code);

        $this->databaseTemplateFetcher->shouldReceive('fetch')
            ->with('en_GB/plain/send-ecmt-successful')
            ->andReturn($template);

        $source = $this->sut->getSourceContext($name);
        $this->assertInstanceOf(Source::class, $source);
        $this->assertEquals($code, $source->getCode());
        $this->assertEquals($name, $source->getName());
    }

    public function testGetSourceContextLoaderError(): void
    {
        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage('Template "en_GB/plain/send-ecmt-successfulddd" does not exist.');

        $name = 'en_GB/plain/send-ecmt-successfulddd';

        $this->databaseTemplateFetcher->shouldReceive('fetch')
            ->with($name)
            ->andThrow(new NotFoundException());

        $this->sut->getSourceContext($name);
    }

    public function testExistsTrue(): void
    {
        $name = 'en_GB/plain/send-ecmt-successful';

        $this->databaseTemplateFetcher->shouldReceive('fetch')
            ->with($name)
            ->andReturn(m::mock(Template::class));

        $this->assertTrue(
            $this->sut->exists($name)
        );
    }

    public function testExistsFalse(): void
    {
        $name = 'en_GB/plain/send-ecmt-successfulddd';

        $this->databaseTemplateFetcher->shouldReceive('fetch')
            ->with($name)
            ->andThrow(new NotFoundException());

        $this->assertFalse(
            $this->sut->exists($name)
        );
    }

    public function testGetCacheKey(): void
    {
        $name = 'en_GB/plain/send-ecmt-successful';

        $this->assertEquals(
            $name,
            $this->sut->getCacheKey($name)
        );
    }

    public function testIsFreshTrue(): void
    {
        $name = 'en_GB/plain/send-ecmt-successful';

        $lastModifiedOnDateTime = m::mock(DateTime::class);
        $lastModifiedOnDateTime->shouldReceive('getTimestamp')
            ->andReturn(12345600);

        $template = m::mock(Template::class);
        $template->shouldReceive('getLastModifiedOn')
            ->with(true)
            ->andReturn($lastModifiedOnDateTime);

        $this->databaseTemplateFetcher->shouldReceive('fetch')
            ->with($name)
            ->andReturn($template);

        $this->assertTrue(
            $this->sut->isFresh($name, 12345678)
        );
    }

    public function testIsFreshFalseLastModifiedAfterTime(): void
    {
        $name = 'en_GB/plain/send-ecmt-successful';

        $lastModifiedOnDateTime = m::mock(DateTime::class);
        $lastModifiedOnDateTime->shouldReceive('getTimestamp')
            ->andReturn(12345678);

        $template = m::mock(Template::class);
        $template->shouldReceive('getLastModifiedOn')
            ->with(true)
            ->andReturn($lastModifiedOnDateTime);

        $this->databaseTemplateFetcher->shouldReceive('fetch')
            ->with($name)
            ->andReturn($template);

        $this->assertFalse(
            $this->sut->isFresh($name, 12345600)
        );
    }

    public function testIsFreshFalseLastModifiedMissing(): void
    {
        $name = 'en_GB/plain/send-ecmt-successful';

        $template = m::mock(Template::class);
        $template->shouldReceive('getLastModifiedOn')
            ->with(true)
            ->andReturn(null);

        $this->databaseTemplateFetcher->shouldReceive('fetch')
            ->with($name)
            ->andReturn($template);

        $this->assertTrue(
            $this->sut->isFresh($name, 12345600)
        );
    }
}
