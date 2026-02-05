<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\SerialNoPrefix;

/**
 * Serial No Prefix test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class SerialNoPrefixTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new SerialNoPrefix();
        $query = $bookmark->getQuery(['communityLic' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNonZeroIssueNumber(): void
    {
        $bookmark = new SerialNoPrefix();
        $bookmark->setData(
            [
                'serialNoPrefix' => 'foo'
            ]
        );

        $this->assertEquals(
            'foo',
            $bookmark->render()
        );
    }
}
