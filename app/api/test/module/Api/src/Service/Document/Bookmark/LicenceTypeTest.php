<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\LicenceType;

/**
 * Licence type test.
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class LicenceTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new LicenceType();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new LicenceType();
        $bookmark->setData(
            [
                'goodsOrPsv' => [
                    'description' => 'foo'
                ],
                'licenceType' => [
                    'description' => 'bar'
                ]
            ]
        );

        $this->assertEquals('foo bar', $bookmark->render());
    }
}
