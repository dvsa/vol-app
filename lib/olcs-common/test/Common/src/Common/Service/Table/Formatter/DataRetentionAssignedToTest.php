<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\DataRetentionAssignedTo;
use Common\View\Helper\PersonName;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Service\Table\Formatter\DataRetentionAssignedTo
 */
class DataRetentionAssignedToTest extends MockeryTestCase
{
    protected $viewHelperManager;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->viewHelperManager = m::mock(HelperPluginManager::class);
        $this->sut = new DataRetentionAssignedTo($this->viewHelperManager);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Tests empty string returned if there's no person information
     */
    public function testFormatUnassigned(): void
    {
        $this->assertEquals('', $this->sut->format([]));
    }

    /**
     * Tests the formatter calls the person helper correctly
     */
    public function testFormat(): void
    {
        $person = [
            'forename' => 'forename',
            'familyName' => 'familyName'
        ];

        $personFormatted = 'forename familyName';

        $data = [
            'assignedTo' => [
                'contactDetails' => [
                    'person' => $person
                ]
            ]
        ];

        $personHelper = m::mock(PersonName::class);
        $personHelper->shouldReceive('__invoke')
            ->once()
            ->with(
                $person,
                [
                    'forename',
                    'familyName'
                ]
            )
            ->andReturn($personFormatted);

        $this->viewHelperManager->shouldReceive('get')->with('personName')->once()->andReturn($personHelper);
        $this->assertEquals($personFormatted, $this->sut->format($data, []));
    }
}
