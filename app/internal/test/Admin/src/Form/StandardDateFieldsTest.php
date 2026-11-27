<?php

declare(strict_types=1);

namespace AdminTest\Form;

use Admin\Form\Model\Fieldset\FeeRateDetails;
use Admin\Form\Model\Fieldset\DelayItem;
use Admin\Form\Model\Fieldset\PermitWindowDetails;
use Admin\Form\Model\Fieldset\ScanningDetails;
use Common\Filter\DateSelectNullifier;
use Common\Filter\DateTimeSelectNullifier;
use Common\Form\Elements\Custom\DateSelect;
use Common\Form\Elements\Custom\DateTimeSelect;
use Common\InputFilter\DateParts;
use Common\InputFilter\DateTimeParts;
use Common\InputFilter\ConditionallyValidatedDateParts;
use Common\InputFilter\ConditionallyValidatedDateTimeParts;
use Common\Form\Model\Form\Lva\Fieldset\UploadEvidenceOperatingCentre;
use Common\Form\Model\Form\Lva\Fieldset\CommunityLicencesStopDates;
use Common\Form\Model\Form\Lva\Fieldset\AdvertisementsAdPlacedNow;
use Common\Form\Model\Form\Lva\Fieldset\LicenceLicenceVehicle;
use Common\Form\Model\Form\Lva\Fieldset\LicenceVehicle;
use Common\Form\Model\Form\Lva\Fieldset\LicenceVehicleAppVar;
use Common\Validator\DateCompare;
use Common\Validator\ValidateIf;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateSla;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Filter\FilterPluginManager;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\ValidatorPluginManager;
use Olcs\Form\Model\Fieldset\InspectionRequestDetails;
use Olcs\Form\Model\Fieldset\ImpoundingFields;
use Olcs\Form\Model\Fieldset\Appeal;
use Olcs\Form\Model\Fieldset\FeePaymentDetails;
use Olcs\Form\Model\Fieldset\GracePeriodDetails;
use Olcs\Form\Model\Fieldset\IrfoGvPermit;
use Olcs\Form\Model\Fieldset\IrfoPsvAuth;
use Olcs\Form\Model\Fieldset\LicenceStatusDecisionCurtail;
use Olcs\Form\Model\Fieldset\LicenceStatusDecisionSuspend;
use Olcs\Form\Model\Fieldset\ProhibitionFields;
use Olcs\Form\Model\Fieldset\PublicInquiryHearingFields;
use Olcs\Form\Model\Fieldset\PublicInquiryAgreedAndLegislationMain;
use Olcs\Form\Model\Fieldset\PublicInquirySlaMain;
use Olcs\Form\Model\Fieldset\RevocationsSla;
use Olcs\Form\Model\Fieldset\Stay;
use Olcs\Form\Model\Fieldset\SubmissionSendTo;
use Olcs\Form\Model\Fieldset\TmCaseNoFurtherAction;
use Olcs\Form\Model\Fieldset\TmCaseRepute;
use Olcs\Form\Model\Fieldset\TmCaseUnfit;
use Olcs\InputFilter\AdjournedHearingDate;
use Olcs\InputFilter\CancelledHearingDate;
use Olcs\InputFilter\RevocationsSlaDate;
use Olcs\Form\Model\Form\Disqualify;
use Olcs\Data\Mapper\PiSla;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StandardDateFieldsTest extends TestCase
{
    #[DataProvider('dateFields')]
    public function testFormModesPreserveDateParts(
        string $fieldset,
        string $field,
        string $inputClass,
        bool $preferFormInputFilter
    ): void {
        $form = $this->createDateForm($fieldset, $field, $preferFormInputFilter);
        $date = ['day' => '12', 'month' => '', 'year' => '2030'];
        if (is_a($inputClass, DateTimeParts::class, true)) {
            $date += ['hour' => '12', 'minute' => '00'];
        }
        $form->setData([$field => $date]);

        self::assertInstanceOf($inputClass, $form->getInputFilter()->get($field));
        self::assertFalse($form->isValid());
        self::assertSame(['dateIncomplete' => '{{fieldLabel}} must include a month'], $form->getMessages()[$field]);
        $element = $form->get($field);
        self::assertSame('12', $element->getDayElement()->getValue());
        self::assertSame('', $element->getMonthElement()->getValue());
        self::assertSame('2030', $element->getYearElement()->getValue());
        if (is_a($inputClass, DateTimeParts::class, true)) {
            self::assertSame('12', $element->getHourElement()->getValue());
            self::assertSame('00', $element->getMinuteElement()->getValue());
        }
    }

    public static function dateFields(): array
    {
        return [
            'fee required, form input' => [FeeRateDetails::class, 'effectiveFrom', DateParts::class, true],
            'fee required, element input' => [FeeRateDetails::class, 'effectiveFrom', DateParts::class, false],
            'inspection optional, form input' => [InspectionRequestDetails::class, 'returnDate', DateParts::class, true],
            'inspection optional, element input' => [InspectionRequestDetails::class, 'returnDate', DateParts::class, false],
            'hearing date-time, form input' => [PublicInquiryHearingFields::class, 'hearingDate', DateTimeParts::class, true],
            'hearing date-time, element input' => [PublicInquiryHearingFields::class, 'hearingDate', DateTimeParts::class, false],
            'permit window start, form input' => [PermitWindowDetails::class, 'startDate', DateTimeParts::class, true],
            'permit window start, element input' => [PermitWindowDetails::class, 'startDate', DateTimeParts::class, false],
            'permit window end, form input' => [PermitWindowDetails::class, 'endDate', DateTimeParts::class, true],
            'permit window end, element input' => [PermitWindowDetails::class, 'endDate', DateTimeParts::class, false],
            'suspend end, form input' => [LicenceStatusDecisionSuspend::class, 'suspendTo', DateTimeParts::class, true],
            'suspend end, element input' => [LicenceStatusDecisionSuspend::class, 'suspendTo', DateTimeParts::class, false],
            'curtail end, form input' => [LicenceStatusDecisionCurtail::class, 'curtailTo', DateTimeParts::class, true],
            'curtail end, element input' => [LicenceStatusDecisionCurtail::class, 'curtailTo', DateTimeParts::class, false],
            'goods expiry, form input' => [IrfoGvPermit::class, 'expiryDate', DateParts::class, true],
            'goods expiry, element input' => [IrfoGvPermit::class, 'expiryDate', DateParts::class, false],
            'psv expiry, form input' => [IrfoPsvAuth::class, 'expiryDate', DateParts::class, true],
            'psv expiry, element input' => [IrfoPsvAuth::class, 'expiryDate', DateParts::class, false],
            'grace end, form input' => [GracePeriodDetails::class, 'endDate', DateParts::class, true],
            'grace end, element input' => [GracePeriodDetails::class, 'endDate', DateParts::class, false],
            'scan received, form input' => [ScanningDetails::class, 'dateReceived', DateParts::class, true],
            'scan received, element input' => [ScanningDetails::class, 'dateReceived', DateParts::class, false],
            'next review, form input' => [DelayItem::class, 'nextReviewDate', DateParts::class, true],
            'next review, element input' => [DelayItem::class, 'nextReviewDate', DateParts::class, false],
            'disqualification, form input' => [Disqualify::class, 'startDate', DateParts::class, true],
            'disqualification, element input' => [Disqualify::class, 'startDate', DateParts::class, false],
            'submission assigned, form input' => [SubmissionSendTo::class, 'assignedDate', DateParts::class, true],
            'submission assigned, element input' => [SubmissionSendTo::class, 'assignedDate', DateParts::class, false],
            'upload evidence, form input' => [UploadEvidenceOperatingCentre::class, 'adPlacedDate', DateParts::class, true],
            'upload evidence, element input' => [UploadEvidenceOperatingCentre::class, 'adPlacedDate', DateParts::class, false],
            ...self::vehicleDateFields(),
        ];
    }

    public static function vehicleDateFields(): array
    {
        $cases = [];
        foreach ([LicenceLicenceVehicle::class, LicenceVehicle::class, LicenceVehicleAppVar::class] as $fieldset) {
            $fields = [
                'receivedDate' => ConditionallyValidatedDateParts::class,
                'specifiedDate' => ConditionallyValidatedDateTimeParts::class,
                'removalDate' => ConditionallyValidatedDateParts::class,
            ];
            if ($fieldset === LicenceLicenceVehicle::class) {
                $fields['warningLetterSeedDate'] = ConditionallyValidatedDateParts::class;
                $fields['warningLetterSentDate'] = ConditionallyValidatedDateParts::class;
            }
            foreach ($fields as $field => $input) {
                $cases[] = [$fieldset, $field, $input, true];
                $cases[] = [$fieldset, $field, $input, false];
            }
        }
        return $cases;
    }

    #[DataProvider('formModes')]
    public function testRemovedGoodsVehicleChecksOnlyRemovalDate(bool $preferFormInputFilter): void
    {
        foreach (['receivedDate', 'specifiedDate', 'removalDate', 'warningLetterSeedDate', 'warningLetterSentDate'] as $field) {
            $input = $this->createDateForm(LicenceLicenceVehicle::class, $field, $preferFormInputFilter)
                ->getInputFilter()->get($field);
            $input->setRequired(false);
            $input->setValidatorChain(new \Laminas\Validator\ValidatorChain());
            if ($field !== 'removalDate') {
                $input->setValidateDateParts(false);
            }
            $value = ['day' => '12', 'month' => '', 'year' => '2020'];
            if ($field === 'specifiedDate') {
                $value += ['hour' => '12', 'minute' => '00'];
            }
            $input->setValue($value);
            self::assertSame($field !== 'removalDate', $input->isValid());
            if ($field === 'removalDate') {
                self::assertArrayHasKey('dateIncomplete', $input->getMessages());
            }
        }
    }

    #[DataProvider('formModes')]
    public function testOptionalBlankAndValidDateKeepExistingBehaviour(bool $preferFormInputFilter): void
    {
        $form = $this->createDateForm(InspectionRequestDetails::class, 'returnDate', $preferFormInputFilter);
        $form->setData(['returnDate' => ['day' => '', 'month' => '', 'year' => '']]);
        self::assertTrue($form->isValid());
        self::assertNull($form->getData()['returnDate']);

        $form = $this->createDateForm(InspectionRequestDetails::class, 'returnDate', $preferFormInputFilter);
        $form->setData(['returnDate' => ['day' => '12', 'month' => '03', 'year' => '2020']]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame('2020-03-12', $form->getData()['returnDate']);
    }

    public static function formModes(): array
    {
        return ['form input' => [true], 'element input' => [false]];
    }

    #[DataProvider('notificationDateFields')]
    public function testNotificationDateAllowsBlankAndRetainsComparison(
        string $fieldset,
        bool $preferFormInputFilter
    ): void {
        $input = $this->createDateForm($fieldset, 'notifiedDate', $preferFormInputFilter)
            ->getInputFilter()->get('notifiedDate');
        $context = ['decisionDate' => ['day' => '01', 'month' => '01', 'year' => '2020']];
        $input->setValue(['day' => '', 'month' => '', 'year' => '']);
        self::assertTrue($input->isValid($context), json_encode($input->getMessages()));
        $input->setValue(['day' => '02', 'month' => '', 'year' => '2020']);
        self::assertFalse($input->isValid($context));
        self::assertArrayHasKey('dateIncomplete', $input->getMessages());
        $input->setValue(['day' => '31', 'month' => '12', 'year' => '2019']);
        self::assertFalse($input->isValid($context));
        $input->setValue(['day' => '02', 'month' => '01', 'year' => '2020']);
        self::assertTrue($input->isValid($context), json_encode($input->getMessages()));
    }

    public static function notificationDateFields(): array
    {
        return [
            [TmCaseUnfit::class, true], [TmCaseUnfit::class, false],
            [TmCaseRepute::class, true], [TmCaseRepute::class, false],
            [TmCaseNoFurtherAction::class, true], [TmCaseNoFurtherAction::class, false],
        ];
    }

    #[DataProvider('formModes')]
    public function testIrfoExpiryRetainsDateComparison(bool $preferFormInputFilter): void
    {
        $input = $this->createDateForm(IrfoGvPermit::class, 'expiryDate', $preferFormInputFilter)
            ->getInputFilter()->get('expiryDate');
        $context = [
            'inForceDate' => ['day' => '01', 'month' => '01', 'year' => '2020'],
            'expiryDate' => ['day' => '31', 'month' => '12', 'year' => '2019'],
        ];
        $input->setValue($context['expiryDate']);
        self::assertFalse($input->isValid($context));
        $context['expiryDate'] = ['day' => '02', 'month' => '01', 'year' => '2020'];
        $input->setValue($context['expiryDate']);
        self::assertTrue($input->isValid($context), json_encode($input->getMessages()));
    }

    #[DataProvider('formModes')]
    public function testFeeDateStillRequiresTodayOrFuture(bool $preferFormInputFilter): void
    {
        $form = $this->createDateForm(FeeRateDetails::class, 'effectiveFrom', $preferFormInputFilter);
        $form->setData(['effectiveFrom' => ['day' => '01', 'month' => '01', 'year' => '2000']]);
        self::assertFalse($form->isValid());
        self::assertNotEmpty($form->getMessages()['effectiveFrom']);

        $form = $this->createDateForm(FeeRateDetails::class, 'effectiveFrom', $preferFormInputFilter);
        $form->setData(['effectiveFrom' => ['day' => '01', 'month' => '01', 'year' => '9999']]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame('9999-01-01', $form->getData()['effectiveFrom']);
    }

    #[DataProvider('formModes')]
    public function testValidDateTimeRetainsTime(bool $preferFormInputFilter): void
    {
        $form = $this->createDateForm(PublicInquiryHearingFields::class, 'hearingDate', $preferFormInputFilter);
        $form->setData(['hearingDate' => [
            'day' => '12', 'month' => '03', 'year' => '2020', 'hour' => '12', 'minute' => '15',
        ]]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame('2020-03-12 12:15:00', $form->getData()['hearingDate']);
    }

    #[DataProvider('formModes')]
    public function testOptionalDateStillUsesConditionalComparison(bool $preferFormInputFilter): void
    {
        $form = $this->createDateForm(ProhibitionFields::class, 'clearedDate', $preferFormInputFilter);
        $input = $form->getInputFilter()->get('clearedDate');
        $context = ['prohibitionDate' => ['day' => '01', 'month' => '01', 'year' => '2020']];

        $input->setValue(['day' => '', 'month' => '', 'year' => '']);
        self::assertTrue($input->isValid($context));
        $input->setValue(['day' => '12', 'month' => '', 'year' => '2020']);
        self::assertFalse($input->isValid($context));
        self::assertArrayHasKey('dateIncomplete', $input->getMessages());
        $input->setValue(['day' => '31', 'month' => '12', 'year' => '2019']);
        self::assertFalse($input->isValid($context));
        self::assertNotEmpty($input->getMessages());
        $input->setValue(['day' => '02', 'month' => '01', 'year' => '2020']);
        self::assertTrue($input->isValid($context));
    }

    #[DataProvider('conditionalHearingDates')]
    public function testConditionalHearingDate(
        string $field,
        string $checkbox,
        string $inputClass,
        bool $preferFormInputFilter
    ): void {
        $form = $this->createDateForm(PublicInquiryHearingFields::class, $field, $preferFormInputFilter);
        $input = $form->getInputFilter()->get($field);
        self::assertInstanceOf($inputClass, $input);

        $blank = ['day' => '', 'month' => '', 'year' => ''];
        $partial = ['day' => '12', 'month' => '', 'year' => '2020'];
        $complete = ['day' => '12', 'month' => '03', 'year' => '2020'];
        if ($field === 'adjournedDate') {
            $blank += ['hour' => '', 'minute' => ''];
            $partial += ['hour' => '12', 'minute' => '00'];
            $complete += ['hour' => '12', 'minute' => '00'];
        }

        $input->setValue($blank);
        self::assertFalse($input->isValid([$checkbox => 'Y']));
        self::assertArrayHasKey('dateMissing', $input->getMessages());
        $input->setValue($partial);
        self::assertFalse($input->isValid([$checkbox => 'Y']));
        self::assertArrayHasKey('dateIncomplete', $input->getMessages());
        $input->setValue(['day' => '31', 'month' => '02', 'year' => '2020'] + (
            $field === 'adjournedDate' ? ['hour' => '12', 'minute' => '00'] : []
        ));
        self::assertFalse($input->isValid([$checkbox => 'Y']));
        self::assertArrayHasKey('dateInvalid', $input->getMessages());
        $input->setValue($complete);
        self::assertTrue($input->isValid([$checkbox => 'Y']), json_encode($input->getMessages()));

        foreach ([$blank, $partial, $complete] as $value) {
            $existing = $this->createDateForm(
                PublicInquiryHearingFields::class,
                $field,
                $preferFormInputFilter,
                true
            )->getInputFilter()->get($field);
            $current = $this->createDateForm(
                PublicInquiryHearingFields::class,
                $field,
                $preferFormInputFilter
            )->getInputFilter()->get($field);
            $existing->setValue($value);
            $current->setValue($value);
            self::assertSame($existing->isValid([$checkbox => 'N']), $current->isValid([$checkbox => 'N']));
        }
    }

    public static function conditionalHearingDates(): array
    {
        return [
            'cancelled, form input' => ['cancelledDate', 'isCancelled', CancelledHearingDate::class, true],
            'cancelled, element input' => ['cancelledDate', 'isCancelled', CancelledHearingDate::class, false],
            'adjourned, form input' => ['adjournedDate', 'isAdjourned', AdjournedHearingDate::class, true],
            'adjourned, element input' => ['adjournedDate', 'isAdjourned', AdjournedHearingDate::class, false],
        ];
    }

    #[DataProvider('revocationDates')]
    public function testRevocationDatesKeepHiddenFieldBehaviour(
        string $field,
        string $condition,
        string $activeValue,
        bool $preferFormInputFilter
    ): void {
        $input = $this->createDateForm(RevocationsSla::class, $field, $preferFormInputFilter)
            ->getInputFilter()->get($field);
        self::assertInstanceOf(RevocationsSlaDate::class, $input);
        $partial = ['day' => '12', 'month' => '', 'year' => '2020'];
        $input->setValue($partial);
        self::assertFalse($input->isValid([$condition => $activeValue]));
        self::assertArrayHasKey('dateIncomplete', $input->getMessages());
        $input->setValue(['day' => '', 'month' => '', 'year' => '']);
        self::assertTrue($input->isValid([$condition => $activeValue]));
        $input->setValue(['day' => '12', 'month' => '03', 'year' => '2020']);
        self::assertTrue($input->isValid([$condition => $activeValue]));
        $existing = $this->createDateForm(RevocationsSla::class, $field, $preferFormInputFilter, true)
            ->getInputFilter()->get($field);
        $existing->setValue($partial);
        $input->setValue($partial);
        self::assertSame($existing->isValid([$condition => '']), $input->isValid([$condition => '']));
    }

    public static function revocationDates(): array
    {
        $fields = [
            'approvalSubmissionIssuedDate' => ['isSubmissionRequiredForApproval', '1'],
            'approvalSubmissionReturnedDate' => ['isSubmissionRequiredForApproval', '1'],
            'finalSubmissionIssuedDate' => ['isSubmissionRequiredForAction', '1'],
            'finalSubmissionReturnedDate' => ['isSubmissionRequiredForAction', '1'],
            'revocationLetterIssuedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_revoke'],
            'nfaLetterIssuedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_nfa'],
            'warningLetterIssuedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_warning'],
            'piAgreedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_pi'],
            'otherActionAgreedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_other'],
        ];
        $cases = [];
        foreach ($fields as $field => [$condition, $value]) {
            $cases[$field . ', form input'] = [$field, $condition, $value, true];
            $cases[$field . ', element input'] = [$field, $condition, $value, false];
        }
        return $cases;
    }

    #[DataProvider('conditionalPaymentAndWithdrawalDates')]
    public function testConditionalPaymentAndWithdrawalDate(
        string $fieldset,
        string $field,
        string $condition,
        string $active,
        string $inactive,
        bool $preferFormInputFilter
    ): void {
        $input = $this->createDateForm($fieldset, $field, $preferFormInputFilter)->getInputFilter()->get($field);
        $blank = ['day' => '', 'month' => '', 'year' => ''];
        $partial = ['day' => '12', 'month' => '', 'year' => '2020'];
        $input->setValue($blank);
        self::assertFalse($input->isValid([$condition => $active]));
        self::assertArrayHasKey('dateMissing', $input->getMessages());
        $input->setValue($partial);
        self::assertFalse($input->isValid([$condition => $active]));
        self::assertArrayHasKey('dateIncomplete', $input->getMessages());
        foreach ([$blank, $partial] as $value) {
            $existing = $this->createDateForm($fieldset, $field, $preferFormInputFilter, true)
                ->getInputFilter()->get($field);
            $existing->setValue($value);
            $input->setValue($value);
            self::assertSame($existing->isValid([$condition => $inactive]), $input->isValid([$condition => $inactive]));
        }
    }

    public static function conditionalPaymentAndWithdrawalDates(): array
    {
        $fields = [
            [Appeal::class, 'withdrawnDate', 'isWithdrawn', 'Y', 'N'],
            [Stay::class, 'withdrawnDate', 'isWithdrawn', 'Y', 'N'],
            [FeePaymentDetails::class, 'receiptDate', 'paymentType', 'fpm_cheque', 'fpm_card_offline'],
            [FeePaymentDetails::class, 'chequeDate', 'paymentType', 'fpm_cheque', 'fpm_po'],
        ];
        $cases = [];
        foreach ($fields as $field) {
            $cases[] = [...$field, true];
            $cases[] = [...$field, false];
        }
        return $cases;
    }

    #[DataProvider('publicInquiryConditionalDates')]
    public function testPublicInquiryConditionalDates(
        string $fieldset,
        string $field,
        string $condition,
        string $active,
        bool $preferFormInputFilter
    ): void {
        $input = $this->createDateForm($fieldset, $field, $preferFormInputFilter)->getInputFilter()->get($field);
        $context = [$condition => $active];
        $input->setValue(['day' => '', 'month' => '', 'year' => '']);
        self::assertTrue($input->isValid($context), json_encode($input->getMessages()));
        $input->setValue(['day' => '12', 'month' => '', 'year' => '2020']);
        self::assertFalse($input->isValid($context));
        self::assertArrayHasKey('dateIncomplete', $input->getMessages());
        $input->setValue(['day' => '12', 'month' => '03', 'year' => '2020']);
        self::assertTrue($input->isValid($context), json_encode($input->getMessages()));
        $input->setValue(['day' => '12', 'month' => '03', 'year' => '9999']);
        self::assertFalse($input->isValid($context));
    }

    public static function publicInquiryConditionalDates(): array
    {
        $fields = [
            [PublicInquiryAgreedAndLegislationMain::class, 'ecmsFirstReceivedDate', 'isEcmsCase', 'Y'],
            [PublicInquirySlaMain::class, 'tcWrittenDecisionDate', 'writtenOutcome', 'piwo_decision'],
            [PublicInquirySlaMain::class, 'writtenDecisionLetterDate', 'writtenOutcome', 'piwo_decision'],
            [PublicInquirySlaMain::class, 'tcWrittenReasonDate', 'writtenOutcome', 'piwo_reason'],
            [PublicInquirySlaMain::class, 'writtenReasonLetterDate', 'writtenOutcome', 'piwo_reason'],
        ];
        $cases = [];
        foreach ($fields as $field) {
            $cases[] = [...$field, true];
            $cases[] = [...$field, false];
        }
        return $cases;
    }

    #[DataProvider('formModes')]
    public function testVerbalDecisionLetterChecksDatePartsWithoutChangingExistingPastRule(
        bool $preferFormInputFilter
    ): void {
        $input = $this->createDateForm(PublicInquirySlaMain::class, 'decisionLetterSentDate', $preferFormInputFilter)
            ->getInputFilter()->get('decisionLetterSentDate');
        $input->setValue(['day' => '12', 'month' => '', 'year' => '2020']);
        self::assertFalse($input->isValid(['writtenOutcome' => 'piwo_verbal']));
        self::assertArrayHasKey('dateIncomplete', $input->getMessages());
        $input->setValue(['day' => '12', 'month' => '03', 'year' => '9999']);
        self::assertTrue($input->isValid(['writtenOutcome' => 'piwo_verbal']), json_encode($input->getMessages()));
        self::assertTrue($input->isValid(['writtenOutcome' => 'piwo_decision']));
    }

    #[DataProvider('formModes')]
    public function testHiddenVerbalDecisionLetterDoesNotBlockWrittenDecision(bool $preferFormInputFilter): void
    {
        $form = $this->createDateForm(
            PublicInquirySlaMain::class,
            'decisionLetterSentDate',
            $preferFormInputFilter,
            false,
            'writtenOutcome',
            ['writtenDecisionLetterDate']
        );
        $form->setData([
            'writtenOutcome' => 'piwo_decision',
            'decisionLetterSentDate' => ['day' => '12', 'month' => '03', 'year' => '9999'],
            'writtenDecisionLetterDate' => ['day' => '12', 'month' => '03', 'year' => '2020'],
        ]);

        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame('2020-03-12', $form->getData()['writtenDecisionLetterDate']);

        $form = $this->createDateForm(
            PublicInquirySlaMain::class,
            'decisionLetterSentDate',
            $preferFormInputFilter,
            false,
            'writtenOutcome',
            ['writtenDecisionLetterDate']
        );
        $form->setData([
            'writtenOutcome' => 'piwo_decision',
            'decisionLetterSentDate' => ['day' => '12', 'month' => '03', 'year' => '9999'],
            'writtenDecisionLetterDate' => ['day' => '12', 'month' => '03', 'year' => '9999'],
        ]);

        self::assertFalse($form->isValid());
        self::assertArrayNotHasKey('decisionLetterSentDate', $form->getMessages());
        self::assertArrayHasKey('writtenDecisionLetterDate', $form->getMessages());
    }

    #[DataProvider('formModes')]
    public function testInvalidVerbalDateIsNotSentAfterSwitchingToWrittenDecision(bool $preferFormInputFilter): void
    {
        $form = $this->createDateForm(
            PublicInquirySlaMain::class,
            'decisionLetterSentDate',
            $preferFormInputFilter,
            false,
            'writtenOutcome',
            ['writtenDecisionLetterDate']
        );
        $invalidDate = ['day' => '31', 'month' => '02', 'year' => '2020'];
        $emptyDate = ['day' => '', 'month' => '', 'year' => ''];
        $form->setData([
            'writtenOutcome' => 'piwo_verbal',
            'decisionLetterSentDate' => $invalidDate,
            'writtenDecisionLetterDate' => $emptyDate,
        ]);
        self::assertFalse($form->isValid());
        self::assertArrayHasKey('dateInvalid', $form->getMessages()['decisionLetterSentDate']);

        $form = $this->createDateForm(
            PublicInquirySlaMain::class,
            'decisionLetterSentDate',
            $preferFormInputFilter,
            false,
            'writtenOutcome',
            ['writtenDecisionLetterDate']
        );
        $form->setData([
            'writtenOutcome' => 'piwo_decision',
            'decisionLetterSentDate' => $invalidDate,
            'writtenDecisionLetterDate' => $emptyDate,
        ]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        $data = PiSla::mapFromForm(['fields' => $form->getData() + ['id' => '1', 'version' => '1']]);
        self::assertArrayNotHasKey('decisionLetterSentDate', $data);
        self::assertNull($data['writtenDecisionLetterDate']);

        $builder = new AnnotationBuilder();
        $builder->setFilterManager(new FilterPluginManager(new ServiceManager()));
        $builder->setValidatorManager(new ValidatorPluginManager(new ServiceManager()));
        $command = $builder->createCommand(UpdateSla::create($data));
        self::assertTrue($command->isValid(), json_encode($command->getMessages()));
    }

    #[DataProvider('formModes')]
    public function testHiddenWrittenDecisionDateDoesNotBlockVerbalOutcome(bool $preferFormInputFilter): void
    {
        $form = $this->createDateForm(
            PublicInquirySlaMain::class,
            'writtenDecisionLetterDate',
            $preferFormInputFilter,
            false,
            'writtenOutcome',
            ['decisionLetterSentDate']
        );
        $form->setData([
            'writtenOutcome' => 'piwo_verbal',
            'writtenDecisionLetterDate' => ['day' => '31', 'month' => '02', 'year' => '2020'],
            'decisionLetterSentDate' => ['day' => '12', 'month' => '03', 'year' => '9999'],
        ]);

        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        $data = PiSla::mapFromForm(['fields' => $form->getData()]);
        self::assertArrayNotHasKey('writtenDecisionLetterDate', $data);
        self::assertSame('9999-03-12', $data['decisionLetterSentDate']);
    }

    #[DataProvider('formModes')]
    public function testNestedSlaFormDiscardsInvalidInactiveDateBeforeDtoValidation(bool $preferFormInputFilter): void
    {
        $form = $this->createDateForm(
            PublicInquirySlaMain::class,
            'decisionLetterSentDate',
            $preferFormInputFilter,
            false,
            'writtenOutcome',
            ['writtenDecisionLetterDate', 'tcWrittenDecisionDate', 'tcWrittenReasonDate', 'writtenReasonLetterDate'],
            true
        );
        $emptyDate = ['day' => '', 'month' => '', 'year' => ''];
        $form->setData(['fields' => [
            'writtenOutcome' => 'piwo_decision',
            'decisionLetterSentDate' => ['day' => '31', 'month' => '02', 'year' => '2020'],
            'writtenDecisionLetterDate' => $emptyDate,
            'tcWrittenDecisionDate' => $emptyDate,
            'tcWrittenReasonDate' => $emptyDate,
            'writtenReasonLetterDate' => $emptyDate,
        ]]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        $data = $form->getData();
        $data['fields'] += ['id' => '1', 'version' => '1'];
        $builder = new AnnotationBuilder();
        $builder->setFilterManager(new FilterPluginManager(new ServiceManager()));
        $builder->setValidatorManager(new ValidatorPluginManager(new ServiceManager()));
        $unmappedCommand = $builder->createCommand(UpdateSla::create($data['fields']));
        self::assertFalse($unmappedCommand->isValid());
        self::assertArrayHasKey('decisionLetterSentDate', $unmappedCommand->getMessages());

        $mappedData = PiSla::mapFromForm($data);
        self::assertArrayNotHasKey('decisionLetterSentDate', $mappedData);
        $command = $builder->createCommand(UpdateSla::create($mappedData));
        self::assertTrue($command->isValid(), json_encode($command->getMessages()));
    }

    #[DataProvider('formModes')]
    public function testImpoundingHearingDateRespectsTypeAndComparison(bool $preferFormInputFilter): void
    {
        $input = $this->createDateForm(ImpoundingFields::class, 'hearingDate', $preferFormInputFilter)
            ->getInputFilter()->get('hearingDate');
        $context = [
            'impoundingType' => 'impt_hearing',
            'applicationReceiptDate' => ['day' => '01', 'month' => '01', 'year' => '2020'],
        ];
        $input->setValue(['day' => '12', 'month' => '', 'year' => '2020', 'hour' => '12', 'minute' => '00']);
        self::assertFalse($input->isValid($context));
        self::assertArrayHasKey('dateIncomplete', $input->getMessages());
        $input->setValue(['day' => '31', 'month' => '12', 'year' => '2019', 'hour' => '12', 'minute' => '00']);
        self::assertFalse($input->isValid($context));
        $input->setValue(['day' => '02', 'month' => '01', 'year' => '2020', 'hour' => '12', 'minute' => '00']);
        self::assertTrue($input->isValid($context), json_encode($input->getMessages()));
    }

    #[DataProvider('formModes')]
    public function testCommunityLicenceStopDatesRespectWithdrawal(bool $preferFormInputFilter): void
    {
        foreach (['startDate', 'endDate'] as $field) {
            $input = $this->createDateForm(CommunityLicencesStopDates::class, $field, $preferFormInputFilter)
                ->getInputFilter()->get($field);
            $partial = ['day' => '12', 'month' => '', 'year' => '2030'];
            $input->setValue($partial);
            self::assertFalse($input->isValid());
            self::assertArrayHasKey('dateIncomplete', $input->getMessages());
            $input->setValidateDateParts(false);
            if ($field === 'startDate') {
                $input->setRequired(false);
                $input->setValidatorChain(new \Laminas\Validator\ValidatorChain());
            }

            $input->setValue($partial);
            self::assertTrue($input->isValid());
        }
    }

    #[DataProvider('formModes')]
    public function testHiddenAdvertisementDateRetainsPreviousBehaviour(bool $preferFormInputFilter): void
    {
        $partial = ['day' => '12', 'month' => '', 'year' => '2020'];
        $input = $this->createDateForm(AdvertisementsAdPlacedNow::class, 'adPlacedDate', $preferFormInputFilter)
            ->getInputFilter()->get('adPlacedDate');
        $input->setValue($partial);
        self::assertFalse($input->isValid());
        self::assertArrayHasKey('dateIncomplete', $input->getMessages());
        $input->setValidateDateParts(false);
        $input->setValue($partial);
        $original = $this->createDateForm(
            AdvertisementsAdPlacedNow::class,
            'adPlacedDate',
            $preferFormInputFilter,
            true
        )->getInputFilter()->get('adPlacedDate');
        $original->setValue($partial);
        self::assertSame($original->isValid(), $input->isValid());
    }

    #[DataProvider('conditionalHearingDates')]
    public function testHearingFormUsesCheckboxContext(
        string $field,
        string $checkbox,
        string $inputClass,
        bool $preferFormInputFilter
    ): void {
        $form = $this->createDateForm(
            PublicInquiryHearingFields::class,
            $field,
            $preferFormInputFilter,
            false,
            $checkbox
        );
        $date = ['day' => '12', 'month' => '', 'year' => '2020'];
        if ($inputClass === AdjournedHearingDate::class) {
            $date += ['hour' => '12', 'minute' => '00'];
        }
        $form->setData([$checkbox => 'Y', $field => $date]);
        self::assertFalse($form->isValid());
        self::assertSame(
            ['dateIncomplete' => '{{fieldLabel}} must include a month'],
            $form->getMessages()[$field]
        );
        self::assertSame('12', $form->get($field)->getDayElement()->getValue());
        self::assertSame('', $form->get($field)->getMonthElement()->getValue());

        $form = $this->createDateForm(
            PublicInquiryHearingFields::class,
            $field,
            $preferFormInputFilter,
            false,
            $checkbox
        );
        $date['month'] = '03';
        $form->setData([$checkbox => 'Y', $field => $date]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame(
            $inputClass === AdjournedHearingDate::class ? '2020-03-12 12:00:00' : '2020-03-12',
            $form->getData()[$field]
        );
    }

    private function createDateForm(
        string $fieldset,
        string $field,
        bool $preferFormInputFilter,
        bool $originalInput = false,
        ?string $contextField = null,
        array $additionalFields = [],
        bool $nested = false
    ): \Laminas\Form\FormInterface {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification($fieldset));
        $spec['type'] = \Common\Form\Form::class;
        $spec['options']['prefer_form_input_filter'] = $preferFormInputFilter;
        $fields = [$field, ...$additionalFields];
        $spec['elements'] = array_values(array_filter(
            $spec['elements'],
            static fn(array $element): bool => in_array($element['spec']['name'], $fields, true)
        ));
        $spec['fieldsets'] = [];
        $spec['input_filter'] = array_intersect_key($spec['input_filter'], array_flip($fields));
        if ($originalInput) {
            unset($spec['elements'][0]['spec']['options']['input_type'], $spec['input_filter'][$field]['type']);
        }
        if ($contextField !== null) {
            $spec['elements'][] = ['spec' => ['name' => $contextField, 'type' => 'Text']];
            $spec['input_filter'][$contextField] = ['name' => $contextField, 'required' => false];
        }

        $elements = new FormElementManager(new ServiceManager());
        $elements->setAlias('DateSelect', DateSelect::class);
        $elements->setInvokableClass(DateSelect::class, DateSelect::class);
        $elements->setAlias('DateTimeSelect', DateTimeSelect::class);
        $elements->setInvokableClass(DateTimeSelect::class, DateTimeSelect::class);
        $factory = new Factory($elements);
        $filters = new FilterPluginManager(new ServiceManager());
        $filters->setAlias('DateSelectNullifier', DateSelectNullifier::class);
        $filters->setInvokableClass(DateSelectNullifier::class, DateSelectNullifier::class);
        $filters->setAlias('DateTimeSelectNullifier', DateTimeSelectNullifier::class);
        $filters->setInvokableClass(DateTimeSelectNullifier::class, DateTimeSelectNullifier::class);
        $factory->getInputFilterFactory()->getDefaultFilterChain()->setPluginManager($filters);
        $validators = new ValidatorPluginManager(new ServiceManager());
        $validators->setAlias('ValidateIf', ValidateIf::class);
        $validators->setInvokableClass(ValidateIf::class, ValidateIf::class);
        $validators->setAlias('DateCompare', DateCompare::class);
        $validators->setInvokableClass(DateCompare::class, DateCompare::class);
        $factory->getInputFilterFactory()->getDefaultValidatorChain()->setPluginManager($validators);

        if ($nested) {
            $spec['name'] = 'fields';
            $spec = [
                'type' => \Common\Form\Form::class,
                'fieldsets' => [['spec' => $spec]],
            ];
        }

        return $factory->createForm($spec);
    }
}
