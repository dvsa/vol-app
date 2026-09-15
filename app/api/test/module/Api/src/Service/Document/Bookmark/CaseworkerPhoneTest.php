<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\CaseworkerPhone;

/**
 * [[CASEWORKER_PHONE]] — the caseworker's direct dial on its own.
 *
 * The number was previously only reachable through CASEWORKER_DETAILS, which brings the
 * name, office, traffic area, address and email with it. Letter content that wants to
 * say "call me on X" had no way to ask for just the number.
 */
final class CaseworkerPhoneTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new CaseworkerPhone();
        $query = $bookmark->getQuery(['user' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testNoQueryWithoutAUserInScope(): void
    {
        $bookmark = new CaseworkerPhone();

        $this->assertNull($bookmark->getQuery([]));
    }

    public function testRendersThePrimaryNumber(): void
    {
        $bookmark = new CaseworkerPhone();
        $bookmark->setData(
            [
                'contactDetails' => [
                    'phoneContacts' => [
                        [
                            'phoneNumber' => '0113 111 2222',
                            'phoneContactType' => ['id' => 'phone_t_primary'],
                        ],
                    ],
                ],
            ]
        );

        $this->assertEquals('0113 111 2222', $bookmark->render());
    }

    /**
     * Matches the existing CASEWORKER_DETAILS behaviour, which falls back to the
     * secondary number so a caseworker who filled in only that one is still reachable.
     */
    public function testFallsBackToTheSecondaryNumber(): void
    {
        $bookmark = new CaseworkerPhone();
        $bookmark->setData(
            [
                'contactDetails' => [
                    'phoneContacts' => [
                        [
                            'phoneNumber' => '0113 999 8888',
                            'phoneContactType' => ['id' => 'phone_t_secondary'],
                        ],
                    ],
                ],
            ]
        );

        $this->assertEquals('0113 999 8888', $bookmark->render());
    }

    /**
     * The number is optional on a user record, so this is the ordinary case rather than
     * an error. Rendering empty lets the letter diagnostics report the empty grab; an
     * invented stand-in would print a wrong number to an operator.
     */
    public function testRendersEmptyWhenTheCaseworkerHasNoNumber(): void
    {
        $bookmark = new CaseworkerPhone();
        $bookmark->setData(['contactDetails' => ['phoneContacts' => []]]);

        $this->assertSame('', $bookmark->render());
    }

    public function testRendersEmptyWhenThereAreNoContactDetails(): void
    {
        $bookmark = new CaseworkerPhone();
        $bookmark->setData([]);

        $this->assertSame('', $bookmark->render());
    }
}
