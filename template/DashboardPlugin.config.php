<?php 
return [
    'plugins' => [
        'meliscore' => [
            'interface' => [
                'melis_dashboardplugin' => [
                    'interface' => [
                        'melisdashboardplugin_section' => [
                            'interface' => [
                                'ModuleTplPluginNamePlugin' => [
                                    'conf' => [
                                        'type' => '/moduletpl/interface/ModuleTplPluginNamePlugin'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
        'moduletpl' => [
            'ressources' => [
                'css' => [
                     '/ModuleTpl/dashboard-plugin/css/PluginName.css' 
                ],
                'js' => [
                    '/ModuleTpl/dashboard-plugin/js/PluginName.js'                    
                ]
            ],
            'interface' => [
                'ModuleTplPluginNamePlugin' => [
                    'conf' => [
                        'name' => 'ModuleTplPluginNamePlugin',
                        'melisKey' => 'ModuleTplPluginNamePlugin'
                    ],
                    'datas' => [
                        'plugin_id' => 'PluginName',
                        'name' => 'tr_moduletpl_dashboard_pluginName_menu title',
                        'description' => 'tr_moduletpl_dashboard_pluginName_menu description',
                        'dashboard_title' => 'tr_moduletpl_dashboard_pluginName title',
                        'icon' => 'fa PluginIcon',
                        'thumbnail' => '/ModuleTpl/dashboard-plugin/images/PluginThumbnail',
                        'jscallback' => 'PluginName_init()',
                        'height' => 4,
                        'width' => 6,
                        'x-axis' => 0,
                        'y-axis' => 0,
                        'section' => 'Custom',
                    ],
                    'forward' => [
                        'module' => 'ModuleTpl',
                        'plugin' => 'ModuleTplPluginNamePlugin',
                        'function' => 'pluginName',
                    ]
                ]
            ]
        ]
    ]
];