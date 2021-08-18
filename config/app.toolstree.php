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
                        'meliscustom_toolstree_section' => [
                            'interface' => [
                                'melisdashboardplugincreator_conf' => [
                                    'conf' => [
                                        'id' => 'id_melisdashboardplugincreator_leftnemu',
                                        'melisKey' => 'melisdashboardplugincreator_leftnemu',
                                        'name' => 'tr_melisdashboardplugincreator_title',
                                        'icon' => 'fa fa-puzzle-piece',
                                    ],
                                    'interface' => [
                                        'melisdashboardplugincreator_tool' => [
                                            'conf' => [
                                                'id' => 'id_melisdashboardplugincreator_tool',
                                                'melisKey' => 'melisdashboardplugincreator_tool',
                                                'name' => 'tr_melisdashboardplugincreator_title',
                                                'icon' => 'fa fa-puzzle-piece',
                                            ],
                                            'forward' => [
                                                'module' => 'MelisDashboardPluginCreator',
                                                'controller' => 'List',
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
                                                        'controller' => 'List',
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
                                                        'controller' => 'List',
                                                        'action' => 'render-tool-content',
                                                        'jscallback' => '',
                                                        'jsdatas' => []
                                                    ],
                                                    'interface' => [

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
            ]
        ]
    ]
];