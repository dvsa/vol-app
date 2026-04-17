<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Surrender;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Surrender\Update as Sut;
use Dvsa\Olcs\Transfer\Command\Surrender\Update as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Surrender as SurrenderRepo;
use Dvsa\Olcs\Api\Entity\Surrender as SurrenderEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class UpdateTest extends AbstractCommandHandlerTestCase
{
    /** @var Sut */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sut();

        $this->mockRepo('Surrender', SurrenderRepo::class);

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];
        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $data): void
    {
        $command = Cmd::create($data);

        $surrenderEntity = m::mock(SurrenderEntity::class);

        if (array_key_exists('communityLicenceDocumentStatus', $data)) {
            $surrenderEntity->shouldReceive('setCommunityLicenceDocumentStatus')->once();
        }
        if (array_key_exists('digitalSignature', $data)) {
            $surrenderEntity->shouldReceive('setDigitalSignature')->once();
        }

        if (array_key_exists('status', $data)) {
            $surrenderEntity->shouldReceive('setStatus')->once();
            if ($data['status'] == SurrenderEntity::SURRENDER_STATUS_DISCS_COMPLETE) {
                $surrenderEntity->shouldReceive('setDiscDestroyed')->with(null)->once();
                $surrenderEntity->shouldReceive('setDiscLost')->with(null)->once();
                $surrenderEntity->shouldReceive('setDiscLostInfo')->with(null)->once();
                $surrenderEntity->shouldReceive('setDiscStolen')->with(null)->once();
                $surrenderEntity->shouldReceive('setDiscStolenInfo')->with(null)->once();
            } elseif ($data['status'] == SurrenderEntity::SURRENDER_STATUS_LIC_DOCS_COMPLETE) {
                if (array_key_exists('licenceDocumentStatus', $data)) {
                    $surrenderEntity->shouldReceive('setLicenceDocumentStatus')->once();
                    $surrenderEntity->shouldReceive('setLicenceDocumentInfo')->with(null)->once();
                    if (
                        $data['licenceDocumentStatus'] == SurrenderEntity::SURRENDER_DOC_STATUS_DESTROYED
                        && (array_key_exists('licenceDocumentInfo', $data) && !empty($data['licenceDocumentInfo']))
                    ) {
                        $surrenderEntity->shouldReceive('setLicenceDocumentInfo')->with(null)->once();
                    } else {
                        if (array_key_exists('licenceDocumentInfo', $data)) {
                            $surrenderEntity->shouldReceive('setLicenceDocumentInfo')->with($data['licenceDocumentInfo'])->once();
                        }
                    }
                }
            }
        }
        if (array_key_exists('discDestroyed', $data)) {
            $surrenderEntity->shouldReceive('setDiscDestroyed')->once();
        }
        if (array_key_exists('discLost', $data)) {
            $surrenderEntity->shouldReceive('setDiscLost')->once();
        }
        if (array_key_exists('discLostInfo', $data)) {
            $surrenderEntity->shouldReceive('setDiscLostInfo')->once();
        }
        if (array_key_exists('discStolen', $data)) {
            $surrenderEntity->shouldReceive('setDiscStolen')->once();
        }
        if (array_key_exists('discStolenInfo', $data)) {
            $surrenderEntity->shouldReceive('setDiscStolenInfo')->once();
        }

        if (array_key_exists('signatureType', $data)) {
            $surrenderEntity->shouldReceive('setSignatureType')->once();
        }

        if (array_key_exists('communityLicenceDocumentInfo', $data)) {
            $surrenderEntity->shouldReceive('setCommunityLicenceDocumentInfo')->once();
        }

        if (array_key_exists('ecmsChecked', $data)) {
            $surrenderEntity->shouldReceive('setEcmsChecked')->once();
        }

        if (array_key_exists('signatureChecked', $data)) {
            $surrenderEntity->shouldReceive('setSignatureChecked')->once();
        }

        $surrenderEntity->shouldReceive('getId')->once()->andReturn(1);

        $this->repoMap['Surrender']
            ->shouldReceive('fetchOneByLicenceId')
            ->andReturn($surrenderEntity)
            ->once();

        $this->repoMap['Surrender']
            ->shouldReceive('save')
            ->with(m::type(SurrenderEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(1, $result->getId('surrender'));
        $this->assertSame(['Surrender successfully updated.'], $result->getMessages());

        $this->assertInstanceOf(Result::class, $result);
    }

    public static function handleCommandProvider(): array
    {
        $data = [
            'case_01' => [
                [
                    'licence' => 11,
                    'communityLicenceDocumentStatus' => 'doc_sts_lost',
                    'digitalSignature' => '1',
                    'discDestroyed' => '1',
                    'discLost' => '0',
                    'discLostInfo' => 'text',
                    'discStolen' => '2',
                    'discStolenInfo' => 'text',
                    'licenceDocumentStatus' => 'doc_sts_destroyed',
                    'status' => 'surr_sts_comm_lic_docs_complete',
                    'signatureType' => 'sig_physical_signature',
                    'communityLicenceDocumentInfo' => 'some community licence doc info',
                    'ecmsChecked' => true,
                    'signatureChecked' => true,
                ]
            ],
            'case_02' => [
                [
                    'licence' => 11,
                    'status' => 'surr_sts_comm_lic_docs_complete',
                    'signatureType' => 'sig_digital_signature',
                    'licenceDocumentStatus' => 'doc_sts_stolen',
                    'licenceDocumentInfo' => 'some licence doc info',
                ]
            ],
            'case_03' => [
                [
                    'licence' => 11,
                    'licenceDocumentStatus' => 'doc_sts_destroyed',
                    'status' => 'surr_sts_lic_docs_complete'
                ]
            ],
            'case_04' => [
                [
                    'licence' => 11,
                    'licenceDocumentStatus' => 'doc_sts_lost',
                    'licenceDocumentInfo' => 'some licence doc info',
                    'status' => 'surr_sts_lic_docs_complete'
                ]
            ],
            'case_05' => [
                [
                    'licence' => 11,
                    'status' => 'surr_sts_discs_complete',
                    'discDestroyed' => '1'
                ]
            ]
        ];
        return $data;
    }
}
