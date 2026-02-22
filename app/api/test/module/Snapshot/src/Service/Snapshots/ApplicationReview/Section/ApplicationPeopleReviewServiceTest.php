<?php

declare(strict_types=1);

/**
 * Application People Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationPeopleReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\PeopleReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application People Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPeopleReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    /** @var PeopleReviewService */
    private $mockPeopleReview;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->mockPeopleReview = m::mock(PeopleReviewService::class);

        $this->sut = new ApplicationPeopleReviewService(
            $abstractReviewServiceServices,
            $this->mockPeopleReview
        );
    }

    public function testGetConfigFromData(): void
    {
        // Shows because we are Adding
        $person1 = [
            'action' => 'A',
            'originalPerson' => null,
            'person' => ['forename' => 'Added']
        ];
        // Shows because we have Updated
        $person2 = [
            'action' => 'U',
            'originalPerson' => [
                'id' => 123
            ],
            'person' => ['forename' => 'Updated']
        ];
        // Is ignored because it is deleted
        $person3 = [
            'action' => 'D',
            'originalPerson' => null,
            'person' => ['id' => 321]
        ];
        // Shows as it is an unchanged, existing record
        $person4 = [
            'person' => [
                'id' => 987,
                'forename' => 'Bob'
            ]
        ];
        // Is ignored as there is an updated version
        $person5 = [
            'person' => [
                'id' => 123,
                'forename' => 'Bob'
            ]
        ];
        // Is ignored as there is a delete delta
        $person6 = [
            'person' => [
                'id' => 321,
                'forename' => 'Bob'
            ]
        ];

        $data = [
            'applicationOrganisationPersons' => [
                $person1, $person2, $person3
            ],
            'licence' => [
                'organisation' => [
                    'organisationPersons' => [
                        $person4, $person5, $person6
                    ]
                ]
            ]
        ];
        $expected = [
            'subSections' => [
                [
                    'mainItems' => [
                        'PERSON1',
                        'PERSON2',
                        'PERSON4'
                    ]
                ]
            ]
        ];

        $this->mockPeopleReview->shouldReceive('shouldShowPosition')
            ->with($data)
            ->andReturn(true)
            ->shouldReceive('getConfigFromData')
            ->with($person1, true)
            ->andReturn('PERSON1')
            ->shouldReceive('getConfigFromData')
            ->with($person2, true)
            ->andReturn('PERSON2')
            ->shouldReceive('getConfigFromData')
            ->with($person4, true)
            ->andReturn('PERSON4');

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
