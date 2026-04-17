<?php

declare(strict_types=1);

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
    public $sut;

    public function setUp(): void
    {
        $this->sut = new ApplicationProcessingHelper();

        parent::setUp();
    }

    /**
     *
     * @param int $applicationId
     * @param string $activeSection
     * @param array $sections
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getNavigationProvider')]
    public function testGetNavigation(mixed $applicationId, mixed $activeSection, mixed $sections): void
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

    public static function getNavigationProvider(): array
    {
        return [
            [7, 'notes', ['notes' => [], 'tasks' => []]],
            [7, 'tasks', ['notes' => [], 'tasks' => []]]
        ];
    }
}
