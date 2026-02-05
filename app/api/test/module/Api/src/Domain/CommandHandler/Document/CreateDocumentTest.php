<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Transfer\Command\Document\CreateDocument as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\CreateDocument as CommandHandler;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * CreateDocumentTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateDocumentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Document', \Dvsa\Olcs\Api\Domain\Repository\Document::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'filename' => 'a',
            'identifier' => 'b',
            'size' => 'c',
            'application' => 'd',
            'busReg' => 'e',
            'case' => 'f',
            'irfoOrganisation' => 'g',
            'submission' => 'h',
            'trafficArea' => 'i',
            'transportManager' => 'j',
            'licence' => 'k',
            'operatingCentre' => 'l',
            'opposition' => 'm',
            'category' => 'n',
            'subCategory' => 'o',
            'description' => 'p',
            'isExternal' => true,
            'isScan' => false,
            'issuedDate' => 't',
        ];
        $command = Cmd::create($data);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Document\CreateDocumentSpecific::class,
            [
                'filename' => 'a',
                'identifier' => 'b',
                'size' => 'c',
                'application' => 'd',
                'busReg' => 'e',
                'case' => 'f',
                'irfoOrganisation' => 'g',
                'submission' => 'h',
                'trafficArea' => 'i',
                'transportManager' => 'j',
                'licence' => 'k',
                'operatingCentre' => 'l',
                'opposition' => 'm',
                'category' => 'n',
                'subCategory' => 'o',
                'description' => 'p',
                'isExternal' => true,
                'isScan' => false,
                'issuedDate' => 't',
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandDefaultExternal(): void
    {
        $data = [
            'filename' => 'a',
            'identifier' => 'b',
            'size' => 'c',
            'application' => 'd',
            'busReg' => 'e',
            'case' => 'f',
            'irfoOrganisation' => 'g',
            'submission' => 'h',
            'trafficArea' => 'i',
            'transportManager' => 'j',
            'licence' => 'k',
            'operatingCentre' => 'l',
            'opposition' => 'm',
            'category' => 'n',
            'subCategory' => 'o',
            'description' => 'p',
            'isScan' => false,
            'issuedDate' => 't',
        ];
        $command = Cmd::create($data);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(\Dvsa\Olcs\Api\Entity\User\Permission::SELFSERVE_USER, null)
            ->andReturn(true);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Document\CreateDocumentSpecific::class,
            [
                'filename' => 'a',
                'identifier' => 'b',
                'size' => 'c',
                'application' => 'd',
                'busReg' => 'e',
                'case' => 'f',
                'irfoOrganisation' => 'g',
                'submission' => 'h',
                'trafficArea' => 'i',
                'transportManager' => 'j',
                'licence' => 'k',
                'operatingCentre' => 'l',
                'opposition' => 'm',
                'category' => 'n',
                'subCategory' => 'o',
                'description' => 'p',
                'isExternal' => true,
                'isScan' => false,
                'issuedDate' => 't',
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->sut->handleCommand($command);
    }
}
