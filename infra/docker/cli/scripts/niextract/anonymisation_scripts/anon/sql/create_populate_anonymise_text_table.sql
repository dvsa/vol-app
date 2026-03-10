DROP TABLE IF EXISTS anonymisation_text;
CREATE  TABLE `anonymisation_text` (
  `table_name` varchar(64) NOT NULL,
  `column_name` varchar(64) NOT NULL,
  `id` int(10) unsigned NOT NULL, 
  `text` varchar(8000) NOT NULL,
  PRIMARY KEY (`table_name`,`column_name`,`id`),
  KEY `ix_anonymisation_text_table_name` (`table_name`)
);

INSERT INTO anonymisation_text (`table_name`,`column_name`,`id`,`text`) values

('application','psv_small_vhl_notes',1,'REQUESTED')
,('application','psv_small_vhl_notes',2,'2 small vehicles to be used for home to school transport.')
,('application','psv_small_vhl_notes',3,'The small vehicle will be just one out of a total 19 vehicles and will be used as just a back up vehcile.')
,('application','psv_medium_vhl_notes',1,'Disscussed at Public Inquiry')
,('application','psv_medium_vhl_notes',2,'Driving taxi''s self-employed working 40/50 hours a week.')
,('application','psv_medium_vhl_notes',3,'I intend employing part time drivers to drive the vehicles. I anticipate the vehicles being used for approximately 20 hours per week. I am enployed the details of the employer are below:

I have enclosed wage slip to verify this.')
,('application','interim_reason',1,'We would like to request interim authority as we are in need of an extra vehicle to deal with the current demand on our business.')
,('application','interim_reason',2,'Interim needed')
,('application','interim_reason',3,'I have work available which needs to start as soon as possible or it will be given to another contractor')
,('application','insolvency_details',1,'REPORT ATTACHED..............................')
,('application','insolvency_details',2,'Need further explanationXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX')
,('application','insolvency_details',3,'Administration history for ABC Limited.............................................................................................................................................................................................................................')
,('application','request_inspection_comment',1,'MAINTENANCE INVESTIGATION FOLLOWING GRANT OF NEW APPLICATION')
,('application','request_inspection_comment',2,'Restricted for 2 vehicles only')
,('application','request_inspection_comment',3,'please arrange new op seminar')

,('decision','description',1,'To consider the operator is still of good repute in accordance with Regulation 5 of the Goods Vehicles (Qualifications of Operators) Regulations(Northern Ireland) 2012')
,('decision','description',2,'Application Granted')
,('decision','description',3,'Licence liable to revocation, suspension or curtailment following a direction under section 25 (4) (director/individual disqualified on another licence)')
,('decision','description',4,'External TM exceeds the 4/50 rule (section 12A (3)(c )(ii))')
,('decision','description',5,'Curtail')

,('disqualification','notes',1,'Disqualified indefinitely.')
,('disqualification','notes',2,'Disqualified from holding or obtaining an operator\'s licence until he can satisfy a TC to vary that order')
,('disqualification','notes',3,'Disqualified at PI on 01 November 2028 for a period of 3 years with effect from 2359 hours on 1 December 2028.')
,('disqualification','notes',4,'The operator is disqualified from holding an operators licence until it can satisfy a traffic commissioner to vary that order.')

,('note','comment',1,'Has applied to be added to licence OH12345678  ABC LTD
Has stated he will be working 60 hours letter sent - John Doe 09/09/2028 12:00')
,('note','comment',2,'Submission to TC - John Doe 01/01/2030 14:00')
,('note','comment',3,'OF1234567 
ON EU TM REMOVED LIST - Fred Smith 01/01/2030 14:00')
,('note','comment',4,'will do 20 hrs req - John Doe 01/01/2050 10:00')
,('note','comment',5,'Prohibitions:
--------------------
No. 1: 	Date: 26-APR-2040	Veh ID: A999999 /Trailer	Type: Variation
	Imposed at: 
	Total Points: 5

	Details of Defects
	1.1	Handbrake	handbrake effort low
--------------------------------------------------------------------------------
No. 2: 	Date: 25-JUN-2040	Veh ID: Z999 ZZZ	Type: Delayed
	Imposed at: 
	Total Points: 7
')

,('submission_section_comment','comment',1,'<h3>Hours of Operation:</h3>
<p>
    Monday to Friday:<br />
    Saturday:<br />
    Sunday:<br />
    Bank Holiday:<br />
</p>
<h3>Hours of Maintenance:</h3>
<p>
    Monday to Friday:<br />
    Saturday:<br />
    Sunday:<br />
    Bank Holiday:<br />
</p>
')
,('submission_section_comment','comment',2,'<p>Test comment</p>')
,('submission_section_comment','comment',3,'Driver stopped at roadside, a number of driving offences highlighted')
,('submission_section_comment','comment',4,'<p>The operator has applied for 3 new licences, the named transport manager will be sole named transport manager on 5 licences if granted and he is also a director of the company.</p>')

,('condition_undertaking','notes',1,'RESTRICTED TO MAXIMUM HEIGHT OF 12FEET, 6 INCHES')
,('condition_undertaking','notes',2,'RESTRICTED TO MINIBUS TYPE VEHICLES ONLY')
,('condition_undertaking','notes',3,'THE HOLDER OF THE LICENCE SHALL NOTIFY THE TRAFFIC COMMISSIONER WITHIN 28 DAYS OF AQUIRING A SUITABLE VEHICLE TO BE USED UNDER THE LICENCE AND SHALL PRODUCE THE VEHICLES CERTIFICATE OF INITIAL FITNESS')
,('condition_undertaking','notes',4,'MINIBUS AND SINGLE DECK')
,('condition_undertaking','notes',5,'(a) Authorised vehicles shall enter and leave the operating centre in forward gear. (b) The engines of authorised vehicles will be turned off when the vehicles are not in use at the operating centre, provided that engines may be "warmed up" for not more than 5 minutes before departure. (c) There will be no more than 40 movements in total into or out of the operating centre in the course of a day (defined as midnight to midnight) by the fleet of vehicles authorised under this licence. A movement is defined as either a vehicle leaving the operating centre or a vehicle entering the operating centre. (d) Vehicles authorised under this licence will not be washed at the operating centre at xxxx. e) The operator will ensure that the lineage within the site is maintained to the standards shown in the photographs provided to the Traffic Commissioner and reapplied should it begin to wear off. f) The operator will issue all staff working at the Witley Station operating centre with written instructions advising them of the conditions and undertakings relevant at the operating centre, and ask staff to sign to state that they understand and will abide by the restrictions. Copies of these documents are to be kept by the operator for 6 years and to be made available, on request, to VOSA or the Traffic Commissioner')

,('document','description',1,'GV - New App Refusal - no advert')
,('document','description',2,'GV - New applications - grant/refusal')
,('document','description',3,'Bus Registration: Ask Complainant For Further Details Letter (NI)')
,('document','description',4,'PSV - No application fee')
,('document','description',5,'Letter to Parish Council approved by DTC John Doe ref the Council''s concerns that the operator did not advertise the application to add Old Macdonalds Farm as an o/c in a newpaper that circulated in the vicinity of the o/c')

,('cases','description',1,'TM repute now restored')
,('cases','description',2,'Outcome of PI, TM Lost her Repute.')
,('cases','description',3,'Misuse of licence; operating more vehciles than authorised; drivers hours breaches; failure to notify change of entity; gaps in records; falsified records; serious lack of compliance in relation to operating outside the authority for a considerable amount of time.')
,('cases','prohibition_note',1,'19/08/2028: Anti-lock warning lamp; indicates the existence of a fault')
,('cases','prohibition_note',2,
'I consider that the defects below indicate a significant failure in the maintenance system:
EXTERNAL MANDATORY MIRROR, MISSING, DRIVER AWARE OF DEFECT.
UNABLE TO PRODUCE DEFECT REPORT AS HE HAS NOT COMPLETED ONE
FOR TODAY OR YESTERDAY
The reasons for my conclusions are as follows:
THE DEFECT SHOULD HAVE BEEN DETECTED AT THE FIRST USE/DAILY WALK ROUND CHECK.')
,('cases','prohibition_note',3,'Issued to trailer C123456')

,('cases','penalties_note',1,
'THREE RECORDED 
NO TEST
FAULTY BRAKES ON VEHICLE 
FAULTY BRAKES ON TRAILER')
,('cases','penalties_note',2,'Immediate prohibition notice issued to vehicle on 15/09/30 at the check site. The vehicle did not have a current MOT test certificate. DVSA Data base shows the last MOT certificate issued on 14/04/30 and expired 14/04/30.')

,('cases','conviction_note',1,'RESPONSE ATTACHED')
,('cases','conviction_note',2,'Letter of Explanation requested from Operator 10/01/2027')
,('cases','conviction_note',3,
'Offence to undertake works without a required permit

19.Ã¢â¬â(1) It is an offence for a statutory undertaker or a person contracted to act on its behalf to undertake specified works in a specified street in the absence of a permit, except to the extent that a permit scheme provides that this requirement does not apply.

(2) A person guilty of an offence under this regulation is liable on summary conviction to a fine not exceeding level 5 on the standard scale.'
)
,('cases','ecms_no',1,'1234-0-4321')
,('cases','ecms_no',2,'N/A')

,('contact_details','description',1,'DERBYSHIRE COUNTY COUNCIL')
,('contact_details','description',2,'POLICE')
,('contact_details','fao',1,'TRANSPORT PLANNING SERVICE')
,('contact_details','fao',2,'John Doe')
,('contact_details','fao',3,'FAO MR F SMITH')

,('grace_period','description',1,'Period of grace to appoint new TM')
,('grace_period','description',2,'3 months granted. New entity to agree audit to be undertaken 6-9 months after grant.')
,('grace_period','description',3,'No transport manager.')
,('grace_period','description',4,'Period of grace granted until 26/03/2027')
,('grace_period','description',5,'Period of grace granted as Mr Smith has lost his repute as a transport manager.')

,('impounding','notes',1,'Adjourned at hearing, to be reconvened on 08/02/2027')
,('impounding','notes',2,'Application for return "revoked" by Mr Smith. No attanedance at PI')

,('inspection_request','requestor_notes',1,'APP GRANTED')
,('inspection_request','requestor_notes',2,'2 NEW OPERATING CENTRES AND INCREASE IN AUTHORITY OF 3 VEHICLES')
,('inspection_request','requestor_notes',3,'New operator. Licence granted 11/9/2001')
,('inspection_request','requestor_notes',4,'NEW OPERATING CENTRE NO INCREASE IN VEHICLE AUTHORITYÃÂ¬CLOSURE OF EXISTING OPERATING CENTRE')
,('inspection_request','requestor_notes',5,'INCREASE IN VEH AUTH BY 5ÃÂ¬INCREASE IN TRAILER AUTH BY 11ÃÂ¬NEW OPERATING CENTRE')

,('inspection_request','inspector_notes',1,'UNABLE TO CONTACT OPERATOR, CONTACTED MAINTENANCE CONTRACTOR AND WAS ADVISED THAT ABC PLC HAD RECENTLY CONTACTED THEM ADVISING THAT THE COMPANY WAS BANKRUPT AND UNABLE TO SETTLE THEIR ACOUNT.')
,('inspection_request','inspector_notes',2,'SEE MIG')
,('inspection_request','inspector_notes',3,'vehicle ok but no inspection sheets/forward planner/ defect booksÃÂ¬VE has agreed a 13 week interval')
,('inspection_request','inspector_notes',4,'ADVICE GIVEN NO FURTHER ACTION')
,('inspection_request','inspector_notes',5,'FOLLOW MIG TO UNSAT MIG OF 28/9/50. MARKED ABC ISSUED ON 17/1/50, POOR ANNUAL INITIAL TEST PASS RATE, NOT ALL PMI SHEETS WERE AVAILABLE FOR INSPECTION. A B SMITH 25/01/30.')

,('appeal','comment',1,'Appeal withdrawn')
,('appeal','comment',2,'in the result we are satisfied that the Traffic Commissioner was entittled to reach all his conclutions. The appeal is dismissed and the order of revokation will come into force at 23.59 hours on 22 March 2050')
,('appeal','comment',3,'The period of two weeks'' suspension will start from 23.59 hours on 28 March 2003')

,('irfo_gv_permit','note',1,'Note 1 ...')
,('irfo_gv_permit','note',2,'Note 2 ...')
,('irfo_gv_permit','note',3,'Note 3 ...')

,('irfo_partner','name',1,'VARIOUS')
,('irfo_partner','name',2,'***REGISTERED IN ERROR***')
,('irfo_partner','name',3,'Property Management Services')

,('submission_action','comment',1,'<p>regÂ  31 to be considered at a hearing</p>')
,('submission_action','comment',2,'<p>The applicant did not apply for a trailer authority at the operating centre but requires this as a haulage operator.Â </p>
<p>Recommend grant with the undertaking added.</p>
<p>John Maughan</p>')
,('submission_action','comment',3,'<p>Please send revocation letter</p>')

,('appeal','outline_ground',1,'That 3 year disqulaification be reduced')
,('appeal','outline_ground',2,'DTC wrongly gave decision on day - disparity & inconsistencies in decision

Findings of the DTC gave the appellant the perception of unfair and inconsistent treatment; DTC ambushed the appellant with questions she could not properly answer')
,('appeal','outline_ground',3,'Believes decision was too harsh')

,('bus_short_notice','unforseen_detail',1,'Supported by council')
,('bus_short_notice','unforseen_detail',2,'Award of contract')
,('bus_short_notice','unforseen_detail',3,'DUE TO ILL HEALTH OPERATOR HAS TO SURRENDER LICENCE.')
,('bus_short_notice','timetable_detail',1,'Amended timetable')
,('bus_short_notice','timetable_detail',2,'Timetable and route amended following revised contract specification from TfGM')
,('bus_short_notice','timetable_detail',3,'Journey retimed 5 mins earlier at request of School & Council')
,('bus_short_notice','holiday_detail',1,'Xmas period')
,('bus_short_notice','holiday_detail',2,'Update to bank holiday attachment')
,('bus_short_notice','holiday_detail',3,'Late notification by council')
,('bus_short_notice','trc_detail',1,'Change of terminus due to work being undertaken at bus station')
,('bus_short_notice','trc_detail',2,'Closure of bus station')
,('bus_short_notice','trc_detail',3,'route change to bus station closure')
,('bus_short_notice','police_detail',1,'Road Closure')
,('bus_short_notice','police_detail',2,'Summer timetable extended following change made by TfGM to the Autumn service change date.')
,('bus_short_notice','police_detail',3,'road closure for 11 weeks')
,('bus_short_notice','special_occasion_detail',1,'special event')
,('bus_short_notice','special_occasion_detail',2,'the council charge pupils an annual fee for using the school bus services, if they do not qualify for free school transport')
,('bus_short_notice','special_occasion_detail',3,'Transport people home on New Years Eve')
,('bus_short_notice','connection_detail',1,'Train service')
,('bus_short_notice','connection_detail',2,'To improve reliability and ensure better connections to trains.')
,('bus_short_notice','connection_detail',3,'Service Reference: 
ServiceDescription: Change to timing point from Somewhere Way to Somewhere Road as the stop is being removed by the council')
,('bus_short_notice','not_available_detail',1,'School service only
This route has operated for a number of years previous. However, as a result of the DDA Act coming into force from 1st Jan 2050,')
,('bus_short_notice','not_available_detail',2,'Park & Ride')
,('bus_short_notice','not_available_detail',3,'No provisions were made by the council to take the children home to ABC
Change is necessary to ensure that school children being conveyed on these services are accommodated')

,('community_lic','serial_no',1,'0')
,('community_lic','serial_no',2,'100')
,('community_lic','serial_no',3,'20')

,('inspection_email','message_body',1,'
Hello!

This request has been forwarded to ABC''s VI Office as the Operating
Centre''s postcode (for ABC) should read AB9 and not AB12, which means
it is not our area. Hope this doesn''t cause too much confusion!

Many Thanks

Emma
')
,('irfo_psv_auth','exemption_details',1,'NOT APPLICABLE')
,('irfo_psv_auth','exemption_details',2,'The fee is unable to be entered as the fee covers both Fred and his partner, but we are unable to match the partners fee. So it has been set as a misc fee.')
,('irfo_psv_auth','service_route_from',1,'LONDON')
,('irfo_psv_auth','service_route_from',2,'MANCHESTER')
,('irfo_psv_auth','service_route_to',1,'KRAKOW')
,('irfo_psv_auth','service_route_to',2,'CLERMONT FERRAND')

,('legacy_recommendation','comment',1,'First TANBS Recommendation: Total Recorded Points  = 11
Please see system user notes for details.')
,('legacy_recommendation','comment',2,'see file for previous history')
,('legacy_recommendation','notes',1,'Prohibitions:
--------------------
No. 1: 	Date: 12-OCT-2050	Veh ID: X999NSY	Type: Immediate (S)
	Imposed at: ABCD
	Total Points: 11

	Details of Defects
	1.1	WHEEL JAW NOT SECURE	FIFTH WHEEL JAW EXCESSIVELY WORN OR OUT OF ADJUSTMENT TO THE EXTENT THAT THE TRAILER PIN WAS NOT SECURELY HELD
--------------------------------------------------------------------------------
Prohibitions Grand Total: 11

Grand Total: 11
--------------------------------------------------------------------------------

')
,('legacy_recommendation','notes',2,'Report Details:
--------------------

No. 1	Date Requested : 12/03/2050	Date Returned  : 12/01/2050

	Total Points: 10
	Aspect Details
	1.1	UNSATISFACTORY	NO WRITTEN DRIVER DEFECT REPORTING SYSTEM
	1.2	UNSATISFACTORY	WTA NOT INFORMED OF CHANGE OF OPERATING CENTRE
	1.3	UNSATISFACTORY	WTA NOT INFORMED OF CHANGE OF CONTRACTOR
	1.4	UNSATISFACTORY	EXCESSIVE INTERVALS BETWEEN INSPECTIONS
	1.5	UNSATISFACTORY	INADEQUATE INSPECTION SHEETS
	1.6	UNSATISFACTORY	POOR VEHICLE CONDITION

Report Grand Total: 10
--------------------------------------------------------------------------------

Grand Total: 10
--------------------------------------------------------------------------------

')

,('licence','tachograph_ins_name',1,'Tachomaster')
,('licence','tachograph_ins_name',2,'Road Haulage Association')

,('opposition','notes',1,'PLANNING PERM OBJ INVALID DOES NOT COME UNDER JURISDICTION OF THE TC')
,('opposition','notes',2,'ENVIRONMENTAL')
,('opposition','valid_notes',1,'Highway safety concerns, noise, fumes, smell and other disturbances to the nearby residents.')
,('opposition','valid_notes',2,'Holding Objection.')

,('other_licence','holder_name',1,'ABC DEF INTERNATIONAL TRANSPORT LTD')
,('other_licence','holder_name',2,'ABC4SECURITY LTD')
,('other_licence','disqualification_length',1,'12 months')
,('other_licence','disqualification_length',2,'10 years')
,('other_licence','additional_information',1,'additional info')
,('other_licence','operating_centres',1,'Leeds')
,('other_licence','operating_centres',2,'All operating centres')

,('pi','comment',1,'Operator was heard at preliminary hearing on 7th November 2050.  The Traffic Commissioner''s decision was that the operator should be called to a Public Inquiry but that it should not be heard until the end of February 2050, as the call-up letter should be sent out in the second half of January 2050.  This is to allow the operator to have an audit carried out and submitted to this office.')
,('pi','comment',2,'material change of entity and finances not produced in company name')
,('pi','decision_notes',1,'Article 6 of Regulation (EC) No 1071/2009')
,('pi','decision_notes',2,'Repute as Transport Manager lost, Indefinite disqualification.')

,('pi_hearing','cancelled_reason',1,'Cancelled due to length of time from submission and that Mr Smith''s own licence was surrndered and not revoked.')
,('pi_hearing','cancelled_reason',2,'Application withdrawn.')
,('pi_hearing','adjourned_reason',1,'Operator on holiday.')
,('pi_hearing','adjourned_reason',2,'Adjourned part heard, variation required to be complete but reps against it')
,('pi_hearing','details',1,'TM called to Public Inquiry to consider good repute and professional competence under Article 6 of Regulation (EC) No 1071/2009')
,('pi_hearing','details',2,
'GV - S26
S26 - Consideration of disciplinary action under Section 26

GV - S27
S27 - Consideration of disciplinary action under Section 27

GV - S28
S28 - Consideration of disciplinary action under Section 28

GV - Sch.3
Sch.3 - Consideration of Transport Managers Repute under Schedule 3


')

,('pi_hearing','imposed_at',1,'Op centre')
,('pi_hearing','imposed_at',2,'Leeds GVTS during annual test')
  
,('prohibition_defect','notes',1,'FIFTH WHEEL JAW EXCESSIVELY WORN OR OUT OF ADJUSTMENT TO THE EXTENT THAT THE TRAILER PIN WAS NOT SECURELY HELD')
,('prohibition_defect','notes',2,'Warning gauge not functioning, only 1 such device fitted.')

,('propose_to_revoke','comment',1,'Licence revoked')
,('propose_to_revoke','comment',2,'Operator using his restricted licence to carry goods for a third party.  In response to the PTR letters the operator has returned his licence docs and disc.  Tc has directed that the licence is therefore revoked.')

,('recipient','contact_name',1,'Company Secretary ABC XYZ Ltd')
,('recipient','contact_name',2,'Mr Blonde')

,('serious_infringement','reason',1,'It is disproportionate to forfeit repute in the light of brake and tyre prohibitions and the use of a trailer without an MOT because of the purposeful steps since taken, the undertakings now offered and the personal circumstances then applying.')
,('serious_infringement','reason',2,'PI now agreed, NEOTC to deal')

,('statement','authorisers_decision',1,'Vehicle is specified under this operators licence.')
,('statement','authorisers_decision',2,'Driver does not have a licence issued by the Traffic Commissioner in this area.')

,('stay','notes',1,'STAY GRANTED')
,('stay','notes',2,'In view of the fact that this matter is unlikely to be heard by the Transport Tribunal before 18 April 2050.')

,('submission','data_snapshot',1,'{"case-summary":{"data":{"overview":{"id":999999,"caseType":"Licence","ecmsNo":null,"organisationName":"TEST USER (SELF SERVICE) (12345) PLEASE IGNORE","isMlh":false,"organisationType":"Limited Company","businessType":null,"disqualificationStatus":"None","licNo":"PB123456","licenceStartDate":"09\/03\/2050","licenceType":"Standard International","goodsOrPsv":"Public Service Vehicle","licenceStatus":"Valid","totAuthorisedVehicles":5,"totAuthorisedTrailers":0,"vehiclesInPossession":4,"trailersInPossession":0,"serviceStandardDate":""}}},"case-outline":{"data":{"text":null}},"outstanding-applications":{"data":{"tables":{"outstanding-applications":[{"id":1000000,"licNo":"PB9999999","version":1,"applicationType":"Variation","receivedDate":"12\/11\/2050","oor":"Not applicable","ooo":"Not applicable"}]}}},"people":{"data":{"tables":{"people":{"999999":{"id":000000,"title":"","familyName":"TESTDIR (12345)","forename":"TEST","disqualificationStatus":"None","birthDate":"01\/01\/2050"}}}}},"operating-centres":{"data":{"tables":{"operating-centres":[{"id":366267,"version":1,"totAuthVehicles":5,"totAuthTrailers":0,"OcAddress":{"addressLine1":"VOSA","addressLine2":"386 HAREHILLS LANE","addressLine3":null,"addressLine4":null,"town":"LEEDS","postcode":"LS9 6NF","countryCode":"GB"}}]}}},"conditions-and-undertakings":{"data":{"tables":{"undertakings":[],"conditions":[]}}},"intelligence-unit-check":{"data":[]},"interim":{"data":[]},"advertisement":{"data":[]},"current-submissions":{"data":[]},"auth-requested-applied-for":{"data":{"tables":{"auth-requested-applied-for":[{"id":1000000,"version":1,"currentVehiclesInPossession":4,"currentTrailersInPossession":"0","currentVehicleAuthorisation":5,"currentTrailerAuthorisation":"0","requestedVehicleAuthorisation":5,"requestedTrailerAuthorisation":"0"}]}}},"transport-managers":{"data":{"tables":{"transport-managers":[]}}},"continuous-effective-control":{"data":[]},"fitness-and-repute":{"data":[]},"previous-history":{"data":[]},"financial-information":{"data":[]},"bus-reg-app-details":{"data":[]},"total-bus-registrations":{"data":[]},"registration-details":{"data":[]},"local-licence-history":{"data":[]},"maintenance-tachographs-hours":{"data":[]},"annual-test-history":{"data":{"text":null}},"statements":{"data":{"tables":{"statements":[]}}},"compliance-complaints":{"data":{"tables":{"compliance-complaints":[]}}},"oppositions":{"data":{"tables":{"oppositions":[]}}},"applicants-responses":{"data":{"text":"<h3>Hours of Operation:<\/h3>\n<p>\n    Monday to Friday:<br \/>\n    Saturday:<br \/>\n    Sunday:<br \/>\n    Bank Holiday:<br \/>\n<\/p>\n<h3>Hours of Maintenance:<\/h3>\n<p>\n    Monday to Friday:<br \/>\n    Saturday:<br \/>\n    Sunday:<br \/>\n    Bank Holiday:<br \/>\n<\/p>\n"}},"other-issues":{"data":[]},"annex":{"data":[]}}')

,('tm_case_decision','repute_not_lost_reason',1,'In making the decision Mr X stated that although Mr John Doe had been issued with a penalty for an MSI this was a single event in an otherwise clear history. There was an explanation given which I have no reason not to accept. Therefore disqualification would be disproportionate and repute remains intact.')
,('tm_case_decision','repute_not_lost_reason',2,'Taking into account the previous good record of John Doe and the steps taken since the wheel nut issue and clear period since May 2025,despite the MSI, the DTC regarded loss of repute as diproportionate.')
,('tm_case_decision','no_further_action_reason',1,'Documentation provided demonstrates good systems in place, Mr Doe is properly engaged with the business and all statuatory requirements are met.')
,('tm_case_decision','no_further_action_reason',2,'TC accepted the surrender of the licence OK9999999 and decided to take no futher action against TM Mr Smith')

,('tm_employment','position',1,'ELECTRICIAN')
,('tm_employment','position',2,'DRIVING INSTRUCTOR/EXAMINER')
,('tm_employment','employer_name',1,'SELF EMPLOYED')
,('tm_employment','employer_name',2,'ABC TRANSPORT COMPLIANCE SERVICES LTD')

,('transport_manager_application','additional_information',1,'I have been an owner driver for the last 25 yrs and a successful Transport manager for longer than 8 yrs.')
,('transport_manager_application','additional_information',2,'17 Big Brother House ,Empire Way')

,('transport_manager_licence','additional_information',1,'2 Transport Mangers looking after both Licences, Splitting times to cover full time operation')
,('transport_manager_licence','additional_information',2,'Operator Licence OF9999999 will be surrendered when the new Limited Company Operator Licence has been issued.  I will therefore no longer be the Transport Manager on this licence.')

,('txn','payer_name',1,'ABC ZXY COMMERCIALS (YORKSHIRE) LTD')
,('txn','payer_name',2,'ZZZ TEMPS LTD')
,('txn','comment',1,'Route amended due to road closure on registered route')
,('txn','comment',2,'Company have already paid fee as part of a variation application on licence OK9999999.')

,('legacy_offence','notes',1,'SPREAD OVER 9 DRIVERS')
,('legacy_offence','notes',2,'SEE FILE COULD BE THAT THIS OPERATOR HAS NOT COMMITTED AN OFFENCE AND THAT SOMEONE IS USING THE REG NOS AND THAT THE 9 HAS BEEN WORN AWAY TO LOOK LIKE A 3 - COULD BE A FALSE REG NOS')
,('legacy_offence','offence_authority',1,'ESSEX POLICE')
,('legacy_offence','offence_authority',2,'Vehicle Inspectorate')
,('legacy_offence','offender_name',1,'ABC Storage Ltd')
,('legacy_offence','offender_name',2,'J DOE')

,('phone_contact','details',1,'phone contact details ......')

,('bus_reg','via',1,'SANDGATE')
,('bus_reg','via',2,'SOUTHBOROUGH, SPELDHURST, GROOMBRIDGE')
,('bus_reg','via',3,'EGHAM, POOLEY GREEN')
,('bus_reg','via',4,'FOUR ELMS, PENSHURST, LANGTON, SOUTHBOROUGH')
,('bus_reg','via',5,'NEWENDEN & ROLVENDEN LAYNE')
,('bus_reg','via',6,'UNIVERSITY, WEST STATION, EAST STATION, COACH PARK, ST AUGUSTINES ABBEY, BUS STATION')
,('bus_reg','via',7,'CHERTSEY, ADDLESTONE AND WEST BYFLEET')
,('bus_reg','via',8,'CAMBERLEY, BAGSHORT & SUNNINGDALE')
,('bus_reg','via',9,'LALEHAM')
,('bus_reg','via',10,'ENGLEFIELD GREEN & EGHAM')
,('bus_reg','other_details',1,'MONDAY - SATURDAY APPROX. HOURLY 0900 - 1600')
,('bus_reg','other_details',2,'MONDAY-FRIDAY SCHOOLDAYS')
,('bus_reg','other_details',3,'0751 FROM HARTFIELD')
,('bus_reg','other_details',4,'1550 FROM TUNBRIDGE WELLS')
,('bus_reg','other_details',5,'SCHOOLDAYS 0740 HOURS FROM LINCOLN AND 1555 HOURS FROM GAINSBOROUGH CASTLE HILLS SCHOOL.
0850 HOURS FROM GAINSBOROUGH BUS STATION TO LINCOLN CITY BUS STATION MONDAY TO SATURDAY')
,('bus_reg','manoeuvre_detail',1,'ENGAYNE AVENUE REVERSE.')
,('bus_reg','manoeuvre_detail',2,'REVERSING AT BURY ST EDMUNDS BUS STATION')
,('bus_reg','manoeuvre_detail',3,'REVERSES AT ST AGNES')
,('bus_reg','manoeuvre_detail',4,'THE VEHICLES MAY OCCASIONALLY MAKE REVERSING OR TURNING MANOUVERS ALONG FLEXIBLE SECTIONS OF ROUTE.  WHERE SAFE TO DO SO.')
,('bus_reg','new_stop_detail',1,'Established unmarked stops')
,('bus_reg','new_stop_detail',2,'Whitefield Road outside Queen Margaret Hospital')
,('bus_reg','new_stop_detail',3,'SEE PSV350')
,('bus_reg','new_stop_detail',4,'new stop required on Jackson St')
,('bus_reg','not_fixed_stop_detail',1,'VEHICLE WILL STOP WHERE IT IS SAFE TO DO SO.')
,('bus_reg','not_fixed_stop_detail',2,'PART HAIL & RIDE')
,('bus_reg','not_fixed_stop_detail',3,'TO STOP ON REQUEST, WHERE SAFE TO DO SO, AND WHERE HIGHWAY CONDITIONS PERMIT')
,('bus_reg','not_fixed_stop_detail',4,'HAIL & RIDE SERVICE THROUGHOUT')
,('bus_reg','subsidy_detail',1,'Surrey County Council')
,('bus_reg','subsidy_detail',2,'Herts County Council')
,('bus_reg','subsidy_detail',3,'CAMBS CC')
,('bus_reg','route_description',1,'Luton, Manchester Street to Luton, Manchester Street')
,('bus_reg','route_description',2,'Beeston')
,('bus_reg','route_description',3,'middlesbrough BS Stockton High St Sedgefield Durham  framwellgate moor')
,('bus_reg','stopping_arrangements',1,'MARINE PARADE, WESTON S MARE, ANY POINT WITHIN A THREE MILE RADIUS OF MARINE PARADE')
,('bus_reg','stopping_arrangements',2,'Warwick Castle and Kenilworth Castle')
,('bus_reg','stopping_arrangements',3,'AS ROUTE DESRIPTION')
,('bus_reg','trc_notes',1,'Road closure orders received from Coventry City Council')
,('bus_reg','trc_notes',2,'If 1 Jan falls on Sat or Sun, 3 Jan will be Sat service
If 1 Jan falls on Sat, 4 Jan wll be Sat service')
,('bus_reg','trc_notes',3,'Road traffic order closing certain roads du to Coventry city centre rebuilding')
,('bus_reg','reason_cancelled',1,'Ebsr DR')
,('bus_reg','reason_cancelled',2,'DATA MIGRATION ERROR.')
,('bus_reg','reason_refused',1,'APPLICATION REGISTERED ON WRONG FILE')
,('bus_reg','reason_refused',2,'ADMIN ERROR')
,('bus_reg','reason_sn_refused',1,'DOES NOT MEET 56 DAY CRITERIA REQUIRED AS LAID DOWN BY LEGISLATION.')
,('bus_reg','reason_sn_refused',2,'TC DECISION')
,('bus_reg','quality_partnership_details',1,'Medway Council (PIP scheme)')
,('bus_reg','quality_partnership_details',2,'Bristol City Council')
,('bus_reg','quality_contract_details',1,'Hampshire County Council')
,('bus_reg','quality_contract_details',2,'Bristol City Council')
,('continuation_detail','other_finances_details',1,'Details of other finances')
,('continuation_detail','other_finances_details',2,'Other finances details')

;
