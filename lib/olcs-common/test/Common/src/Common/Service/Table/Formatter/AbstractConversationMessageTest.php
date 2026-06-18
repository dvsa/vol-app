<?php

namespace CommonTest\Common\Service\Table\Formatter;

use Common\Service\Table\Formatter\AbstractConversationMessage;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ExternalConversationLinkLink test
 */
class AbstractConversationMessageTest extends MockeryTestCase
{
    /** @var AbstractConversationMessage */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = m::mock(AbstractConversationMessage::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $reflection = new \ReflectionClass($this->sut);
        $property = $reflection->getProperty('rowTemplate');
        $property->setAccessible(true);
        $property->setValue($this->sut, '<<<HTML
<div>
    <p>{senderName}</p>
    <time>{messageDate}</time>
    <div>{messageBody}</div>
    {caseworkerFooter}
    {fileList}
    {firstReadBy}
</div>
HTML;');

        date_default_timezone_set('Europe/London');
    }

    /**
     * @dataProvider messageFormatProvider
     */
    public function testFormatIncludesExpectedParts(
        array $row,
        string $expectedSenderName,
        string $expectedFileList,
        string $expectedReadBy,
    ): void {

        $this->sut->allows('getSenderName')
            ->with($row)
            ->andReturn($expectedSenderName);

        $this->sut->allows('getFileList')
            ->with($row)
            ->andReturn($expectedFileList);

        $this->sut->allows('getFirstReadBy')
            ->with($row)
            ->andReturn($expectedReadBy);

        $output = $this->sut->format($row);

        $expectedDate = (new \DateTimeImmutable($row['createdOn']))
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
            ->format('l j F Y \a\t H:ia');

        $this->assertStringContainsString($expectedDate, $output);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider messageFormatProvider
     */
    public function messageFormatProvider(): array
    {
        return [
            'external_user_message' => [
                [
                    'id' => 1,
                    'version' => 1,
                    'createdOn' => '2025-05-15T10:00:00+00:00', // Should convert to 11:00am in BST
                    'lastModifiedOn' => null,
                    'createdBy' => [
                        'accountDisabled' => 'N',
                        'disabledDate' => null,
                        'id' => 1,
                        'lastLoginAt' => null,
                        'loginId' => 'system',
                        'pid' => 'bbc5e661e106c6dcd8dc6dd186454c2fcba3c710fb4d8e71a60c93eaf077f073',
                        'translateToWelsh' => 'N',
                        'termsAgreed' => false,
                        'version' => 1,
                        'createdOn' => '2025-05-02T15:14:53+0000',
                        'lastModifiedOn' => '2025-05-02T15:14:53+0000',
                        'deletedDate' => null,
                        'contactDetails' => null,
                        'team' => null, // External user
                    ],
                    'lastModifiedBy' => null,
                    'messagingContent' => [
                        'id' => 1,
                        'text' => 'Hello from Operator',
                        'version' => 1,
                        'createdOn' => '2023-11-06T12:12:12+0000',
                        'lastModifiedOn' => null,
                    ],
                    'documents' => [],
                ],
                'Jane Caseworker',           // getSenderName()
                '',                          // getFileList()
                '',                          // getFirstReadBy()
            ],
            'internal_user_message_with_team' => [
                [
                    'id' => 2,
                    'version' => 1,
                    'createdOn' => '2025-01-15T10:00:00+00:00', // UTC in winter
                    'lastModifiedOn' => null,
                    'createdBy' => [
                        'id' => 2,
                        'team' => 'Licensing Team',
                        'contactDetails' => null,
                        'createdOn' => '2025-05-01T10:00:00+0000',
                        'lastModifiedOn' => '2025-05-01T10:00:00+0000',
                        'accountDisabled' => 'N',
                        'version' => 1,
                    ],
                    'lastModifiedBy' => null,
                    'messagingContent' => [
                        'id' => 2,
                        'text' => 'This is a caseworker message.',
                        'version' => 1,
                        'createdOn' => '2025-06-01T09:00:00+0000',
                        'lastModifiedOn' => null,
                    ],
                    'documents' => [],
                ],
                'Alex Caseworker',          // getSenderName()
                '',                         // getFileList()
                '',                         // getFirstReadBy()
            ],
        ];
    }
}
