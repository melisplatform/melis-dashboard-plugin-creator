<?php

/**
 * Melis Technology (http://www.melistechnology.com]
 *
 * @copyright Copyright (c] 2015 Melis Technology (http://www.melistechnology.com]
 *
 */

return [
    'plugins' => [
        'meliscore' => [
            'interface' => [
                'meliscore_leftmenu' => [
                    'interface' => [
                        'meliscore_toolstree_section' => [
                            'interface' => [
                                   'meliscore_tool_creatrion_designs' => [
                                    'conf' => [
                                        'id' => 'id_meliscore_tool_creatrion_designs',
                                        'melisKey' => 'meliscore_tool_creatrion_designs',
                                        'name' => 'tr_meliscore_tool_creatrion_designs',
                                        'icon' => 'fa fa-paint-brush',
                                    ],
                                    'interface' => [
                                        'meliscore_tool_tools' => [
                                            'conf' => [
                                                'id' => 'id_meliscore_tool_tools',
                                                'melisKey' => 'meliscore_tool_tools',
                                                'name' => 'tr_meliscore_tool_tools',
                                                'icon' => 'fa fa-magic',
                                            ],
                                            'interface' => [
                                                'melisdashboardplugincreator_conf' => [
                                                    'conf' => [
                                                        'type' => '/melisdashboardplugincreator/interface/melisdashboardplugincreator_tool',
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ]
                                ]                               
                            ]
                        ]
                    ]
                ]
            ]
        ],

        'melisdashboardplugincreator' => [
            'interface' => [
                'melisdashboardplugincreator_tool' => [
                    'conf' => [
                        'id' => 'id_melisdashboardplugincreator_tool',
                        'melisKey' => 'melisdashboardplugincreator_tool',
                        'name' => 'tr_melisdashboardplugincreator_title',
                        'icon' => 'fa fa-puzzle-piece',
                        'follow_regular_rendering' => false,
                    ],
                    'forward' => [
                        'module' => 'MelisDashboardPluginCreator',
                        'controller' => 'DashboardPluginCreator',
                        'action' => 'render-tool',
                        'jscallback' => '',
                        'jsdatas' => []
                    ],
                    'interface' => [
                        'melisdashboardplugincreator_header' => [
                            'conf' => [
                                'id' => 'id_melisdashboardplugincreator_header',
                                'melisKey' => 'melisdashboardplugincreator_header',
                                'name' => 'tr_melisdashboardplugincreator_header',
                            ],
                            'forward' => [
                                'module' => 'MelisDashboardPluginCreator',
                                'controller' => 'DashboardPluginCreator',
                                'action' => 'render-tool-header',
                                'jscallback' => '',
                                'jsdatas' => []
                            ],
                        ],
                        'melisdashboardplugincreator_content' => [
                            'conf' => [
                                'id' => 'id_melisdashboardplugincreator_content',
                                'melisKey' => 'melisdashboardplugincreator_content',
                                'name' => 'tr_melisdashboardplugincreator_content',
                            ],
                            'forward' => [
                                'module' => 'MelisDashboardPluginCreator',
                                'controller' => 'DashboardPluginCreator',
                                'action' => 'render-tool-content',
                                'jscallback' => '',
                                'jsdatas' => []
                            ],
                            'interface' => [
                                'melisdashboardplugincreator_steps' => [ 
                                    'conf' => [
                                        'id' => 'id_melisdashboardplugincreator_steps',
                                        'melisKey' => 'melisdashboardplugincreator_steps',
                                        'name' => 'tr_melisdashboardplugincreator_steps',
                                    ],
                                    'forward' => [
                                        'module' => 'MelisDashboardPluginCreator',
                                        'controller' => 'DashboardPluginCreator',
                                        'action' => 'render-dashboard-plugin-creator-steps',
                                        'jscallback' => '',
                                        'jsdatas' => []
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];