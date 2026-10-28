<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendBSRNotificationToLTAs;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use PHPUnit\Framework\TestCase;

final class SendBSRNotificationToLTAsTest extends TestCase
{
    /**
     * An older registration stays linked to the councils it was made with; once a council has
     * been replaced, its notifications go to it and to the authority that replaced it, each
     * address once
     */
    public function testRecipientsIncludeTheAuthoritiesThatReplacedTheRegistrationsAuthorities(): void
    {
        $teesValley = $this->authority('tees.valley@example.com');
        $darlington = $this->authority('darlington@example.com');
        $stockton = $this->authority('stockton@example.com');
        $durham = $this->authority('durham@example.com');
        $darlington->addSuccessors($teesValley);
        $stockton->addSuccessors($teesValley);

        $busReg = new BusReg();
        $busReg->setLocalAuthoritys(new ArrayCollection([$darlington, $stockton, $durham]));

        $this->assertSame(
            [
                'to' => 'darlington@example.com',
                'cc' => ['tees.valley@example.com', 'stockton@example.com', 'durham@example.com'],
                'bcc' => [],
            ],
            (new \ReflectionMethod(SendBSRNotificationToLTAs::class, 'getRecipients'))
                ->invoke(new SendBSRNotificationToLTAs(), $busReg)
        );
    }

    /**
     * The two sides of a split carry the old authority's address until they have their own
     */
    public function testAnAddressSharedByAuthoritiesIsUsedOnce(): void
    {
        $northamptonshire = $this->authority('northants@example.com');
        $northamptonshire->addSuccessors($this->authority('northants@example.com'));
        $northamptonshire->addSuccessors($this->authority('northants@example.com'));

        $busReg = new BusReg();
        $busReg->setLocalAuthoritys(new ArrayCollection([$northamptonshire]));

        $this->assertSame(
            ['to' => 'northants@example.com', 'cc' => [], 'bcc' => []],
            (new \ReflectionMethod(SendBSRNotificationToLTAs::class, 'getRecipients'))
                ->invoke(new SendBSRNotificationToLTAs(), $busReg)
        );
    }

    private function authority(string $emailAddress): LocalAuthority
    {
        $authority = new LocalAuthority();
        $authority->setEmailAddress($emailAddress);

        return $authority;
    }
}
