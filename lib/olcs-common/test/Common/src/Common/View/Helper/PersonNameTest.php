<?php

/**
 * Test PersonName view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\PersonName;

/**
 * Test PersonName view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class PersonNameTest extends \PHPUnit\Framework\TestCase
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
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('personNameDataProvider')]
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
     * @return \Iterator<(int | string), array<(array<(array<string> | null)> | string)>>
     *
     * @psalm-return list{list{array{person: array{title: 't', forename: 'f', familyName: 's'}}, 't f s'}, list{array{person: array{forename: 'f', familyName: 's'}, fields: null}, 'f s'}, list{array{person: array{title: 't', forename: 'f', familyName: 's'}, fields: list{'title', 'familyName'}}, 't s'}}
     */
    public static function personNameDataProvider(): \Iterator
    {
        yield [ // include title
            [
                'person' => [
                    'title' => 't',
                    'forename' => 'f',
                    'familyName' => 's'
                ]
            ],
            't f s'
        ];
        yield [ // no title
            [
                'person' => [
                    'forename' => 'f',
                    'familyName' => 's'
                ],
                'fields' => null
            ],
            'f s'
        ];
        yield [ // include select fields
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
        ];
        yield [
            [
                'person' => [
                    'title' => ['description' => 'Mr'],
                    'forename' => 'John',
                    'familyName' => 'Doe'
                ],
                'fields' => null
            ],
            'Mr John Doe'
        ];
    }
}
