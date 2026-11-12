<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Notify;

use Dvsa\Olcs\Api\Service\Notify\NotifyMessageRecorder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(NotifyMessageRecorder::class)]
final class NotifyMessageRecorderTest extends MockeryTestCase
{
    private $conn;
    private $em;
    private NotifyMessageRecorder $sut;

    protected function setUp(): void
    {
        $this->conn = m::mock(\Doctrine\DBAL\Connection::class);
        $this->em = m::mock(\Doctrine\ORM\EntityManagerInterface::class);
        $this->em->shouldReceive('getConnection')->andReturn($this->conn);
        $this->em->shouldNotReceive('flush');
        $this->em->shouldNotReceive('persist');
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('doctrine.entitymanager.orm_default')->andReturn($this->em);

        $this->sut = new NotifyMessageRecorder($container);
    }

    public function testWritesRowWithContext(): void
    {
        $this->conn->shouldReceive('isTransactionActive')->andReturn(false);
        $row = null;
        $this->conn->shouldReceive('insert')->once()->andReturnUsing(function ($table, $r) use (&$row) {
            $this->assertSame('notify_message', $table);
            $row = $r;
            return 1;
        });

        $this->sut->recordEmail(
            'a@example.com',
            [
                'organisation' => 1, 'licence' => 2, 'document' => 3, 'correspondenceInbox' => 4, 'user' => 5,
                'templateName' => 'licensing-information-standard', 'description' => 'Subject',
                'notifyTemplateId' => 'tpl-id', 'redirectedTo' => 'redirect@example.com',
            ],
            'notify-id-1',
            null,
            null
        );

        $this->assertSame('nmc_email', $row['channel']);
        $this->assertSame('nms_created', $row['status']);
        $this->assertSame('a@example.com', $row['recipient']);
        $this->assertSame('redirect@example.com', $row['redirected_to']);
        $this->assertSame('notify-id-1', $row['notify_id']);
        $this->assertSame('tpl-id', $row['notify_template_id']);
        $this->assertSame('licensing-information-standard', $row['template_name']);
        $this->assertSame('Subject', $row['description']);
        $this->assertSame(1, $row['attempts']);
        $this->assertSame(1, $row['version']);
        $this->assertSame(1, $row['organisation_id']);
        $this->assertSame(2, $row['licence_id']);
        $this->assertNull($row['application_id']);
        $this->assertSame(3, $row['document_id']);
        $this->assertSame(4, $row['correspondence_inbox_id']);
        $this->assertSame(5, $row['user_id']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $row['requested_on']);
        $this->assertSame($row['requested_on'], $row['sent_on']);
        $this->assertSame($row['requested_on'], $row['created_on']);
        $this->assertSame($row['requested_on'], $row['status_updated_on']);
        $this->assertNull($row['completed_on']);
        $this->assertNull($row['error_code']);
    }

    public function testAcceptedWithoutNotifyIdIsCreated(): void
    {
        $this->conn->shouldReceive('isTransactionActive')->andReturn(false);
        $this->conn->shouldReceive('insert')->once()->with('notify_message', m::on(
            fn($r) => $r['status'] === 'nms_created' && $r['notify_id'] === null && $r['sent_on'] !== null
        ));

        $this->sut->recordEmail('a@example.com', [], null, null, null);
    }

    public function testWritesRejectedRowWithError(): void
    {
        $this->conn->shouldReceive('isTransactionActive')->andReturn(false);
        $row = null;
        $this->conn->shouldReceive('insert')->once()->andReturnUsing(function ($t, $r) use (&$row) {
            $row = $r;
            return 1;
        });

        $this->sut->recordEmail('bad@example.com', [], null, '400', str_repeat('x', 2000));

        $this->assertSame('nms_rejected', $row['status']);
        $this->assertNull($row['notify_id']);
        $this->assertSame('400', $row['error_code']);
        $this->assertSame(1024, mb_strlen($row['error_message']));
        $this->assertNull($row['sent_on']);
        $this->assertNotNull($row['completed_on']);
    }

    public function testInsertUsesSavepointInsideTransaction(): void
    {
        $this->conn->shouldReceive('isTransactionActive')->andReturn(true);
        $this->conn->shouldReceive('createSavepoint')->once()->ordered();
        $this->conn->shouldReceive('insert')->once()->ordered();
        $this->conn->shouldReceive('releaseSavepoint')->once()->ordered();

        $this->sut->recordEmail('a@example.com', [], 'id', null, null);
    }

    public function testFailedInsertRollsBackSavepoint(): void
    {
        $this->conn->shouldReceive('isTransactionActive')->andReturn(true);
        $this->conn->shouldReceive('createSavepoint')->once();
        $this->conn->shouldReceive('insert')->once()->andThrow(new \RuntimeException('boom'));
        $this->conn->shouldReceive('rollbackSavepoint')->once();
        $this->conn->shouldNotReceive('releaseSavepoint');

        $this->expectException(\RuntimeException::class);
        $this->sut->recordEmail('a@example.com', [], 'id', null, null);
    }
}
