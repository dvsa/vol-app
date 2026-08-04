<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\AandDStoredPublicationDate;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Alias\SpecificLicenceUndertakings;
use Dvsa\Olcs\Api\Service\Document\Bookmark\BookmarkFactory;
use Dvsa\Olcs\Api\Service\Document\Bookmark\TextBlock;

/**
 * Bookmark Factory test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class BookmarkFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testLocate(): void
    {
        $sut = new BookmarkFactory();

        $this->assertInstanceOf(AandDStoredPublicationDate::class, $sut->locate('AandD_stored_publication_date'));

        $this->assertInstanceOf(SpecificLicenceUndertakings::class, $sut->locate('SPECIFIC_LICENCE_UNDERTAKINGS'));

        $this->assertInstanceOf(TextBlock::class, $sut->locate('invalid'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('allBookmarksProvider')]
    public function testGetClassNameFromToken(mixed $token, mixed $expected): void
    {
        $sut = new BookmarkFactory();

        $actual = $sut->getClassNameFromToken($token);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Includes all bookmarks used in olcs-templates, mapped to existing classes
     * where they exist in the Dvsa\OlcsTest\Api\Service\Document\Bookmark
     * namespace.
     */
    public static function allBookmarksProvider(): \Iterator
    {
        yield ['AandD_stored_publication_date', 'AandDStoredPublicationDate'];
        yield ['AandD_stored_publication_number', 'AandDStoredPublicationNumber'];
        yield ['ADDITIONAL_UNDERTAKINGS', 'AdditionalUndertakings'];
        yield ['Address_1', 'Address1'];
        yield ['ADDRESS_OF_ESTABLISHMENT', 'AddressOfEstablishment'];
        yield ['applicant_name', 'ApplicantName'];
        yield ['application_type', 'ApplicationType'];
        yield ['application_type_NI', 'ApplicationTypeNi'];
        yield ['AUTHORISED_VEHICLES', 'AuthorisedVehicles'];
        yield ['AuthorisedDecision', 'AuthorisedDecision'];
        yield ['AuthorisorName2', 'AuthorisorName2'];
        yield ['AuthorisorName3', 'AuthorisorName3'];
        yield ['AuthorisorTeam', 'AuthorisorTeam'];
        yield ['background_to_imposition_of_conditions', 'BackgroundToImpositionOfConditions'];
        yield ['background_to_imposition_of_conditions_n', 'BackgroundToImpositionOfConditionsN'];
        yield ['BR_COUNCILS_NOTIFIED', 'BrCouncilsNotified'];
        yield ['BR_DATE_RECEIVED', 'BrDateReceived'];
        yield ['BR_EFFECTIVE_DATE', 'BrEffectiveDate'];
        yield ['BR_LOGO', 'BrLogo'];
        yield ['BR_LOGO', 'BrLogo'];
        yield ['BR_N_P_NO', 'BrNPNo'];
        yield ['BR_OP_ADDRESS', 'BrOpAddress'];
        yield ['BR_REASON_FOR_VAR', 'BrReasonForVar'];
        yield ['BR_REG_NO_OUR_REF', 'BrRegNoOurRef'];
        yield ['BR_REG_NOBR_FINISH_POINT', 'BrRegNobrFinishPoint'];
        yield ['BR_ROUTE_NUM', 'BrRouteNum'];
        yield ['BR_ROUTE_NUMBR_EFFECTIVE_DATE', 'BrRouteNumbrEffectiveDate'];
        yield ['BR_START_POINT', 'BrStartPoint'];
        yield ['caseworker_details', 'CaseworkerDetails'];
        yield ['caseworker_name', 'CaseworkerName'];
        yield ['caseworker_name1', 'CaseworkerName1'];
        yield ['CONDITIONS', 'Conditions'];
        yield ['Conditions', 'Conditions'];
        yield ['Cont_Next_Exp_Date', 'ContNextExpDate'];
        yield ['CONTINUATION_DATE', 'ContinuationDate'];
        yield ['Continuation_Date1', 'ContinuationDate1'];
        yield ['Continuation_Date2', 'ContinuationDate2'];
        yield ['Continuation_Date3', 'ContinuationDate3'];
        yield ['Continuation_Date4', 'ContinuationDate4'];
        yield ['Continuation_Date6', 'ContinuationDate6'];
        yield ['COPY_TYPE', 'CopyType'];
        yield ['Date_From', 'DateFrom'];
        yield ['Date_To', 'DateTo'];
        yield ['dear_sir_or_madam', 'DearSirOrMadam'];
        yield ['Disc_List', 'DiscList'];
        yield ['DISCS_ISSUED', 'DiscsIssued'];
        yield ['DOC_CATEGORY_ID_SCAN', 'DocCategoryIdScan'];
        yield ['DOC_CATEGORY_NAME_SCAN', 'DocCategoryNameScan'];
        yield ['DOC_DESCRIPTION_ID_SCAN', 'DocDescriptionIdScan'];
        yield ['DOC_DESCRIPTION_NAME_SCAN', 'DocDescriptionNameScan'];
        yield ['DOC_SUBCATEGORY_ID_SCAN', 'DocSubcategoryIdScan'];
        yield ['DOC_SUBCATEGORY_NAME_SCAN', 'DocSubcategoryNameScan'];
        yield ['ENTITY_ID_REPEAT_SCAN', 'EntityIdRepeatScan'];
        yield ['ENTITY_ID_SCAN', 'EntityIdScan'];
        yield ['ENTITY_ID_TYPE_SCAN', 'EntityIdTypeScan'];
        yield ['ERRATA_SECTION', 'ErrataSection'];
        yield ['European_Licence_Number', 'EuropeanLicenceNumber'];
        yield ['FEE_DUE_DATE', 'FeeDueDate'];
        yield ['FEE_REQ_GRANT_NUMBER', 'FeeReqGrantNumber'];
        yield ['FOOTER_LICENCE_NUMBER', 'FooterLicenceNumber'];
        yield ['FStanding_CapitalReserves', 'FStandingCapitalReserves'];
        yield ['FStanding_ProvedDate', 'FStandingProvedDate'];
        yield ['GV_LIC_FEE2', 'GvLicFee2'];
        yield ['ins_more_freq_no', 'InsMoreFreqNo'];
        yield ['ins_more_freq_yes', 'InsMoreFreqYes'];
        yield ['ins_no_trailers', 'InsNoTrailers'];
        yield ['ins_no_vhls', 'InsNoVhls'];
        yield ['INT_LIC_FEE', 'IntLicFee'];
        yield ['INTERIM_LICENCE_TYPE', 'InterimLicenceType'];
        yield ['INTERIM_OPERATING_CENTRES', 'InterimOperatingCentres'];
        yield ['INTERIM_SPECIFIC_LICENCE_CONDITIONS', 'InterimSpecificLicenceConditions'];
        yield ['INTERIM_SPECIFIC_LICENCE_UNDERTAKINGS', 'InterimSpecificLicenceUndertakings'];
        yield ['INTERIM_STANDARD_CONDITIONS', 'InterimStandardConditions'];
        yield ['INTERIM_TRAILERS', 'InterimTrailers'];
        yield ['INTERIM_UNLINKED_TM', 'InterimUnlinkedTm'];
        yield ['INTERIM_VALID_DATE', 'InterimValidDate'];
        yield ['INTERIM_VEHICLES', 'InterimVehicles'];
        yield ['ISSUE_DATE', 'IssueDate'];
        yield ['letter_date_add_10_days', 'LetterDateAdd10Days'];
        yield ['letter_date_add_14_days', 'LetterDateAdd14Days'];
        yield ['letter_date_add_21_days', 'LetterDateAdd21Days'];
        yield ['letter_date_add_28_days', 'LetterDateAdd28Days'];
        yield ['Lic_Address', 'LicAddress'];
        yield ['Lic_Mail_Address', 'LicMailAddress'];
        yield ['Lic_Mail_Name', 'LicMailName'];
        yield ['Licence_Holder_Address', 'LicenceHolderAddress'];
        yield ['licence_holder_address', 'LicenceHolderAddress'];
        yield ['licence_holder_name', 'LicenceHolderName'];
        yield ['LICENCE_NUMBER', 'LicenceNumber'];
        yield ['Licence_Number', 'LicenceNumber'];
        yield ['licence_number', 'LicenceNumber'];
        yield ['Licence_Number1', 'LicenceNumber1'];
        yield ['Licence_Number2', 'LicenceNumber2'];
        yield ['Licence_Number3', 'LicenceNumber3'];
        yield ['Licence_Number4', 'LicenceNumber4'];
        yield ['Licence_Number5', 'LicenceNumber5'];
        yield ['Licence_Number6', 'LicenceNumber6'];
        yield ['Licence_Number7', 'LicenceNumber7'];
        yield ['Licence_Number8', 'LicenceNumber8'];
        yield ['licence_number__01', 'LicenceNumber01'];
        yield ['LICENCE_NUMBER_REPEAT', 'LicenceNumberRepeat'];
        yield ['licence_number_repeat', 'LicenceNumberRepeat'];
        yield ['LICENCE_NUMBER_REPEAT_SCAN', 'LicenceNumberRepeatScan'];
        yield ['LICENCE_NUMBER_SCAN', 'LicenceNumberScan'];
        yield ['Licence_Operating_Centres', 'LicenceOperatingCentres'];
        yield ['Licence_Partners', 'LicencePartners'];
        yield ['licence_review_date', 'LicenceReviewDate'];
        yield ['LICENCE_TITLE', 'LicenceTitle'];
        yield ['Licence_Trailer_Limit', 'LicenceTrailerLimit'];
        yield ['LICENCE_TYPE', 'LicenceType'];
        yield ['Licence_Type', 'LicenceType'];
        yield ['Licence_Vehicle_Limit', 'LicenceVehicleLimit'];
        yield ['Name', 'Name'];
        yield ['NandP_stored_publication_date', 'NandPStoredPublicationDate'];
        yield ['NandP_stored_publication_number', 'NandPStoredPublicationNumber'];
        yield ['NO_DISCS_PRINTED', 'NoDiscsPrinted'];
        yield ['OBJ_DEADLINE', 'ObjDeadline'];
        yield ['OP_ADDRESS', 'OpAddress'];
        yield ['op_address', 'OpAddress'];
        yield ['OP_DETAILS', 'OpDetails'];
        yield ['OP_FAO_NAME', 'OpFaoName'];
        yield ['op_fao_name', 'OpFaoName'];
        yield ['op_name', 'OpName'];
        yield ['OP_Name_Only', 'OpNameOnly'];
        yield ['OPERATING_CENTRES', 'OperatingCentres'];
        yield ['OPERATOR_NAME', 'OperatorName'];
        yield ['Operator_Name', 'OperatorName'];
        yield ['Original_Copy', 'OriginalCopy'];
        yield ['p_GV_OR_PSV', 'PGvOrPsv'];
        yield ['p_GV_OR_PSV_NI', 'PGvOrPsvNi'];
        yield ['p_PI_S35_GV_PSV_S54', 'PPiS35GvPsvS54'];
        yield ['p_PI_S35_GV_PSV_S54_NI', 'PPiS35GvPsvS54Ni'];
        yield ['p_unacceptable_advert', 'PUnacceptableAdvert'];
        yield ['p_unacceptable_advert_NI', 'PUnacceptableAdvertNi'];
        yield ['Phone_Numbers', 'PhoneNumbers'];
        yield ['PI_HEARING_DATE', 'PiHearingDate'];
        yield ['PI_HEARING_VENUE', 'PiHearingVenue'];
        yield ['POLICE_PEOPLE', 'PolicePeople'];
        yield ['POLICE_PERSON', 'PolicePerson'];
        yield ['Psv_Disc_Page', 'PsvDiscPage'];
        yield ['PSV_STANDARD_CONDITIONS', 'PsvStandardConditions'];
        yield ['PUBLICATION_DATE', 'PublicationDate'];
        yield ['PUBLICATION_NUMBER', 'PublicationNumber'];
        yield ['reason_for_closure', 'ReasonForClosure'];
        yield ['reason_for_closure_NI', 'ReasonForClosureNi'];
        yield ['reason_for_no_review', 'ReasonForNoReview'];
        yield ['reason_for_no_review_NI', 'ReasonForNoReviewNi'];
        yield ['Registered_Number', 'RegisteredNumber'];
        yield ['RequestDate', 'RequestDate'];
        yield ['RequestMode', 'RequestMode'];
        yield ['REVIEW_DATE', 'ReviewDate'];
        yield ['Review_Date_Add_2_Months', 'ReviewDateAdd2Months'];
        yield ['S43_AUTHORISED_DECISION', 'S43AuthorisedDecision'];
        yield ['S43_REQUEST_MODE', 'S43RequestMode'];
        yield ['S43_Requestor_Name_Body_Address', 'S43RequestorNameBodyAddress'];
        yield ['S9_authorised_decision', 'S9AuthorisedDecision'];
        yield ['S9_authorisors_age', 'S9AuthorisorsAge'];
        yield ['S9_REQUEST_MODE', 'S9RequestMode'];
        yield ['S9_Requestor_Name_Body_Address', 'S9RequestorNameBodyAddress'];
        yield ['safety_insp_morefreq', 'SafetyInspMorefreq'];
        yield ['SafetyAddresses', 'SafetyAddresses'];
        yield ['SECTION1_1', 'Section11'];
        yield ['SECTION1_2', 'Section12'];
        yield ['SECTION2_1', 'Section21'];
        yield ['SECTION2_10', 'Section210'];
        yield ['SECTION2_2', 'Section22'];
        yield ['SECTION2_3', 'Section23'];
        yield ['SECTION2_4', 'Section24'];
        yield ['SECTION2_5', 'Section25'];
        yield ['SECTION2_6', 'Section26'];
        yield ['SECTION2_7', 'Section27'];
        yield ['SECTION2_9', 'Section29'];
        yield ['SECTION3_1', 'Section31'];
        yield ['SECTION3_2', 'Section32'];
        yield ['SECTION3_3', 'Section33'];
        yield ['SECTION3_4', 'Section34'];
        yield ['SECTION3_5', 'Section35'];
        yield ['SECTION3_6', 'Section36'];
        yield ['SECTION4_1', 'Section41'];
        yield ['SECTION4_2', 'Section42'];
        yield ['SECTION5_1', 'Section51'];
        yield ['SECTION5_2', 'Section52'];
        yield ['SECTION5_3', 'Section53'];
        yield ['SECTION5_4', 'Section54'];
        yield ['SECTION6_1', 'Section61'];
        yield ['SECTION7_1', 'Section71'];
        yield ['SECTION7_2', 'Section72'];
        yield ['SECTION8_1', 'Section81'];
        yield ['SERIAL_NO_PREFIX', 'SerialNoPrefix'];
        yield ['SERIAL_NUM', 'SerialNum'];
        yield ['SPECIFIC_LICENCE_CONDITIONS', 'SpecificLicenceConditions'];
        yield ['SPECIFIC_LICENCE_UNDERTAKINGS', 'SpecificLicenceUndertakings'];
        yield ['subject_address', 'SubjectAddress'];
        yield ['subject_operating_centre_address', 'SubjectOperatingCentreAddress'];
        yield ['TA_ADD1', 'TaAdd1'];
        yield ['TA_ADDRESS', 'TaAddress'];
        yield ['TA_ADDRESS_PHONE', 'TaAddressPhone'];
        yield ['TA_NAME', 'TaName'];
        yield ['TA_Name', 'TaName'];
        yield ['TA_NAME_UPPERCASE', 'TaNameUppercase'];
        yield ['TAAddress_1', 'TaAddress1'];
        yield ['TAAddress_2', 'TaAddress2'];
        yield ['tachograph_details', 'TachographDetails'];
        yield ['TAName', 'TaName'];
        yield ['TC_SIGNATURE', 'TcSignature'];
        yield ['TM_ADDRESS', 'TmAddress'];
        yield ['tm_id', 'TmId'];
        yield ['TM_NAME', 'TmName'];
        yield ['today_date_sentence', 'TodayDateSentence'];
        yield ['TODAYS_DATE', 'TodaysDate'];
        yield ['todays_date', 'TodaysDate'];
        yield ['TotalContFee', 'TotalContFee'];
        yield ['TRADING_NAME', 'TradingName'];
        yield ['Trading_Names', 'TradingNames'];
        yield ['TRAILERS', 'Trailers'];
        yield ['Transport_Managers', 'TransportManagers'];
        yield ['Two_Weeks_Before', 'TwoWeeksBefore'];
        yield ['UNDERTAKINGS', 'Undertakings'];
        yield ['UNLINKED_TM', 'UnlinkedTm'];
        yield ['UserKnownAs', 'UserKnownAs'];
        yield ['VALID_DATE', 'ValidDate'];
        yield ['VEHICLE_ROW', 'VehicleRow'];
        yield ['VEHICLES', 'Vehicles'];
        yield ['Vehicles_Specified', 'VehiclesSpecified'];
        yield ['warning_re_early_operating', 'WarningReEarlyOperating'];
        yield ['warning_re_early_operating_NI', 'WarningReEarlyOperatingNi'];
    }
}
