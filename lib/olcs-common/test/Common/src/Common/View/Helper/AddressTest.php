<?php

/**
 * Test Address view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\Address;

/**
 * Test Address view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class AddressTest extends \PHPUnit\Framework\TestCase
{
    public $viewHelper;
    /**
     * Setup the view helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->viewHelper = new Address();
    }

    /**
     * Test invoke
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('addressDataProvider')]
    public function testInvokeDefaultFields($input, $expected): void
    {
        if (!empty($input['fields'])) {
            // specified fields to return
            $this->assertEquals($expected, $this->viewHelper->__invoke($input['address'], $input['fields']));
        } else {
            // use default
            $this->assertEquals($expected, $this->viewHelper->__invoke($input['address']));
        }
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<(array<string> | string)> | null)> | string)>>
     *
     * @psalm-return list{list{array{address: array{addressLine1: 'a1', addressLine2: 'a2', addressLine3: 'a3', town: 't', postcode: 'pc', countryCode: array{id: 'cc'}}}, 'a1, a2, a3, t, pc, cc'}, list{array{address: array{addressLine1: 'a1', addressLine2: 'a2', addressLine3: 'a3', town: 't', postcode: 'pc'}, fields: null}, 'a1, a2, a3, t, pc'}, list{array{address: array{addressLine1: 'a1', addressLine2: 'a2', addressLine3: 'a3', town: 't', postcode: 'pc', countryCode: array{id: 'cc'}}, fields: list{'addressLine1', 'addressLine3', 'postcode', 'countryCode'}}, 'a1, a3, pc, cc'}}
     */
    public static function addressDataProvider(): \Iterator
    {
        yield [ // include countryCode
            [
                'address' => [
                    'addressLine1' => 'a1',
                    'addressLine2' => 'a2',
                    'addressLine3' => 'a3',
                    'town' => 't',
                    'postcode' => 'pc',
                    'countryCode' => ['id' => 'cc']
                ]
            ],
            'a1, a2, a3, t, pc, cc'
        ];
        yield [ // no country code
            [
                'address' => [
                    'addressLine1' => 'a1',
                    'addressLine2' => 'a2',
                    'addressLine3' => 'a3',
                    'town' => 't',
                    'postcode' => 'pc'
                ],
                'fields' => null
             ],
            'a1, a2, a3, t, pc'
        ];
        yield [ // include select fields
            [
                'address' => [
                    'addressLine1' => 'a1',
                    'addressLine2' => 'a2',
                    'addressLine3' => 'a3',
                    'town' => 't',
                    'postcode' => 'pc',
                    'countryCode' => ['id' => 'cc']
                ],
                'fields' => [
                    'addressLine1',
                    'addressLine3',
                    'postcode',
                    'countryCode'
                ]
            ],
            'a1, a3, pc, cc'
        ];
    }
}
