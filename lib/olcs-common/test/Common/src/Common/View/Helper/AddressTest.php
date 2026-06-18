<?php

/**
 * Test Address view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace CommonTest\View\Helper;

use Common\View\Helper\Address;

/**
 * Test Address view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class AddressTest extends \PHPUnit\Framework\TestCase
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
     * @dataProvider addressDataProvider
     */
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
     * @return (((string|string[])[]|null)[]|string)[][]
     *
     * @psalm-return list{list{array{address: array{addressLine1: 'a1', addressLine2: 'a2', addressLine3: 'a3', town: 't', postcode: 'pc', countryCode: array{id: 'cc'}}}, 'a1, a2, a3, t, pc, cc'}, list{array{address: array{addressLine1: 'a1', addressLine2: 'a2', addressLine3: 'a3', town: 't', postcode: 'pc'}, fields: null}, 'a1, a2, a3, t, pc'}, list{array{address: array{addressLine1: 'a1', addressLine2: 'a2', addressLine3: 'a3', town: 't', postcode: 'pc', countryCode: array{id: 'cc'}}, fields: list{'addressLine1', 'addressLine3', 'postcode', 'countryCode'}}, 'a1, a3, pc, cc'}}
     */
    public function addressDataProvider(): array
    {
        return [
            [ // include countryCode
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
            ],
            [ // no country code
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
            ],
            [ // include select fields
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
            ],
        ];
    }
}
