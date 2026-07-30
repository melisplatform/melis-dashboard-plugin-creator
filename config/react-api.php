<?php

/**
 * Routes + controleur React API fournis par MelisDashboardPluginCreator.
 *
 * Ces routes s'ajoutent aux child_routes de melis-react-api (le bridge GENERIQUE). Modularite :
 * le controleur / les routes / l'invokable de l'outil vivent dans SON module, pas dans
 * MelisReactApi. Laminas\Stdlib\ArrayUtils::merge() fusionne les configs, via
 * MelisDashboardPluginCreator\Module::getConfig(). L'UI est livree par la brique React du module
 * (public/ui-react), donc l'outil n'apparait dans le BO React que si le module est ACTIF.
 *
 * Toutes les routes sont des litteraux (aucun :id), sauf /dpc/step/:step, contraint a [1-3] et
 * declare apres les litteraux → pas de piege d'ordre de matching.
 *
 * Contrat de reponse : { success: bool, data: T, error?: string }.
 */

$controller = static fn (string $action): array => [
    '__NAMESPACE__' => 'MelisDashboardPluginCreator\Controller',
    'controller'    => 'MelisReactApiDashboardPluginCreator',
    'action'        => $action,
];

return [
    'router' => [
        'routes' => [
            'melis-backoffice' => [
                'child_routes' => [
                    'melis-react-api' => [
                        'child_routes' => [
                            // Contexte statique : preflight (droits FS), meta des 5 etapes,
                            // langues, modules existants, icones (plugin + onglets).
                            'dpc-context' => [
                                'type'    => 'Segment',
                                'options' => ['route' => '/dpc/context[/]', 'defaults' => $controller('context')],
                            ],
                            // Etat courant de l'assistant (conteneur de session) → restaure l'UI au reload.
                            'dpc-state' => [
                                'type'    => 'Segment',
                                'options' => ['route' => '/dpc/state[/]', 'defaults' => $controller('state')],
                            ],
                            // Reinitialise l'assistant (vide la session + la vignette temporaire).
                            'dpc-reset' => [
                                'type'    => 'Segment',
                                'options' => ['route' => '/dpc/reset[/]', 'defaults' => $controller('reset')],
                            ],
                            // Recapitulatif (etape 4), mis en forme pour l'affichage.
                            'dpc-summary' => [
                                'type'    => 'Segment',
                                'options' => ['route' => '/dpc/summary[/]', 'defaults' => $controller('summary')],
                            ],
                            // Vignette du plugin (etape 2). /remove est declare AVANT /thumbnail
                            // pour que le plus specifique gagne le matching.
                            'dpc-thumbnail-remove' => [
                                'type'    => 'Segment',
                                'options' => ['route' => '/dpc/thumbnail/remove[/]', 'defaults' => $controller('thumbnailRemove')],
                            ],
                            'dpc-thumbnail' => [
                                'type'    => 'Segment',
                                'options' => ['route' => '/dpc/thumbnail[/]', 'defaults' => $controller('thumbnail')],
                            ],
                            // GENERATION (etape 5) : ecrit le plugin sur disque + active le module.
                            'dpc-generate' => [
                                'type'    => 'Segment',
                                'options' => ['route' => '/dpc/generate[/]', 'defaults' => $controller('generate')],
                            ],
                            // Validation + persistance d'une etape (1..3). Declare APRES les litteraux.
                            'dpc-step' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/dpc/step/:step',
                                    'constraints' => ['step' => '[1-3]'],
                                    'defaults'    => $controller('step'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'invokables' => [
            'MelisDashboardPluginCreator\Controller\MelisReactApiDashboardPluginCreator'
                => \MelisDashboardPluginCreator\Controller\MelisReactApiDashboardPluginCreatorController::class,
        ],
    ],
];
