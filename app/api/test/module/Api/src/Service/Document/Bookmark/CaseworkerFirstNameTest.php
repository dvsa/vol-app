<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\CaseworkerFirstName;

/**
 * [[CASEWORKER_FIRST_NAME]] — the caseworker's forename on its own.
 *
 * CASEWORKER_NAME renders "forename familyName", which is right for a signature block
 * but wrong mid-sentence; this exists so letter content can address the reader less
 * formally without also rewriting the sign-off.
 */
final class CaseworkerFirstNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new CaseworkerFirstName();
        $query = $bookmark->getQuery(['user' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    /**
     * A preview built before the letter has a creating user has no user in scope. The
     * query is skipped rather than run with a null id, matching CaseworkerName.
     */
    public function testNoQueryWithoutAUserInScope(): void
    {
        $bookmark = new CaseworkerFirstName();

        $this->assertNull($bookmark->getQuery([]));
    }

    public function testRendersTheForenameOnly(): void
    {
        $bookmark = new CaseworkerFirstName();
        $bookmark->setData(
            [
                'contactDetails' => [
                    'person' => [
                        'forename' => 'Bob',
                        'familyName' => 'Smith',
                    ],
                ],
            ]
        );

        $this->assertEquals('Bob', $bookmark->render());
    }

    /**
     * Renders empty rather than inventing a label. An empty grab is reported by the
     * letter diagnostics, whereas a stand-in like "Caseworker" would read as the
     * person's name mid-sentence and pass unnoticed.
     */
    public function testRendersEmptyWhenThereIsNoPersonData(): void
    {
        $bookmark = new CaseworkerFirstName();
        $bookmark->setData([]);

        $this->assertSame('', $bookmark->render());
    }

    public function testRendersEmptyWhenThePersonHasNoForename(): void
    {
        $bookmark = new CaseworkerFirstName();
        $bookmark->setData(['contactDetails' => ['person' => ['familyName' => 'Smith']]]);

        $this->assertSame('', $bookmark->render());
    }
}
