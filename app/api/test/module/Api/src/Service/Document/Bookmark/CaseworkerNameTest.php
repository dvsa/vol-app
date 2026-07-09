<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\CaseworkerName;

/**
 * Case worker name test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class CaseworkerNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new CaseworkerName();
        $query = $bookmark->getQuery(['user' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new CaseworkerName();
        $bookmark->setData(
            [
                'contactDetails' => [
                    'person' => [
                        'forename' => 'Bob',
                        'familyName' => 'Smith'
                    ]
                ]
            ]
        );

        $this->assertEquals(
            'Bob Smith',
            $bookmark->render()
        );
    }

    public function testRenderFallbackIsLogged(): void
    {
        // This bookmark is shared with legacy RTF docgen, which used to fail loudly
        // on missing person data — the graceful fallback must at least be visible.
        $spyLogger = new class extends \Psr\Log\AbstractLogger {
            public array $records = [];

            public function log($level, $message, array $context = []): void
            {
                $this->records[] = [$level, (string) $message];
            }
        };
        \Olcs\Logging\Log\Logger::setLogger($spyLogger);

        try {
            $bookmark = new CaseworkerName();
            $bookmark->setData([]);

            $this->assertEquals('Caseworker', $bookmark->render());
            $this->assertNotEmpty(
                array_filter($spyLogger->records, static fn($r) => $r[0] === 'warning')
            );
        } finally {
            \Olcs\Logging\Log\Logger::setLogger(new \Psr\Log\NullLogger());
        }
    }
}
