<?php
return array(
    'sections' => array(
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_casu' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_case' => array(
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'submissionSections' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )
                    ),
                    'legacyOffences' => array(
                        'properties' => 'ALL',
                    ),
                    'caseType' => array(
                        'properties' => 'id',
                    ),
                    'licence' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'status' => array(
                                'properties' => array('id')
                            ),
                            'licenceType' => array(
                                'properties' => array('id')
                            ),
                            'goodsOrPsv' => array(
                                'properties' => array('id')
                            ),
                            'trafficArea' => array(
                                'properties' => 'ALL'
                            ),
                            'organisation' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'type' => array(
                                        'properties' => array('id')
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        'submission_section_msin' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_pers' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_opce' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_ochi' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_ctud' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_inuc' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intm' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_advt' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_llan' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_alau' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_ltca' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_cusu' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_auth' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_trma' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_cnec' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_fire' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_preh' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_brad' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_trac' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_tbus' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_llhi' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_mlhh' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_regd' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_mtdh' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_proh' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_cpoh' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_anth' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_pens' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_misc' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_terp' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_site' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_plpm' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_acom' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_vaes' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_comp' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_envc' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_reps' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_objs' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_fnin' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_maps' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_wflf' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_surr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_annx' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'submission_section_intr' => array(
            'data' => 'submission_section_case',
            'view' => 'blank'
        ),
        'case-summary-info' => array(
            'view' => 'submission/partials/case-summary',
            'dataPath' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'submissionSections' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )
                    ),
                    'convictions' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'category' => array(
                                'properties' => array(
                                    'id',
                                    'description'
                                )
                            )
                        )
                    ),
                    'licence' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'licenceType' => array(
                                'properties' => array('id')
                            ),
                            'trafficArea' => array(
                                'properties' => 'ALL'
                            ),
                            'organisation' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'type' => array(
                                        'properties' => array('id')
                                    ),
                                    'sicCode' => array(
                                        'properties' => array('id')
                                    ),
                                    'organisationPersons' => array(
                                        'properties' => 'ALL',
                                        'children' => array(
                                            'person' => array(
                                                'properties' => 'ALL'
                                            )
                                        )
                                    )
                                )
                            ),
                            'transportManagerLicences' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'transportManager' => array(
                                        'properties' => 'ALL',
                                        'children' => array(
                                            'qualifications' => array(
                                                'properties' => 'ALL'
                                            ),
                                            'contactDetails' => array(
                                                'properties' => 'ALL',
                                                'children' => array(
                                                    'person' => array(
                                                        'properties' => 'ALL'
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        'persons' => array(
            'view' => 'submission/partials/persons'
        ),
        'transport-managers' => array(
            'view' => 'submission/partials/transport-managers',
            'exclude' => array(
                'column' => 'licenceType/id',
                'values' => array(
                    'standard national',
                    'standard international'
                )
            )
        ),
        'outstanding-applications' => null,
        'objections' => null,
        'representations' => null,
        'complaints' => null,
        'environmental' => null,
        'previous-history' => null,
        'operating-centre' => null,
        'conditions' => null,
        'undertakings' => null,
        'annual-test-history' => null,
        'prohibition-history' => null,
        'conviction-history' => array(
            'view' => 'submission/partials/conviction-history',
        ),
        'bus-services-registered' => array(
            'exclude' => array(
                'column' => 'goodsOrPsv/id',
                'values' => array(
                    'psv',
                )
            )
        ),
        'bus-compliance-issues' => array(
            'exclude' => array(
                'column' => 'goodsOrPsv/id',
                'values' => array(
                    'psv',
                )
            )
        ),
        'current-submission' => null
    )
);
