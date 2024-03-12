<?php

namespace OlcsTest\Helper;

use Olcs\Helper\ApplicationProcessingHelper;

/**
 * Class ApplicationProcessingHelper Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingHelperTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->sut = new ApplicationProcessingHelper();

        parent::setUp();
    }

    /**
     * @dataProvider getNavigationProvider
     *
     * @param int $applicationId
     * @param string $activeSection
     * @param array $sections
     */
    public function testGetNavigation($applicationId, $activeSection, $sections)
    {
        $expected = [];

        //work out expected data
        foreach ($sections as $section => $val) {
            $expected[] = [
                'label' => 'internal-application-processing-' . $section . '-label',
                'title' => 'internal-application-processing-' . $section . '-title',
                'route' => 'lva-application/processing/' . $section,
                'use_route_match' => true,
                'params' => [
                    'application' => $applicationId
                ],
                'active' => $activeSection == $section ? true : false
            ];
        }

        $this->sut->setSections($sections);

        $this->assertEquals($expected, $this->sut->getNavigation($applicationId, $activeSection));
    }

    public function getNavigationProvider()
    {
        return [
            [7, 'notes', ['notes' => [], 'tasks' => []]],
            [7, 'tasks', ['notes' => [], 'tasks' => []]]
        ];
    }
}
