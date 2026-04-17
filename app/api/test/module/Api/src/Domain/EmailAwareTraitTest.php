<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain;

use Doctrine\ORM\EntityNotFoundException;
use Dvsa\Olcs\Api\Domain\CommandHandler\EmailAwareTraitTestStub;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Mockery as m;

/**
 * Email aware trait test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class EmailAwareTraitTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * Test organisation recipients when user and contact details present
     */
    public function testOrganisationRecipients(): void
    {
        $userEmail = 'user@test.com';
        $orgEmails = ['orgEmail1@test.com'];
        $isInternal = false;

        $contactDetails = m::mock(ContactDetails::class);
        $contactDetails->shouldReceive('getEmailAddress')->once()->withNoArgs()->andReturn($userEmail);

        $user = m::mock(User::class);
        $user->shouldReceive('getContactDetails')->once()->withNoArgs()->andReturn($contactDetails);
        $user->shouldReceive('isInternal')->once()->withNoArgs()->andReturn($isInternal);

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getAdminEmailAddresses')->once()->withNoArgs()->andReturn($orgEmails);

        $expected = [
            'to' => $userEmail,
            'cc' => $orgEmails,
            'bcc' => [],
        ];

        $sut = new EmailAwareTraitTestStub();
        $this->assertEquals($expected, $sut->organisationRecipients($organisation, $user));
    }

    /**
     * Test organisation recipients when there is no user, or when the user has no contact details
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('emptyUserProvider')]
    public function testOrganisationRecipientsNoUserDetails(mixed $user): void
    {
        $orgEmail1 = 'orgEmail1@test.com';
        $orgEmail2 = 'orgEmail2@test.com';

        $orgEmails = [
            $orgEmail1,
            $orgEmail2
        ];

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getAdminEmailAddresses')->once()->withNoArgs()->andReturn($orgEmails);

        $expected = [
            'to' => $orgEmail1,
            'cc' => [1 => $orgEmail2],
            'bcc' => []
        ];

        $sut = new EmailAwareTraitTestStub();
        $this->assertEquals($expected, $sut->organisationRecipients($organisation, $user));
    }

    public static function emptyUserProvider(): array
    {
        $userWithoutEmail = m::mock(User::class);
        $userWithoutEmail->shouldReceive('isInternal')->withNoArgs()->andReturn(false);
        $userWithoutEmail->shouldReceive('getContactDetails')->withNoArgs()->andReturnNull();

        $contactDetails = m::mock(ContactDetails::class);
        $contactDetails->shouldReceive('getEmailAddress')->withNoArgs()->andReturn('internal@test.com');

        $userInternal = m::mock(User::class);
        $userInternal->shouldReceive('isInternal')->withNoArgs()->andReturn(true);
        $userInternal->shouldReceive('getContactDetails')->withNoArgs()->andReturn($contactDetails);

        return [
            [null],
            [$userWithoutEmail],
            [$userInternal],
        ];
    }

    public function testOrganisationRecipientsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\MissingEmailException::class);

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getAdminEmailAddresses')->once()->withNoArgs()->andReturn([]);

        $sut = new EmailAwareTraitTestStub();
        $sut->organisationRecipients($organisation, null);
    }

    public function testOrganisationRecipientsEntityNotFoundException(): void
    {
        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getAdminEmailAddresses')->once()->withNoArgs()->andReturn(['orgEmail1@test.com']);

        $user = m::mock(User::class);
        $user->shouldReceive('isInternal')->once()->withNoArgs()->andReturn(false);
        $user->shouldReceive('getContactDetails')->once()->withNoArgs()->andThrow(EntityNotFoundException::class);

        $expected = [
            'to' => 'orgEmail1@test.com',
            'cc' => [],
            'bcc' => []
        ];

        $sut = new EmailAwareTraitTestStub();
        $this->assertEquals($expected, $sut->organisationRecipients($organisation, $user));
    }
}
