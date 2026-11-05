<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Notify;

use Dvsa\Olcs\Api\Domain\Repository\NotifyMessage as Repo;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\Notify\NotifyMessageRecorder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(NotifyMessageRecorder::class)]
final class NotifyMessageRecorderTest extends MockeryTestCase
{
    private $repo;
    private NotifyMessageRecorder $sut;

    protected function setUp(): void
    {
        $this->repo = m::mock(Repo::class);
        $repos = m::mock(ContainerInterface::class);
        $repos->shouldReceive('get')->with('NotifyMessage')->andReturn($this->repo);
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('RepositoryServiceManager')->andReturn($repos);

        $this->repo->shouldReceive('getRefdataReference')->andReturnUsing(
            fn(string $id) => new RefData($id)
        );

        $this->sut = new NotifyMessageRecorder($container);
    }

    public function testWritesRowWithContext(): void
    {
        $refs = [];
        $this->repo->shouldReceive('getReference')->andReturnUsing(
            function (string $class, $id) use (&$refs) {
                $refs[] = [$class, $id];
                return m::mock($class);
            }
        );

        $saved = null;
        $this->repo->shouldReceive('save')->once()->andReturnUsing(function ($e) use (&$saved) {
            $saved = $e;
        });

        $this->sut->recordEmail(
            'a@example.com',
            [
                'organisation' => 1,
                'licence' => 2,
                'document' => 3,
                'correspondenceInbox' => 4,
                'user' => 5,
                'templateName' => 'licensing-information-standard',
                'description' => 'Subject',
                'notifyTemplateId' => 'tpl-id',
                'redirectedTo' => 'redirect@example.com',
            ],
            'notify-id-1',
            null,
            null
        );

        /** @var NotifyMessage $saved */
        $this->assertInstanceOf(NotifyMessage::class, $saved);
        $this->assertSame(NotifyMessage::CHANNEL_EMAIL, $saved->getChannel()->getId());
        $this->assertSame(NotifyMessage::STATUS_CREATED, $saved->getStatus()->getId());
        $this->assertSame('a@example.com', $saved->getRecipient());
        $this->assertSame('redirect@example.com', $saved->getRedirectedTo());
        $this->assertSame('notify-id-1', $saved->getNotifyId());
        $this->assertSame('tpl-id', $saved->getNotifyTemplateId());
        $this->assertSame('licensing-information-standard', $saved->getTemplateName());
        $this->assertSame('Subject', $saved->getDescription());
        $this->assertSame(1, $saved->getAttempts());
        $this->assertSame(\DateTime::class, $saved->getRequestedOn()::class);
        $this->assertSame(\DateTime::class, $saved->getSentOn()::class);
        $this->assertSame(\DateTime::class, $saved->getStatusUpdatedOn()::class);
        $this->assertNull($saved->getErrorCode());
        $this->assertSame(
            [
                [Organisation::class, 1],
                [Licence::class, 2],
                [Document::class, 3],
                [CorrespondenceInbox::class, 4],
                [User::class, 5],
            ],
            $refs
        );
    }

    public function testWritesRejectedRowWithError(): void
    {
        $saved = null;
        $this->repo->shouldReceive('save')->once()->andReturnUsing(function ($e) use (&$saved) {
            $saved = $e;
        });

        $this->sut->recordEmail('bad@example.com', [], null, '400', str_repeat('x', 2000));

        $this->assertSame(NotifyMessage::STATUS_REJECTED, $saved->getStatus()->getId());
        $this->assertNull($saved->getNotifyId());
        $this->assertSame('400', $saved->getErrorCode());
        $this->assertSame(1024, mb_strlen($saved->getErrorMessage()));
        $this->assertNull($saved->getSentOn());
        $this->assertSame(\DateTime::class, $saved->getCompletedOn()::class);
    }
}
