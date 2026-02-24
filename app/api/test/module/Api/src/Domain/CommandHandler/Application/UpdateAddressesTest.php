<?php

declare(strict_types=1);

/**
 * Update Addresses test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\SetDefaultTrafficAreaAndEnforcementArea;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;
use Dvsa\Olcs\Api\Domain\Command\Licence\SaveAddresses;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateAddresses;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Service\TrafficAreaValidator;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\UpdateAddresses as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Addresses test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UpdateAddressesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateAddresses();
        $this->mockRepo('Application', ApplicationRepo::class);

        $this->mockedSmServices['TrafficAreaValidator'] = m::mock(TrafficAreaValidator::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 123,
            'correspondence' => 'corr',
            'contact' => 'contact',
            'correspondenceAddress' => [
                'addressLine1' => 'c_addressLine1',
                'addressLine2' => 'c_addressLine2',
                'addressLine3' => 'c_addressLine3',
                'addressLine4' => 'c_addressLine4',
                'town' => 'c_town',
                'postcode' => 'c_postcode',
                'countryCode' => 'c_countryCode',
            ],
            'establishment' => 'est',
            'establishmentAddress' => [
                'addressLine1' => 'e_addressLine1',
                'addressLine2' => 'e_addressLine2',
                'addressLine3' => 'e_addressLine3',
                'addressLine4' => 'e_addressLine4',
                'town' => 'e_town',
                'postcode' => 'e_postcode',
                'countryCode' => 'e_countryCode',
            ],
            'consultant' => 'consultant'
        ];
        $command = Cmd::create($data);

        // licence ID overrides application ID
        $addressData = array_merge($data, ['id' => 456]);

        $this->expectedSideEffect(
            SaveAddresses::class,
            $addressData,
            (new Result())->addMessage('SaveAddresses executed')
        );

        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            [
                'id' => 123,
                'section' => 'addresses'
            ],
            (new Result())->addMessage('UpdateApplicationCompletion executed')
        );

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getId')
            ->andReturn(456);

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('getId')
            ->andReturn(123)
            ->shouldReceive('getLicence')
            ->andReturn($licence)
            ->shouldReceive('isNew')
            ->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'SaveAddresses executed',
                'UpdateApplicationCompletion executed',
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommandForLgv')]
    public function testHandleCommandForLgv(mixed $establishmentAddress, mixed $expectedPostcode): void
    {
        $data = [
            'id' => 123,
            'correspondence' => 'corr',
            'contact' => 'contact',
            'correspondenceAddress' => [
                'addressLine1' => 'c_addressLine1',
                'addressLine2' => 'c_addressLine2',
                'addressLine3' => 'c_addressLine3',
                'addressLine4' => 'c_addressLine4',
                'town' => 'c_town',
                'postcode' => 'c_postcode',
                'countryCode' => 'c_countryCode',
            ],
            'establishment' => 'est',
            'establishmentAddress' => $establishmentAddress,
            'consultant' => 'consultant'
        ];
        $command = Cmd::create($data);

        // licence ID overrides application ID
        $addressData = array_merge($data, ['id' => 456]);

        $this->expectedSideEffect(
            SaveAddresses::class,
            $addressData,
            (new Result())->addMessage('SaveAddresses executed')
        );

        $application = m::mock(ApplicationEntity::class)->makePartial();

        $this->mockedSmServices['TrafficAreaValidator']
            ->shouldReceive('validateTrafficAreaWithPostcode')
            ->with($application, $expectedPostcode)
            ->once();

        $this->expectedSideEffect(
            SetDefaultTrafficAreaAndEnforcementArea::class,
            [
                'id' => 123,
                'postcode' => $expectedPostcode,
            ],
            (new Result())->addMessage('SetDefaultTrafficAreaAndEnforcementArea executed')
        );

        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            [
                'id' => 123,
                'section' => 'addresses'
            ],
            (new Result())->addMessage('UpdateApplicationCompletion executed')
        );

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getId')
            ->andReturn(456)
            ->shouldReceive('setTrafficArea')
            ->with(null)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setEnforcementArea')
            ->with(null)
            ->once()
            ->andReturnSelf();

        $application->shouldReceive('getId')
            ->andReturn(123)
            ->shouldReceive('getLicence')
            ->andReturn($licence)
            ->shouldReceive('isNew')
            ->andReturn(true)
            ->shouldReceive('getVehicleType')
            ->andReturn(new RefData(RefData::APP_VEHICLE_TYPE_LGV));

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'SaveAddresses executed',
                'SetDefaultTrafficAreaAndEnforcementArea executed',
                'UpdateApplicationCompletion executed',
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpTestHandleCommandForLgv(): array
    {
        return [
            [
                [
                    'addressLine1' => 'e_addressLine1',
                    'addressLine2' => 'e_addressLine2',
                    'addressLine3' => 'e_addressLine3',
                    'addressLine4' => 'e_addressLine4',
                    'town' => 'e_town',
                    'postcode' => 'e_postcode',
                    'countryCode' => 'e_countryCode',
                ],
                'e_postcode',
            ],
            [
                [
                    'addressLine1' => 'e_addressLine1',
                    'addressLine2' => '',
                    'addressLine3' => '',
                    'addressLine4' => '',
                    'town' => '',
                    'postcode' => '',
                    'countryCode' => 'e_countryCode',
                ],
                '',
            ],
            [
                [
                    'addressLine1' => '',
                    'addressLine2' => 'e_addressLine2',
                    'addressLine3' => '',
                    'addressLine4' => '',
                    'town' => '',
                    'postcode' => '',
                    'countryCode' => 'e_countryCode',
                ],
                '',
            ],
            [
                [
                    'addressLine1' => '',
                    'addressLine2' => '',
                    'addressLine3' => 'e_addressLine3',
                    'addressLine4' => '',
                    'town' => '',
                    'postcode' => '',
                    'countryCode' => 'e_countryCode',
                ],
                '',
            ],
            [
                [
                    'addressLine1' => '',
                    'addressLine2' => '',
                    'addressLine3' => '',
                    'addressLine4' => 'e_addressLine4',
                    'town' => '',
                    'postcode' => '',
                    'countryCode' => 'e_countryCode',
                ],
                '',
            ],
            [
                [
                    'addressLine1' => '',
                    'addressLine2' => '',
                    'addressLine3' => '',
                    'addressLine4' => '',
                    'town' => 'e_town',
                    'postcode' => '',
                    'countryCode' => 'e_countryCode',
                ],
                '',
            ],
            [
                [
                    'addressLine1' => '',
                    'addressLine2' => '',
                    'addressLine3' => '',
                    'addressLine4' => '',
                    'town' => '',
                    'postcode' => 'e_postcode',
                    'countryCode' => 'e_countryCode',
                ],
                'e_postcode',
            ],
            [
                [
                    'addressLine1' => '',
                    'addressLine2' => '',
                    'addressLine3' => '',
                    'addressLine4' => '',
                    'town' => '',
                    'postcode' => '',
                    'countryCode' => 'e_countryCode',
                ],
                'c_postcode',
            ],
        ];
    }

    public function testHandleCommandForLgvAndInvalidTrafficArea(): void
    {
        $expectedException = new ValidationException(['TA invalid']);

        $data = [
            'id' => 123,
            'correspondence' => 'corr',
            'contact' => 'contact',
            'correspondenceAddress' => [
                'addressLine1' => 'c_addressLine1',
                'addressLine2' => 'c_addressLine2',
                'addressLine3' => 'c_addressLine3',
                'addressLine4' => 'c_addressLine4',
                'town' => 'c_town',
                'postcode' => 'c_postcode',
                'countryCode' => 'c_countryCode',
            ],
            'establishment' => 'est',
            'establishmentAddress' => [
                'addressLine1' => '',
                'addressLine2' => '',
                'addressLine3' => '',
                'addressLine4' => '',
                'town' => '',
                'postcode' => '',
                'countryCode' => 'e_countryCode',
            ],
            'consultant' => 'consultant'
        ];
        $command = Cmd::create($data);

        // licence ID overrides application ID
        $addressData = array_merge($data, ['id' => 456]);

        $this->expectedSideEffect(
            SaveAddresses::class,
            $addressData,
            (new Result())->addMessage('SaveAddresses executed')
        );

        $application = m::mock(ApplicationEntity::class)->makePartial();

        $this->mockedSmServices['TrafficAreaValidator']
            ->shouldReceive('validateTrafficAreaWithPostcode')
            ->with($application, 'c_postcode')
            ->once()
            ->andThrow($expectedException);

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getId')
            ->andReturn(456)
            ->shouldReceive('setTrafficArea')
            ->never()
            ->shouldReceive('setEnforcementArea')
            ->never();

        $application->shouldReceive('getId')
            ->andReturn(123)
            ->shouldReceive('getLicence')
            ->andReturn($licence)
            ->shouldReceive('isNew')
            ->andReturn(true)
            ->shouldReceive('getVehicleType')
            ->andReturn(new RefData(RefData::APP_VEHICLE_TYPE_LGV));

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->never();

        $this->expectExceptionObject($expectedException);

        $this->sut->handleCommand($command);
    }
}
