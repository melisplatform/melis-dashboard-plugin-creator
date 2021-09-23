<?php

/**
 * Melis Technology (http://www.melistechnology.com]
 *
 * @copyright Copyright (c] 2015 Melis Technology (http://www.melistechnology.com]
 *
 */

return [
    'plugins' => [
        'melisdashboardplugincreator' => [
            'conf' => [
                'id' => '',
                'name' => 'tr_melisdashboardplugincreator_tool_name',
                'rightsDisplay' => 'none',
            ],
            'ressources' => [
                'js' => [
                    '/MelisDashboardPluginCreator/js/dashboard-plugin-creator.js'
                ],
                'css' => [
                    '/MelisDashboardPluginCreator/css/style.css'
                ],
                /**
                 * the "build" configuration compiles all assets into one file to make
                 * lesser requests
                 */
                'build' => [
                    // configuration to override "use_build_assets" configuration, if you want to use the normal assets for this module.
                    //change this to false after testing
                    'disable_bundle' => false,
                    // lists of assets that will be loaded in the layout
                    'css' => [
                        '/MelisDashboardPluginCreator/build/css/bundle.css',
                    ],
                    'js' => [
                        '/MelisDashboardPluginCreator/build/js/bundle.js',
                    ]
                ]
            ],
            'datas' => [
                'steps' => [
                    'melisdashboardplugincreator_step1' => [
                        'name' => 'tr_melisdashboardplugincreator_plugin',
                        'icon' => 'fa-puzzle-piece'
                    ],
                    'melisdashboardplugincreator_step2' => [
                        'name' => 'tr_melisdashboardplugincreator_menu_texts_display',
                        'icon' => 'fa-language'
                    ],
                    'melisdashboardplugincreator_step3' => [
                        'name' => 'tr_melisdashboardplugincreator_dashboard_texts_display',
                        'icon' => 'fa-dashboard'
                    ],
                    'melisdashboardplugincreator_step4' => [
                        'name' => 'tr_melisdashboardplugincreator_summary',
                        'icon' => 'fa-list'
                    ],
                    'melisdashboardplugincreator_step5' => [
                        'name' => 'tr_melisdashboardplugincreator_finalization',
                        'icon' => 'fa-cogs'
                    ],                    
                ],      
                'dashboardTabIcons' => [
                    'charts'  => 'fa-bar-chart-o',
                    'calendar'  => 'fa-calendar',                                     
                    'warning_sign'  => 'fa-warning',
                    'table'  => 'fa-table',
                    'cogwheel' => 'fa-cog',
                    'chat'  => 'fa-comment',           
                    'link'  => 'fa-chain',                                       
                    'google_maps'  => 'fa-map-marker',  
                    'bin' => 'fa-trash-o',
                    'filter' => 'fa-filter',
                    'search' => 'fa-search',
                    'table' => 'fa-table',
                    'tag' => 'fa-tag',
                    'bookmark' => 'fa-bookmark',
                    'group' => 'fa-group',
                    'bell' => 'fa-bell',
                    'clock' => 'fa-clock-o',
                    'wrench' => 'fa-wrench',
                    'ban' => 'fa-ban'
                ]

            ],
        ]
    ]
];



