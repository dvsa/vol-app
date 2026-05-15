<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\Template\TemplateSource as TemplateSourceHandler;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Transfer\Query\Template\TemplateSource as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Template\Template as TemplateEntity;
use Mockery as m;

/**
 * Template Source Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class TemplateSourceTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = TemplateSourceHandler::class;
    protected $sutRepo = 'Template';
    protected $qryClass = QryClass::class;
    protected $repoClass = TemplateRepo::class;
    protected $entityClass = TemplateEntity::class;

    public function setUp(): void
    {
        $this->mockedSmServices = [
            'config' => ['email' => ['notify_test' => ['dsn' => null]]],
        ];
        parent::setUp();
    }

    /**
     * Overrides the generic base test because this handler also fetches siblings (VOL-7238)
     * and merges them into the result via the Result `values` arg.
     */
    public function testHandleQuery(): void
    {
        $query = QryClass::create(['id' => 1]);

        $siblings = [
            ['id' => 2, 'locale' => 'cy_GB', 'format' => 'html'],
            ['id' => 3, 'locale' => 'en_GB', 'format' => 'md'],
        ];

        $entity = m::mock($this->entityClass);
        $entity->shouldReceive('getName')->withNoArgs()->andReturn('auth-forgot-password');
        $entity->shouldReceive('getId')->withNoArgs()->andReturn(1);
        $entity->shouldReceive('serialize')->once()->with($this->bundle)->andReturn(['id' => 1, 'name' => 'auth-forgot-password']);

        $this->repoMap[$this->sutRepo]
            ->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($entity);
        $this->repoMap[$this->sutRepo]
            ->shouldReceive('fetchSiblings')->with('auth-forgot-password', 1)->once()->andReturn($siblings);

        $result = $this->sut->handleQuery($query)->serialize();

        self::assertSame('auth-forgot-password', $result['name']);
        self::assertSame($siblings, $result['siblings']);
        self::assertFalse($result['notifyTestEnabled']);
        self::assertSame('', $result['notifyTestHint']);
    }

    public function testNotifyTestEnabledWithMailpitDsnExposesMailpitHint(): void
    {
        $this->mockedSmServices = [
            'config' => ['email' => ['notify_test' => ['dsn' => 'govuknotify+mailpit://mailpit:1025']]],
        ];
        parent::setUp();

        $query = QryClass::create(['id' => 1]);

        $entity = m::mock($this->entityClass);
        $entity->shouldReceive('getName')->andReturn('some-template');
        $entity->shouldReceive('getId')->andReturn(1);
        $entity->shouldReceive('serialize')->andReturn(['id' => 1]);

        $this->repoMap[$this->sutRepo]
            ->shouldReceive('fetchUsingId')->andReturn($entity);
        $this->repoMap[$this->sutRepo]
            ->shouldReceive('fetchSiblings')->andReturn([]);

        $result = $this->sut->handleQuery($query)->serialize();

        self::assertTrue($result['notifyTestEnabled']);
        self::assertStringContainsString('Mailpit', $result['notifyTestHint']);
    }

    public function testNotifyTestEnabledWithRealNotifyDsnExposesSafelistHint(): void
    {
        $this->mockedSmServices = [
            'config' => ['email' => ['notify_test' => ['dsn' => 'govuknotify://test-key@api.notifications.service.gov.uk']]],
        ];
        parent::setUp();

        $query = QryClass::create(['id' => 1]);

        $entity = m::mock($this->entityClass);
        $entity->shouldReceive('getName')->andReturn('some-template');
        $entity->shouldReceive('getId')->andReturn(1);
        $entity->shouldReceive('serialize')->andReturn(['id' => 1]);

        $this->repoMap[$this->sutRepo]
            ->shouldReceive('fetchUsingId')->andReturn($entity);
        $this->repoMap[$this->sutRepo]
            ->shouldReceive('fetchSiblings')->andReturn([]);

        $result = $this->sut->handleQuery($query)->serialize();

        self::assertTrue($result['notifyTestEnabled']);
        self::assertStringContainsString('safelist', $result['notifyTestHint']);
    }
}
