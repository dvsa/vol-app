<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\GdsVerify;

use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateApplication;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateContinuationDetail;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateSurrender;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateTmApplication;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\GdsVerify\ProcessSignatureResponse;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\GdsVerify;
use Dvsa\Olcs\GdsVerify\Data\Attributes;
use Dvsa\Olcs\GdsVerify\Service;
use Dvsa\Olcs\Transfer\Command\GdsVerify\ProcessSignatureResponse as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class ProcessSignatureResponseTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var ProcessSignatureResponse
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ProcessSignatureResponse();
        $this->mockRepo('DigitalSignature', \Dvsa\Olcs\Api\Domain\Repository\DigitalSignature::class);

        $this->mockedSmServices = [
            Service\GdsVerify::class => m::mock(Service\GdsVerify::class)
        ];

        parent::setUp();
    }

    public function testHandleCommandInvalidSignature(): void
    {
        $command = Cmd::create(['samlResponse' => 'SAML']);

        $attributes = m::mock(Attributes::class);
        $attributes->expects('isValidSignature')->withNoArgs()->andReturnFalse();
        $this->mockedSmServices[Service\GdsVerify::class]->expects('getAttributesFromResponse')
            ->with('SAML')->andReturn($attributes);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandInvalidAssertion(): void
    {
        $this->mockedSmServices[Service\GdsVerify::class]->expects('getAttributesFromResponse')
            ->with('SAML')->andThrow(GdsVerify\Exception::class, 'EXCEPTION_MESSAGE');

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $command = Cmd::create(['samlResponse' => 'SAML']);
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandApplication(): void
    {
        $application = 7;
        $digitalSignatureId = 999;
        $command = Cmd::create(['samlResponse' => base64_encode('SAML'), 'application' => $application]);

        $this->getValidAttributes();
        $this->saveDigitalSignature($digitalSignatureId);

        $this->expectedSideEffect(
            UpdateApplication::class,
            ['application' => $application, 'digitalSignature' => $digitalSignatureId],
            $this->sideEffectResult('side effect result')
        );

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($this->expectedResultMessages(), $result->getMessages());
    }

    public function testHandleCommandContinuationDetail(): void
    {
        $continuationDetail = 65;
        $digitalSignatureId = 999;
        $command = Cmd::create(
            ['samlResponse' => base64_encode('SAML'), 'continuationDetail' => $continuationDetail]
        );

        $this->getValidAttributes();
        $this->saveDigitalSignature($digitalSignatureId);

        $this->expectedSideEffect(
            UpdateContinuationDetail::class,
            ['continuationDetail' => $continuationDetail, 'digitalSignature' => $digitalSignatureId],
            $this->sideEffectResult('side effect result')
        );

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($this->expectedResultMessages(), $result->getMessages());
    }

    public function testHandleCommandTransportManagerApplication(): void
    {
        $tmApplicationId = 65;
        $digitalSignatureId = 999;
        $role = RefData::TMA_SIGN_AS_TM;

        $command = Cmd::create([
            'samlResponse' => base64_encode('SAML'),
            'transportManagerApplication' => $tmApplicationId,
            'role' => $role
        ]);

        $this->getValidAttributes();
        $this->saveDigitalSignature($digitalSignatureId);

        $this->expectedSideEffect(
            UpdateTmApplication::class,
            ['application' => $tmApplicationId, 'digitalSignature' => $digitalSignatureId, 'role' => $role],
            $this->sideEffectResult('side effect result')
        );

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($this->expectedResultMessages(), $result->getMessages());
    }

    public function testProcessSignatureSurrender(): void
    {
        $licence = 65;
        $digitalSignatureId = 999;

        $data = [
            'samlResponse' => base64_encode('SAML'),
            'licence' => $licence,
        ];
        $command = Cmd::create($data);

        $this->getValidAttributes();
        $this->saveDigitalSignature($digitalSignatureId);

        $this->expectedSideEffect(
            UpdateSurrender::class,
            ['licence' => $licence, 'digitalSignature' => $digitalSignatureId],
            $this->sideEffectResult('side effect result')
        );

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($this->expectedResultMessages(), $result->getMessages());
    }

    private function expectedResultMessages(): array
    {
        return [
            0 => 'Digital signature created',
            1 => 'side effect result',
        ];
    }

    private function getValidAttributes(): void
    {
        $attributes = m::mock(Attributes::class);
        $attributes->expects('isValidSignature')->withNoArgs()->andReturnTrue();
        $attributes->expects('getArrayCopy')->withNoArgs()->andReturn(['foo' => 'bar']);
        $this->mockedSmServices[Service\GdsVerify::class]
            ->expects('getAttributesFromResponse')->with(base64_encode('SAML'))->andReturn($attributes);
    }

    private function saveDigitalSignature(int $digitalSignatureId): void
    {
        $this->repoMap['DigitalSignature']->expects('save')->andReturnUsing(
            function ($digitalSignature) use ($digitalSignatureId) {
                /** @var \Dvsa\Olcs\Api\Entity\DigitalSignature $digitalSignature */
                $this->assertSame(['foo' => 'bar'], $digitalSignature->getAttributesArray());
                $this->assertSame('SAML', $digitalSignature->getSamlResponse());
                $digitalSignature->setId($digitalSignatureId);
            }
        );
    }

    public function testSetGetGdsVerifyService()
    {
        $sut = new ProcessSignatureResponse();
        $this->assertNull($sut->getGdsVerifyService());
        $mock = m::mock(Service\GdsVerify::class);
        $sut->setGdsVerifyService($mock);
        $this->assertSame($mock, $sut->getGdsVerifyService());
    }
}
