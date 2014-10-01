<?php

return array(
    'label' => 'Home',
    'route' => 'dashboard',
    'use_route_match' => true,
    'pages' => array(
        array(
            'id' => 'licence',
            'label' => 'Licence',
            'route' => 'licence',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'licence_overview',
                    'label' => 'selfserve-licence-breadcrumb',
                    'route' => 'licence/overview',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'licence_licence_type',
                            'label' => 'selfserve-licence-licence_type',
                            'route' => 'licence/licence_type',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_your_business',
                            'label' => 'selfserve-licence-your_business',
                            'route' => 'licence/your_business',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_operating_centres',
                            'label' => 'selfserve-licence-operating_centres',
                            'route' => 'licence/operating_centres',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'id' => 'licence_operating_centres_authorisation',
                                    'label' => 'selfserve-licence-operating_centres_authorisation',
                                    'route' => 'licence/operating_centres/authorisation',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_operating_centres_financial',
                                    'label' => 'selfserve-licence-operating_centres_financial',
                                    'route' => 'licence/operating_centres/financial',
                                    'use_route_match' => true
                                )
                            )
                        ),
                        array(
                            'id' => 'licence_transport_managers',
                            'label' => 'selfserve-licence-transport_managers',
                            'route' => 'licence/transport_managers',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_vehicle_safety',
                            'label' => 'selfserve-licence-vehicle_safety',
                            'route' => 'licence/vehicles_safety',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_previous_history',
                            'label' => 'selfserve-licence-previous_history',
                            'route' => 'licence/previous_history',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_review',
                            'label' => 'selfserve-licence-review',
                            'route' => 'licence/review',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_pay',
                            'label' => 'selfserve-licence-pay',
                            'route' => 'licence/pay',
                            'use_route_match' => true
                        )
                    )
                )
            )
        )
    )
);
