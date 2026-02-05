<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TmApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TmLicenceRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Mockery as m;

/**
 * Class TransportManagersTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class TransportManagersTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\TransportManagers::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public static function sectionTestProvider(): array
    {
        $case = static::getCase();

        $expectedResult = [
            'data' => [
                'tables' => [
                    'transport-managers' => [
                        43 => [
                            'id' => 43,
                            'version' => 53,
                            'licNo' => 'OB12345',
                            'tmType' => 'tmType-desc',
                            'title' => 'title-desc',
                            'forename' => 'fn22',
                            'familyName' => 'sn22',
                            'qualifications' => [
                                0 => 'tm-qual-desc'
                            ],
                            'otherLicences' => [
                                0 => [
                                    'licNo' => '1-licNo',
                                    'applicationId' => 2255
                                ],
                                1 => [
                                    'licNo' => '1-licNo',
                                    'applicationId' => false
                                ]
                            ],
                            'birthDate' => '22/01/1977',
                            'birthPlace' => 'bp'
                        ],
                        216 => [
                            'id' => 216,
                            'version' => 226,
                            'licNo' => 'OB12345',
                            'tmType' => 'tmType-desc',
                            'title' => 'title-desc',
                            'forename' => 'fn22',
                            'familyName' => 'sn22',
                            'qualifications' => [
                                0 => 'tm-qual-desc'
                            ],
                            'otherLicences' => [],
                            'birthDate' => '22/01/1977',
                            'birthPlace' => 'bp'
                        ]
                    ]
                ]
            ]
        ];

        return [
            [$case, $expectedResult],
        ];
    }

    #[\Override]
    protected function mockSetRepos(mixed $sut): void
    {
        $mockTmLicenceRepo = m::mock(TmLicenceRepo::class);
        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getLicNo')
            ->andReturn('1-licNo')
            ->getMock();

        $mockTmLicences = [
            m::mock(TransportManagerLicence::class)
                ->shouldReceive('getLicence')
                ->andReturn($mockLicence)
                ->getMock()
        ];
        $mockTmLicenceRepo->shouldReceive('fetchForTransportManager')
            ->with(
                43,
                [
                    Licence::LICENCE_STATUS_VALID,
                    Licence::LICENCE_STATUS_SUSPENDED,
                    Licence::LICENCE_STATUS_CURTAILED
                ]
            )
            ->andReturn($mockTmLicences);

        $mockTmLicenceRepo->shouldReceive('fetchForTransportManager')
            ->with(
                216,
                [
                    Licence::LICENCE_STATUS_VALID,
                    Licence::LICENCE_STATUS_SUSPENDED,
                    Licence::LICENCE_STATUS_CURTAILED
                ]
            )
            ->andReturn([]);

        $mockTmApplicationRepo = m::mock(TmApplicationRepo::class);

        $mockApplication = m::mock(Application::class)
            ->shouldReceive('getLicence')
            ->once()
            ->andReturn($mockLicence)
            ->shouldReceive('getId')
            ->once()
            ->andReturn(2255)
            ->getMock();

        $mockTmApplications = [
            m::mock(TransportManagerApplication::class)
                ->shouldReceive('getApplication')
                ->andReturn($mockApplication)
                ->getMock()
        ];

        $mockTmApplicationRepo->shouldReceive('fetchForTransportManager')
            ->with(
                43,
                [
                    Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
                    Application::APPLICATION_STATUS_NOT_SUBMITTED,
                    Application::APPLICATION_STATUS_GRANTED
                ],
                true
            )
            ->andReturn($mockTmApplications);

        $mockTmApplicationRepo->shouldReceive('fetchForTransportManager')
            ->with(
                216,
                [
                    Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
                    Application::APPLICATION_STATUS_NOT_SUBMITTED,
                    Application::APPLICATION_STATUS_GRANTED
                ],
                true
            )
            ->andReturn([]);

        $mockRepos = [
            TmLicenceRepo::class => $mockTmLicenceRepo,
            TmApplicationRepo::class => $mockTmApplicationRepo
        ];

        $sut->setRepos($mockRepos);
    }
}
