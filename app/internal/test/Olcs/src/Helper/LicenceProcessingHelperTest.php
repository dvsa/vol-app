<?php

namespace OlcsTest\Helper;

use Olcs\Helper\LicenceProcessingHelper;

/**
 * Class LicenceProcessingHelper Test
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingHelperTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new LicenceProcessingHelper();

        parent::setUp();
    }

    /**
     * @dataProvider getNavigationProvider
     *
     * @param int $licenceId
     * @param string $activeSection
     * @param array $sections
     */
    public function testGetNavigation($licenceId, $activeSection, $sections)
    {
        $expected = [];

        //work out expected data
        foreach ($sections as $section => $val) {
            $expected[] = [
                'label' => 'internal-licence-processing-' . $section . '-label',
                'title' => 'internal-licence-processing-' . $section . '-title',
                'route' => 'licence/processing/' . $section,
                'use_route_match' => true,
                'params' => [
                    'licence' => $licenceId
                ],
                'active' => $activeSection == $section ? true : false
            ];
        }

        $this->sut->setSections($sections);

        $this->assertEquals($expected, $this->sut->getNavigation($licenceId, $activeSection));
    }

    public function getNavigationProvider()
    {
        return [
            [7, 'notes', ['notes' => [], 'tasks' => []]],
            [7, 'tasks', ['notes' => [], 'tasks' => []]]
        ];
    }
}
