<?php

/**
 * Test PersonName view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace CommonTest\View\Helper;

use Common\View\Helper\PersonName;

/**
 * Test PersonName view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class PersonNameTest extends \PHPUnit\Framework\TestCase
{
    public $viewHelper;
    /**
     * Setup the view helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->viewHelper = new PersonName();
    }

    /**
     * Test invoke
     * @dataProvider personNameDataProvider
     */
    public function testInvokeDefaultFields($input, $expected): void
    {
        if (!empty($input['fields'])) {
            // specified fields to return
            $this->assertEquals($expected, $this->viewHelper->__invoke($input['person'], $input['fields']));
        } else {
            // use default
            $this->assertEquals($expected, $this->viewHelper->__invoke($input['person']));
        }
    }

    /**
     * @return ((null|string[])[]|string)[][]
     *
     * @psalm-return list{list{array{person: array{title: 't', forename: 'f', familyName: 's'}}, 't f s'}, list{array{person: array{forename: 'f', familyName: 's'}, fields: null}, 'f s'}, list{array{person: array{title: 't', forename: 'f', familyName: 's'}, fields: list{'title', 'familyName'}}, 't s'}}
     */
    public function personNameDataProvider(): array
    {
        return [
            [ // include title
                [
                    'person' => [
                        'title' => 't',
                        'forename' => 'f',
                        'familyName' => 's'
                    ]
                ],
                't f s'
            ],
            [ // no title
                [
                    'person' => [
                        'forename' => 'f',
                        'familyName' => 's'
                    ],
                    'fields' => null
                ],
                'f s'
            ],
            [ // include select fields
                [
                    'person' => [
                        'title' => 't',
                        'forename' => 'f',
                        'familyName' => 's'
                    ],
                    'fields' => [
                        'title',
                        'familyName'
                    ]
                ],
                't s'
            ],
            [
                [
                    'person' => [
                        'title' => ['description' => 'Mr'],
                        'forename' => 'John',
                        'familyName' => 'Doe'
                    ],
                    'fields' => null
                ],
                'Mr John Doe'
            ],
        ];
    }
}
