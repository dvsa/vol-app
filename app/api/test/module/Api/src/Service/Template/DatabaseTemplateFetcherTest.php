<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Template;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Api\Entity\Template\Template;
use Dvsa\Olcs\Api\Service\Template\DatabaseTemplateFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DatabaseTemplateFetcherTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DatabaseTemplateFetcherTest extends MockeryTestCase
{
    public function testFetch(): void
    {
        $localeComponent = 'en_GB';
        $formatComponent = 'plain';
        $nameComponent = 'send-ecmt-successful';

        $name = 'en_GB/plain/send-ecmt-successful';

        $template = m::mock(Template::class);

        $templateRepo = m::mock(TemplateRepo::class);
        $templateRepo->shouldReceive('fetchByLocaleFormatName')
            ->with($localeComponent, $formatComponent, $nameComponent)
            ->andReturn($template);

        $databaseTemplateFetcher = new DatabaseTemplateFetcher($templateRepo);
        $this->assertSame(
            $template,
            $databaseTemplateFetcher->fetch($name)
        );
    }

    public function testIncorrectComponentCountException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Incorrect number of path components');

        $name = 'blah/en_GB/plain/send-ecmt-successful';

        $templateRepo = m::mock(TemplateRepo::class);

        $databaseTemplateFetcher = new DatabaseTemplateFetcher($templateRepo);
        $databaseTemplateFetcher->fetch($name);
    }
}
