<?php

declare(strict_types=1);

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class BrCouncilsNotifiedTest extends \PHPUnit\Framework\TestCase
{
    public const SUT_CLASS_NAME = \Dvsa\Olcs\Api\Service\Document\Bookmark\BrCouncilsNotified::class;

    public function testGetQuery(): void
    {
        $sutClassName = static::SUT_CLASS_NAME;

        $busRegId = '123';

        $bookmark = new $sutClassName();

        $this->assertTrue(is_null($bookmark->getQuery([])));

        $query = $bookmark->getQuery(['busRegId' => $busRegId]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $sutClassName = static::SUT_CLASS_NAME;

        $bookmark = new $sutClassName();

        $data = [
            'id' => '123',
            'localAuthoritys' => [
                0 => ['description' => 'LA 1'],
                1 => ['description' => 'L A2'],
            ]
        ];

        $bookmark->setData($data);

        $this->assertEquals('LA 1, L A2', $bookmark->render());
    }
}
