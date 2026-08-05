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
                    'ban' => 'fa-ban',
                    'share' => 'fa-share',
                    'file' => 'fa-file',
                    'list' => 'fa-list',
                    'heart' => 'fa-heart',
                    'inbox' => 'fa-inbox',
                    'envelope' => 'fa-envelope'
                ],              
                /**
                 * Trace SVG de chaque icone d'onglet, a l'IDENTIQUE du selecteur de l'assistant
                 * (ui-react/src/icons.tsx, table ICONS) : c'est ce trace-la qui est ecrit dans la
                 * vue generee, pour que l'onglet du plugin dessine exactement l'icone choisie.
                 *
                 * Une police d'icones (FontAwesome, Glyphicons) ne peut pas donner ce resultat :
                 * ses glyphes sont pleins la ou le selecteur montre un trait. On sort donc du
                 * vocabulaire "classe CSS" pour ecrire le dessin lui-meme.
                 *
                 * ⚠️ Les cles sont celles de `dashboardTabIcons` ci-dessus, et les traces doivent
                 *    rester synchrones avec ICONS de icons.tsx : c'est le selecteur qui fait foi.
                 *    Rendu dans un viewBox 24, fill:none, stroke:currentColor, stroke-width:2.
                 */
                'dashboardTabIconSvg' => [
                    'charts' => '<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>',
                    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
                    'warning_sign' => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
                    'table' => '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/>',
                    'cogwheel' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
                    'chat' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
                    'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
                    'google_maps' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
                    'bin' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
                    'filter' => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
                    'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
                    'tag' => '<path d="M20.59 13.41 13.42 20.59a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
                    'bookmark' => '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
                    'group' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                    'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
                    'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                    'wrench' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
                    'ban' => '<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>',
                    'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
                    'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
                    'list' => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
                    'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
                    'inbox' => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
                    'envelope' => '<rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,6 12,13 2,6"/>',
                ],
                'plugin_thumbnail' => [
                    'min_size' => 1,
                    'max_size' => '512000',
                    'path' => $_SERVER['DOCUMENT_ROOT'].'/dpc/temp-thumbnail/'
                ],
                 //ref: https://www.php.net/manual/en/reserved.keywords.php
                'reserved_keywords' => array('__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor'), 
            ],
        ]
    ]
];



