<?php

/**
 * Melis Technology (http://www.melistechnology.com]
 *
 * @copyright Copyright (c] 2015 Melis Technology (http://www.melistechnology.com]
 *
 */

return [
    'router' => [
        'routes' => [
        	'melis-backoffice' => [
                'child_routes' => [
                    'application-MelisDashboardPluginCreator' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => 'MelisDashboardPluginCreator',
                            'defaults' => [
                                '__NAMESPACE__' => 'MelisDashboardPluginCreator\Controller',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'default' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:controller[/:action]]',
                                    'constraints' => [
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ],
                                    'defaults' => [
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
        	],
        ],
    ],
   'service_manager' => [
        'aliases' => [
            // Service
            'MelisDashboardPluginCreatorService' => \MelisDashboardPluginCreator\Service\MelisDashboardPluginCreatorService::class,           
        ]
    ],

    'controllers' => [
        'invokables' => [
            'MelisDashboardPluginCreator\Controller\DashboardPluginCreator' => \MelisDashboardPluginCreator\Controller\DashboardPluginCreatorController::class,
        ],
    ],
     'form_elements' => [
        'factories' => [      
            'MelisDashboardPluginCreatorModuleSelect' => \MelisDashboardPluginCreator\Form\Factory\MelisDashboardPluginCreatorModuleSelectFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'melis-dashboard-plugin-creator/render-form'  => __DIR__ . '/../view/melis-dashboard-plugin-creator/dashboard-plugin-creator/render-form.phtml',           
            'melis-dashboard-plugin-creator/partial/render-step1'  => __DIR__ . '/../view/melis-dashboard-plugin-creator/dashboard-plugin-creator/partial/render-step1.phtml',
            'melis-dashboard-plugin-creator/partial/render-step2'  => __DIR__ . '/../view/melis-dashboard-plugin-creator/dashboard-plugin-creator/partial/render-step2.phtml',
            'melis-dashboard-plugin-creator/partial/render-step3'  => __DIR__ . '/../view/melis-dashboard-plugin-creator/dashboard-plugin-creator/partial/render-step3.phtml',
            'melis-dashboard-plugin-creator/partial/render-step4'  => __DIR__ . '/../view/melis-dashboard-plugin-creator/dashboard-plugin-creator/partial/render-step4.phtml',
            'melis-dashboard-plugin-creator/partial/render-step5'  => __DIR__ . '/../view/melis-dashboard-plugin-creator/dashboard-plugin-creator/partial/render-step5.phtml',
            'melis-dashboard-plugin-creator/render-step5-finalization'  => __DIR__ . '/../view/melis-dashboard-plugin-creator/dashboard-plugin-creator/render-step5-finalization.phtml'
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
