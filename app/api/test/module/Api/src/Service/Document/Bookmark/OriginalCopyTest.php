<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\OriginalCopy;

/**
 * Original Copy test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OriginalCopyTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new OriginalCopy();
        $query = $bookmark->getQuery(['communityLic' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithZeroIssueNumber(): void
    {
        $bookmark = new OriginalCopy();
        $bookmark->setData(
            [
                'issueNo' => 0
            ]
        );

        $this->assertEquals(
            'LICENCE',
            $bookmark->render()
        );
    }

    public function testRenderWithNonZeroIssueNumber(): void
    {
        $bookmark = new OriginalCopy();
        $bookmark->setData(
            [
                'issueNo' => 5
            ]
        );

        $this->assertEquals(
            'CERTIFIED TRUE COPY',
            $bookmark->render()
        );
    }
}
