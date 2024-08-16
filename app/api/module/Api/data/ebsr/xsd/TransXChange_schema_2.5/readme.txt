Release 2.5a TransXChange  
(c) 2013 CROWN COPYRIGHT

2.5a is an upgrade to TransXChange that supercedes 2.4

    It shares common NaPT data types with the NaPTAN & NPTG 2.5 release.

    It is accompanied by a set of  example documents available at www.transxchange.org.uk.
        - The examples are intended to demonstrate the use of all the main TXC features and every possible route topology.
          An index page shows which features are used one each example.
        - Each example is explained by a web page with timetable and route maps.
        - Some further examples covering partial exchange of data are planned.
    
    A revised version of the TransXChangeSchema Guide is available online at http://www.transxchange.org.uk which includes significant revisions.
    A revised version of the TransXChange Examples are available online at http://www.transxchange.org.uk with revisions coverign the new features.
    

NB: 2.5a is a a draft release  
     
 
TO DO
    metadata dnf, layer number, baseline version 

Changes 
2013-09-05 V2.5b Correct spellings in Schema guide

2013-05-15 V2.5b
         - Other - JW alignment - add submode to ServicePart, use grousp to clarify structure
         - Add ServiceFacilitySet with details   
         - PTIC086 - revise to show  netex correspodences.
         - NB Change order of VehicleEquipment

2013-03-30 V2.5a
         - PTIC086- Accessibility Update to use Naptan Version v2.5
         - PTIC087 - Enhance Accessibilty capabilities
                 Add AssistanceServce to Operational group and to Service
                 Extend VehicleAccessEquipment with more accessibility  attributes: hoist, guidedog, availability
                 Add AssistanceBooking to Operator element
         - Other  Make version number variable

20010-12-30 V2.4
         - Update to use  Naptan Version no v2.4
20010-08-09 V2.4b
         PTIC-001 Move EBSR agent to registration from operator  :  txc_operator : linear
         PTIC-013 Add marketing name & Line description by direction to Line : txc_service, txc_service_journeyGrouping : grouping+
         PTIC-029 Add Wheelchair accessible flag to service and to vehicle type
         PTIC-077 Add data source to document
         PTIC-011 Revise Post midnight Temporal Revvise to se groupas: txc_vehicleJourney  : circular
   
20010-04-04 V2.4a
        PTIC-042 Add Marketing Name to Line as well  . : txc_service_parts : linear
        PTIC-013 Add LineDescription: Origin, Destination, Vias, Description for each direction
20010-02-01 V2.4a
    - Technical internals
          - Modularise the  txc schema by  splitting up TXc common  and additional structures, part of
            long term alignment with Transmodel/ NeTEX and to facilitate maintenance
      NaPTAN alignment    
         PTIC-046  Revise to use modularised NaPTAN  components 
          - Add BusCoachTrolley types aliases for BCS etc  and deprecate BusCoachTram 
      Functional  
         PTIC-001 Add National Operator DB extra elements to Operation :  txc_operator : linear
         PTIC-002 Add Partial Frequency Services : txc_vehicleJourney : lollipop
         PTIC-003 Relax service classification : txc_service : eye
         PTIC-008 NaPTAN SMS short code changes : napt_stop : express
         PTIC-011 Post midnight Temporal Short : txc_vehicleJourney  : circular
         PTIC-012 Explicit grouping of routes/journeys : txc_service, txc_service_journeyGrouping : grouping+
         PTIC-013 Line description by direction  : txc_service, txc_service_journeyGrouping : grouping+
         PTIC-016 Additional business rule validation : TransxChange_registration, TransxChange_general,  : circular
         PTIC-018 Support concise Cancellation  txc_service : cancellation+
         PTIC-022 Support Footnotes & publishing of schools/serviced organisations   txc_servicedOrganisation : linear
         PTIC-027 Multiple operational references per schema : txc_operational : circular
         PTIC-028 Cross referencing workflow attributes : txc_registration : hailAndRide
         PTIC-029 Vehicle Equipment eg Low floor flags : txc_operational, txc_equipment :
         PTIC-031 Permission levels/IPR use   : txc_utiliy_types, napt_versioingATtributes: grouping 
         PTIC-032 Add Dynamic Vias to StopUsage : txc_journeyPatternTimingLink : eye
         PTIC-033 Add recommended end date to operating period :  txc_service & txc_service_parts:  cloverleaf
         PTIC-034 Calendar for Bank Holiday variations : txc_calendar : grouping, circular
         PTIC-035 General Support for School Holidays Schema : txc_calendar, txc_servicedOrganisation: grouping
         PTIC-036 Add minimum layover time to Layover : txc_deadRun : Lollipop 
         PTIC-037 Add DutyCrew code to positioning Link : txc_deadRun : Circular
         PTIC-038 Add CommercialBasis flags to Service, VehcileJourney & VJ Link :  txc_service, txc_journeyPattern, txc_journeyPatternTimingLink : cloverleaf
         PTIC-039 JourneyInterchanges : txc_vehicleJourneyInterchange, txc_vehicleJourney : interchange
         PTIC-040 Add Jan2ndHoliday Displacement : Napt_DayTypes : interchange       
         PTIC-041 Add Parameterized route colours for Lines : txc_route : interchange
         PTIC-042 Add Marketing Name to Service Description. : txc_service_parts : linear
         PTIC-044 Additional Change Management Support  Deltas : various txc_  : delta
         PTIC-O67 Allow ServiceCode to be a string : :napt_versioingAttributes, txc_registratiotxc_service : lollipop
                   Note that & will have to be escaped   eg <ServiceCode>S!&S2</ServiceCode>  
         PTIC-069 Use of NewStops required : texc_service : interchange
         PTIC-070 Use of supporting documents : no change : calendar 
         PTIC-071 National Terms Databset : Napt_DayTypes, txc_calendar : grouping, 
         PTIC-073 Support amended frequency text : txc_frequency : interchange:
         PTIC-074 Flexible support for Authorities;   Support ExeterCity
2009-07-08
    -  Correction CheshireWestAndCheshire to CheshireWestAndChester 
        (add  CheshireWestAndCheshire  deprecated.
2009-06-16
    Circulated Authority Changes
     -  Add CheshireEast  CheshireWestAndCheshire to Circulated Authorities. 
       Cheshire is now deprecated.
 
     - Add Bedford and  CentralBedfordshire to circulated authorities. Bedfordshire is now deprecated
     - Note that package and  schema version numbers have not been incremented

2007 01 02
    Add Extension Elements to

    Txc _common
        Track
        Operator
        LicensedOperator
        Route
        VehicleJourney
        ServicedOrganisation
        Garage

        VehicleJourneyStopUsage
        VehicleJourneyInterchange

        OperatingProfile

        FlexibleService
        StandardService
        JourneyPatternInterchange
        VehicleJourneyTimingLink
        JourneyPatternTimingLink
        Registration

       naPt_stop_2.2a
        StopPoint
        AnnotatedStopPoint
        StopArea
        StopValidity


2007 02 14 
     Add Location to AnnotatedStopRef  (napt_stop, napt_journey, txc_Common)
     
     Revise includes in txc_types to use only napt_geographic
2007 03 07
     Add draft of journey grouping, 