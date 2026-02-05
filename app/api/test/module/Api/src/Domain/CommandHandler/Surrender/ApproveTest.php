<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Surrender;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Surrender\Approve as ApproveHandler;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Surrender as SurrenderRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Licence\SurrenderLicence;
use Dvsa\Olcs\Transfer\Command\Surrender\Approve as ApproveCommand;
use Dvsa\Olcs\Transfer\Command\Surrender\Update as UpdateCommand;
use Mockery as m;

class ApproveTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var ApproveHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ApproveHandler();
        $this->refData = [];
        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Surrender', SurrenderRepo::class);
        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            RefData::SURRENDER_STATUS_APPROVED,
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommand')]
    public function testHandleCommand(mixed $data, mixed $expected): void
    {
        $now = new \DateTime();
        $cmdData = [
            'id' => 45,
            'surrenderDate' => $now->format('Y-m-d')
        ];

        $command = ApproveCommand::create($cmdData);

        $this->expectedSideEffect(
            UpdateCommand::class,
            [
                'id' => 45,
                'status' => RefData::SURRENDER_STATUS_APPROVED,
            ],
            new Result()
        );

        $this->expectedSideEffect(
            SurrenderLicence::class,
            [
                'id' => 45,
                'surrenderDate' => $cmdData['surrenderDate'],
                'terminated' => false
            ],
            new Result()
        );

        $licenceEntity = m::mock(Licence::class);

        $this->repoMap['Licence']->shouldReceive('fetchById')->andReturn($licenceEntity);

        $licenceEntity->shouldReceive('getCreatedBy->getId')->andReturn(5);
        $licenceEntity->shouldReceive('getGoodsOrPsv->getId')->andReturn($data['goodsOrPsv']);
        $licenceEntity->shouldReceive('getLicenceType->getId')->andReturn($data['licType']);
        $licenceEntity->shouldReceive('isNi')->andReturn($data['isNi']);

        $surrenderEntity = m::mock(Surrender::class);

        $this->repoMap['Licence']->shouldReceive('fetchById')->andReturn($licenceEntity);
        $this->repoMap['Surrender']->shouldReceive('fetchOneByLicenceId')->andReturn($surrenderEntity);

        $surrenderEntity->shouldReceive('getEcmsChecked')->andReturn(true);
        $surrenderEntity->shouldReceive('getSignatureChecked')->andReturn(true);

        $this->expectedSideEffect(
            GenerateAndStore::class,
            [
                'template' => $expected['template'],
                'query' => [
                    'licence' => 45,
                ],
                'description' => $expected['description'],
                'licence' => 45,
                'category' => Category::CATEGORY_LICENSING,
                'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
                'isExternal' => true,
                'isScan' => false,
                'dispatch' => true
            ],
            new Result()
        );

        $this->sut->handleCommand($command);
    }

    public static function dpTestHandleCommand(): array
    {
        return [
            'psv_sn_notNi' => [
                'data' => [
                    'goodsOrPsv' => Licence::LICENCE_CATEGORY_PSV,
                    'licType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'isNi' => false,
                ],
                'expected' => [
                    'template' => 'GB/SURRENDER_LETTER_TO_OPERATOR_PSV',
                    'description' => 'PSV - Surrender actioned letter',
                ]
            ],
            'psv_si_notNi' => [
                'data' => [
                    'goodsOrPsv' => Licence::LICENCE_CATEGORY_PSV,
                    'licType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'isNi' => false,
                ],
                'expected' => [
                    'template' => 'GB/SURRENDER_LETTER_TO_OPERATOR_PSV',
                    'description' => 'PSV - Surrender actioned letter',
                ]
            ],
            'psv_restricted_notNi' => [
                'data' => [
                    'goodsOrPsv' => Licence::LICENCE_CATEGORY_PSV,
                    'licType' => Licence::LICENCE_TYPE_RESTRICTED,
                    'isNi' => false,
                ],
                'expected' => [
                    'template' => 'GB/SURRENDER_LETTER_TO_OPERATOR_PSV',
                    'description' => 'PSV - Surrender actioned letter',
                ]
            ],
            'gv_sn_notNi' => [
                'data' => [
                    'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'isNi' => false,
                ],
                'expected' => [
                    'template' => 'GB/SURRENDER_LETTER_TO_OPERATOR_GV_GB',
                    'description' => 'GV - Surrender actioned letter',
                ]
            ],
            'gv_si_notNi' => [
                'data' => [
                    'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'isNi' => false,
                ],
                'expected' => [
                    'template' => 'GB/SURRENDER_LETTER_TO_OPERATOR_GV_GB',
                    'description' => 'GV - Surrender actioned letter',
                ]
            ],
            'gv_restricted_notNi' => [
                'data' => [
                    'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licType' => Licence::LICENCE_TYPE_RESTRICTED,
                    'isNi' => false,
                ],
                'expected' => [
                    'template' => 'GB/SURRENDER_LETTER_TO_OPERATOR_GV_GB',
                    'description' => 'GV - Surrender actioned letter',
                ]
            ],
            'gv_sn_isNi' => [
                'data' => [
                    'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'isNi' => true,
                ],
                'expected' => [
                    'template' => 'NI/SURRENDER_LETTER_TO_OPERATOR_GV_NI',
                    'description' => 'GV - Surrender actioned letter (NI)',
                ]
            ],
            'gv_si_isNi' => [
                'data' => [
                    'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'isNi' => true,
                ],
                'expected' => [
                    'template' => 'NI/SURRENDER_LETTER_TO_OPERATOR_GV_NI',
                    'description' => 'GV - Surrender actioned letter (NI)',
                ]
            ],
            'gv_restricted_isNi' => [
                'data' => [
                    'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licType' => Licence::LICENCE_TYPE_RESTRICTED,
                    'isNi' => true,
                ],
                'expected' => [
                    'template' => 'NI/SURRENDER_LETTER_TO_OPERATOR_GV_NI',
                    'description' => 'GV - Surrender actioned letter (NI)',
                ]
            ],
        ];
    }

    public function testGenerateDocumentAndSendNotificationEmailWithInvalidLicenceType(): void
    {
        $now = new \DateTime();
        $cmdData = [
            'id' => 45,
            'surrenderDate' => $now->format('Y-m-d')
        ];

        $command = ApproveCommand::create($cmdData);

        $this->expectedSideEffect(
            UpdateCommand::class,
            [
                'id' => 45,
                'status' => RefData::SURRENDER_STATUS_APPROVED,
            ],
            new Result()
        );

        $this->expectedSideEffect(
            SurrenderLicence::class,
            [
                'id' => 45,
                'surrenderDate' => $cmdData['surrenderDate'],
                'terminated' => false
            ],
            new Result()
        );

        $licenceEntity = m::mock(Licence::class);

        $this->repoMap['Licence']->shouldReceive('fetchById')->andReturn($licenceEntity);

        $surrenderEntity = m::mock(Surrender::class);

        $this->repoMap['Surrender']->shouldReceive('fetchOneByLicenceId')->andReturn($surrenderEntity);

        $surrenderEntity->shouldReceive('getEcmsChecked')->andReturn(true);
        $surrenderEntity->shouldReceive('getSignatureChecked')->andReturn(true);

        $licenceEntity->shouldReceive('getCreatedBy->getId')->andReturn(5);
        $licenceEntity->shouldReceive('getGoodsOrPsv->getId')->andReturn(Licence::LICENCE_CATEGORY_PSV);
        $licenceEntity->shouldReceive('getLicenceType->getId')->andReturn(Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $licenceEntity->shouldReceive('isNi')->andReturn(true);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Licence type not surrenderable');

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTesthasEcmsAndSignatureBeenChecked')]
    public function testhasEcmsAndSignatureBeenChecked(mixed $data, mixed $expected): void
    {
        $now = new \DateTime();
        $cmdData = [
            'id' => 45,
            'surrenderDate' => $now->format('Y-m-d')
        ];

        $command = ApproveCommand::create($cmdData);

        $this->expectedSideEffect(
            UpdateCommand::class,
            [
                'id' => 45,
                'status' => RefData::SURRENDER_STATUS_APPROVED,
            ],
            new Result(),
            $expected['numberOfSideEffectCalls']
        );

        $this->expectedSideEffect(
            SurrenderLicence::class,
            [
                'id' => 45,
                'surrenderDate' => $cmdData['surrenderDate'],
                'terminated' => false
            ],
            new Result(),
            $expected['numberOfSideEffectCalls']
        );

        $this->expectedSideEffect(
            GenerateAndStore::class,
            [
                'template' => 'NI/SURRENDER_LETTER_TO_OPERATOR_GV_NI',
                'query' => [
                    'licence' => 45,
                ],
                'description' => 'GV - Surrender actioned letter (NI)',
                'licence' => 45,
                'category' => Category::CATEGORY_LICENSING,
                'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
                'isExternal' => true,
                'isScan' => false,
                'dispatch' => true
            ],
            new Result(),
            $expected['numberOfSideEffectCalls']
        );

        $licenceEntity = m::mock(Licence::class);

        $this->repoMap['Licence']->shouldReceive('fetchById')->andReturn($licenceEntity);

        $surrenderEntity = m::mock(Surrender::class);

        $this->repoMap['Surrender']->shouldReceive('fetchOneByLicenceId')->andReturn($surrenderEntity);

        $surrenderEntity->shouldReceive('getEcmsChecked')->andReturn($data['ecmsChecked']);
        $surrenderEntity->shouldReceive('getSignatureChecked')->andReturn($data['signatureChecked']);

        $licenceEntity->shouldReceive('getCreatedBy->getId')->andReturn(5);
        $licenceEntity->shouldReceive('getGoodsOrPsv->getId')->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $licenceEntity->shouldReceive('getLicenceType->getId')->andReturn(Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $licenceEntity->shouldReceive('isNi')->andReturn(true);

        if ($expected['throwException'] === true) {
            $this->expectException(ForbiddenException::class);
            $this->expectExceptionMessage('The surrender has not been checked');
        }

        $this->sut->handleCommand($command);
    }

    public static function dpTesthasEcmsAndSignatureBeenChecked(): array
    {
        return [
            'ecms_checked' => [
                'data' => [
                    'ecmsChecked' => true,
                    'signatureChecked' => false,
                ],
                'expected' => [
                    'throwException' => true,
                    'numberOfSideEffectCalls' => 0
                ]
            ],
            'signature_checked' => [
                'data' => [
                    'ecmsChecked' => false,
                    'signatureChecked' => true
                ],
                'expected' => [
                    'throwException' => true,
                    'numberOfSideEffectCalls' => 0
                ]
            ],
            'both_checked' => [
                'data' => [
                    'ecmsChecked' => true,
                    'signatureChecked' => true
                ],
                'expected' => [
                    'throwException' => false,
                    'numberOfSideEffectCalls' => 1
                ]
            ],
            'none_checked' => [
                'data' => [
                    'ecmsChecked' => false,
                    'signatureChecked' => false
                ],
                'expected' => [
                    'throwException' => true,
                    'numberOfSideEffectCalls' => 0
                ]
            ]
        ];
    }
}
