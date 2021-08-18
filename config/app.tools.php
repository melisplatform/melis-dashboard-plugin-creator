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
            'tools' => [
                'melisdashboardplugincreator_tools' => [
                    'conf' => [
                        'title' => 'tr_melisdashboardplugincreator_templates',
                        'id' => 'id_melisdashboardplugincreator_templates',
                    ],
                    'table' => [
                        // table ID
                        'target' => '#tableToolMelisdashboardplugincreator',
                        'ajaxUrl' => '/melis/MelisDashboardPluginCreator/List/getList',
                        'dataFunction' => '',
                        'ajaxCallback' => '',
                        'filters' => [
                            'left' => [
                                'melisdashboardplugincreator-tbl-filter-limit' => [
                                    'module' => 'MelisDashboardPluginCreator',
                                    'controller' => 'List',
                                    'action' => 'render-table-filter-limit',
                                ],
                            ],
                            'center' => [
                                'melisdashboardplugincreator-tbl-filter-search' => [
                                    'module' => 'MelisDashboardPluginCreator',
                                    'controller' => 'List',
                                    'action' => 'render-table-filter-search',
                                ],
                            ],
                            'right' => [
                                'melisdashboardplugincreator-tbl-filter-refresh' => [
                                    'module' => 'MelisDashboardPluginCreator',
                                    'controller' => 'List',
                                    'action' => 'render-table-filter-refresh',
                                ],
                            ],
                        ],
                        'columns' => [

                        ],
                        // define what columns can be used in searching
                        'searchables' => [

                        ],
                        'actionButtons' => [

                        ]
                    ],

                ]
            ]
        ]
    ]
];