<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Sms;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Sms\SmsNumberCapture;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SmsNumberCaptureTest extends TestCase
{
    private function capture(bool $toggle): SmsNumberCapture
    {
        $toggles = $this->createStub(ToggleService::class);
        $toggles->method('isEnabled')->willReturnCallback(
            fn (string $name): bool => $name === FeatureToggle::SMS_NUMBER_CAPTURE && $toggle
        );

        return new SmsNumberCapture($toggles);
    }

    private function licence(bool $withSms): Licence
    {
        $licence = (new \ReflectionClass(Licence::class))->newInstanceWithoutConstructor();
        $phones = new ArrayCollection();
        if ($withSms) {
            $pc = new PhoneContact(new RefData(PhoneContact::TYPE_SMS));
            $pc->setPhoneNumber('+447700900123');
            $phones->add($pc);
        }
        $cd = new ContactDetails(new RefData());
        $cd->setPhoneContacts($phones);
        $licence->setCorrespondenceCd($cd);

        return $licence;
    }

    public function testIsRequiredFollowsToggle(): void
    {
        $this->assertTrue($this->capture(true)->isRequired());
        $this->assertFalse($this->capture(false)->isRequired());
    }

    #[DataProvider('missingCases')]
    public function testIsMissing(bool $toggle, bool $external, bool $withSms, bool $expected): void
    {
        $this->assertSame($expected, $this->capture($toggle)->isMissing($this->licence($withSms), $external));
    }

    public static function missingCases(): array
    {
        return [
            'toggle on, external, no number' => [true, true, false, true],
            'toggle on, external, has number' => [true, true, true, false],
            'toggle on, internal, no number' => [true, false, false, false],
            'toggle off, external, no number' => [false, true, false, false],
        ];
    }

    #[DataProvider('applicationCases')]
    public function testBlocksApplication(bool $toggle, bool $variation, bool $expected): void
    {
        $application = $this->createStub(Application::class);
        $application->method('isVariation')->willReturn($variation);
        $application->method('getLicence')->willReturn($this->licence(false));

        $this->assertSame($expected, $this->capture($toggle)->blocksApplication($application, fn (): bool => true));
    }

    public function testVariationNeverAsksWhoTheUserIs(): void
    {
        $application = $this->createStub(Application::class);
        $application->method('isVariation')->willReturn(true);

        $asked = false;
        $isExternal = function () use (&$asked): bool {
            $asked = true;
            return true;
        };

        $this->assertFalse($this->capture(true)->blocksApplication($application, $isExternal));
        $this->assertFalse($asked);
    }

    public static function applicationCases(): array
    {
        return [
            'new application, toggle on' => [true, false, true],
            'new application, toggle off' => [false, false, false],
            'variation, toggle on' => [true, true, false],
        ];
    }

    #[DataProvider('staleCompletionCases')]
    public function testMarkStaleAddressesIncomplete(
        bool $toggle,
        bool $external,
        bool $variation,
        string $status,
        int $addressesStatus,
        bool $withSms,
        bool $expectIncomplete
    ): void {
        $completion = $this->createStub(ApplicationCompletion::class);
        $completion->method('getAddressesStatus')->willReturn($addressesStatus);

        $application = $this->createStub(Application::class);
        $application->method('isVariation')->willReturn($variation);
        $application->method('getStatus')->willReturn(new RefData($status));
        $application->method('getApplicationCompletion')->willReturn($completion);
        $application->method('getLicence')->willReturn($this->licence($withSms));
        $application->method('serialize')->willReturn(
            ['applicationCompletion' => ['addressesStatus' => $addressesStatus, 'peopleStatus' => 2]]
        );

        $result = new Result($application);
        $returned = $this->capture($toggle)->markStaleAddressesIncomplete($result, $application, fn (): bool => $external);

        $this->assertSame($result, $returned);
        $data = $result->serialize();
        $this->assertSame(
            $expectIncomplete ? ApplicationCompletion::STATUS_INCOMPLETE : $addressesStatus,
            $data['applicationCompletion']['addressesStatus']
        );
        $this->assertSame(2, $data['applicationCompletion']['peopleStatus']);
    }

    public static function staleCompletionCases(): \Iterator
    {
        $open = Application::APPLICATION_STATUS_NOT_SUBMITTED;
        $done = ApplicationCompletion::STATUS_COMPLETE;

        yield 'selfserve, capture on, no number' => [true, true, false, $open, $done, false, true];
        yield 'has a number' => [true, true, false, $open, $done, true, false];
        yield 'capture off' => [false, true, false, $open, $done, false, false];
        yield 'internal user' => [true, false, false, $open, $done, false, false];
        yield 'variation' => [true, true, true, $open, $done, false, false];
        yield 'submitted' => [true, true, false, Application::APPLICATION_STATUS_UNDER_CONSIDERATION, $done, false, false];
        yield 'addresses not started' => [true, true, false, $open, ApplicationCompletion::STATUS_NOT_STARTED, false, false];
    }

    public function testCaptureOffNeverTouchesTheApplication(): void
    {
        $application = $this->createMock(Application::class);
        $application->expects($this->never())->method($this->anything());

        $this->capture(false)->markStaleAddressesIncomplete(new Result(), $application, fn (): bool => true);
    }
}
