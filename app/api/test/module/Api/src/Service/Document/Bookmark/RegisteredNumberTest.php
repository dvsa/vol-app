<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\RegisteredNumber;

/**
 * Registered Number test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class RegisteredNumberTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new RegisteredNumber();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoRegisteredNumber(): void
    {
        $bookmark = new RegisteredNumber();
        $bookmark->setData(
            [
                'organisation' => null
            ]
        );

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithRegisteredNumber(): void
    {
        $bookmark = new RegisteredNumber();
        $bookmark->setData(
            [
                'organisation' => [
                    'companyOrLlpNo' => 'regNumber'
                ]
            ]
        );

        $this->assertEquals(
            'regNumber',
            $bookmark->render()
        );
    }
}
