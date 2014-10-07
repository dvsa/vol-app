<?php
return array(
    'sections' => array(
        'submission_section_intr' => array(
        ),
        'submission_section_casu' => array(
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'application' => array(
                        'properties' => array(
                            'targetCompletionDate'
                        )
                    ),
                    'caseType' => array(
                        'properties' => array('id')
                    ),
                    'submissionSections' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )
                    ),
                    'legacyOffences' => array(
                        'properties' => 'ALL',
                    ),
                    'licence' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'status' => array(
                                'properties' => array('id', 'description')
                            ),
                            'licenceType' => array(
                                'properties' => array('id', 'description')
                            ),
                            'goodsOrPsv' => array(
                                'properties' => array('id', 'description')
                            ),
                            'trafficArea' => array(
                                'properties' => 'ALL'
                            ),
                            'licenceVehicles' => array(
                                'properties' => array(
                                    'id',
                                    'specifiedDate',
                                    'deletedDate'
                                )
                            ),
                            'organisation' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'type' => array(
                                        'properties' => array('id', 'description')
                                    ),
                                    'sicCode' => array(
                                            'properties' => array('id', 'description')
                                        ),
                                    )
                            )
                        )
                    )
                )
            )
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
        ),
        'submission_section_pers' => array(
        ),
        'submission_section_opce' => array(
        ),
        'submission_section_ochi' => array(
        ),
        'submission_section_ctud' => array(
        ),
        'submission_section_inuc' => array(
        ),
        'submission_section_intm' => array(
        ),
        'submission_section_advt' => array(
        ),
        'submission_section_llan' => array(
        ),
        'submission_section_alau' => array(
        ),
        'submission_section_ltca' => array(
        ),
        'submission_section_cusu' => array(
        ),
        'submission_section_auth' => array(
        ),
        'submission_section_trma' => array(
        ),
        'submission_section_cnec' => array(
        ),
        'submission_section_fire' => array(
        ),
        'submission_section_preh' => array(
        ),
        'submission_section_brad' => array(
        ),
        'submission_section_trac' => array(
        ),
        'submission_section_tbus' => array(
        ),
        'submission_section_llhi' => array(
        ),
        'submission_section_mlhh' => array(
        ),
        'submission_section_regd' => array(
        ),
        'submission_section_mtdh' => array(
        ),
        'submission_section_proh' => array(
        ),
        'submission_section_cpoh' => array(
        ),
        'submission_section_anth' => array(
        ),
        'submission_section_pens' => array(
        ),
        'submission_section_misc' => array(
        ),
        'submission_section_terp' => array(
        ),
        'submission_section_site' => array(
        ),
        'submission_section_plpm' => array(
        ),
        'submission_section_acom' => array(
        ),
        'submission_section_vaes' => array(
        ),
        'submission_section_comp' => array(
        ),
        'submission_section_envc' => array(
        ),
        'submission_section_reps' => array(
        ),
        'submission_section_objs' => array(
        ),
        'submission_section_fnin' => array(
        ),
        'submission_section_maps' => array(
        ),
        'submission_section_wflf' => array(
        ),
        'submission_section_surr' => array(
        ),
        'submission_section_annx' => array(
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
