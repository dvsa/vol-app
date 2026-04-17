<?php

declare(strict_types=1);

/**
 * Generate People List Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace OlcsTest\View\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Helper\GeneratePeopleList;

/**
 * Generate People List Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class GeneratePeopleListTest extends MockeryTestCase
{
    /**
     * Test render no persons
     */
    public function testInvokeEmpty(): void
    {
        $sut = new GeneratePeopleList();

        $response = $sut($this->getTestPeopleArray(0), 'single');

        $this->assertEquals(
            [
                [
                    'label' => 'single',
                    'value' => ''
                ]
            ],
            $response
        );
    }

    /**
     * Test render single person
     */
    public function testInvokeSingle(): void
    {
        $sut = new GeneratePeopleList();

        $response = $sut($this->getTestPeopleArray(1), 'single');

        $this->assertEquals(
            [
                [
                    'label' => 'single',
                    'value' => 'John0 Smith0'
                ]
            ],
            $response
        );
    }

    /**
     * Test invoke multiple people
     */
    public function testInvokeMultiple(): void
    {
        $sut = new GeneratePeopleList();

        $response = $sut($this->getTestPeopleArray(2), 'multiple');

        $this->assertEquals(
            [
                [
                    'label' => 'multiple',
                    'value' => 'John0 Smith0'
                ],
                [
                    'label' => '',
                    'value' => 'John1 Smith1'
                ]
            ],
            $response
        );
    }

    /**
     * Test render no persons
     */
    public function testRenderEmpty(): void
    {
        $sut = new GeneratePeopleList();

        $response = $sut->render($this->getTestPeopleArray(0), 'single');

        $this->assertEquals(
            [
                [
                    'label' => 'single',
                    'value' => ''
                ]
            ],
            $response
        );
    }

    /**
     * Test render single person
     */
    public function testRenderSingle(): void
    {
        $sut = new GeneratePeopleList();

        $response = $sut->render($this->getTestPeopleArray(1), 'single');

        $this->assertEquals(
            [
                [
                    'label' => 'single',
                    'value' => 'John0 Smith0'
                ]
            ],
            $response
        );
    }

    /**
     * Test render multiple people
     */
    public function testRenderMultiple(): void
    {
        $sut = new GeneratePeopleList();

        $response = $sut->render($this->getTestPeopleArray(2), 'multiple');

        $this->assertEquals(
            [
                [
                    'label' => 'multiple',
                    'value' => 'John0 Smith0'
                ],
                [
                    'label' => '',
                    'value' => 'John1 Smith1'
                ]
            ],
            $response
        );
    }

    /**
     * Generates test data of $count people
     *
     * @param $count
     *
     * @return array
     *
     * @psalm-param 0|1|2 $count
     */
    private function getTestPeopleArray(int $count): array
    {
        $testData = [];
        for ($i = 0; $i < $count; $i++) {
            $testData[$i] = [
                'person' => [
                    'forename' => 'John' . $i,
                    'familyName' => 'Smith' . $i
                ]
            ];
        }

        return $testData;
    }
}
