<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\DeclarationReviewService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\I18n\Translator\TranslatorInterface;

class DeclarationReviewServiceTest extends MockeryTestCase
{
    use \Dvsa\Olcs\Snapshot\Service\Snapshots\FormatReviewDataTrait;

    /** @var DeclarationReviewService */
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->sut = new DeclarationReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $mockEntity = m::mock(Surrender::class);

        $mockEntity->shouldReceive('getLicence->getLicNo')->andReturn(7)->once();

        $this->mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($expected) => $expected . '-translated'
            );

        $expected = ['markup' => 'markup-licence-surrender-declaration-translated'];

        $this->assertEquals($expected, $this->sut->getConfigFromData($mockEntity));
    }
}
