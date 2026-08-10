<?php

/**
 * Capacites d'outils — droits avances du back-office React (module MelisDashboardPluginCreator).
 *
 * Meme convention que melis-templating-plugin-creator : declaration par melisKey, lue par
 * MelisReactApi\Service\Capabilities via la cle de config mergee melisReactToolCapabilities.
 * Pilote l'affichage des cases a cocher dans l'onglet Rights (RightsTreeView) et, cote serveur,
 * la garde denyUnlessCan() de MelisReactApiDashboardPluginCreatorController.
 * Mergee dans MelisDashboardPluginCreator\Module::getConfig().
 *
 * Semantique DEFAULT-ALLOW : absence de donnee = tout permis (un role legacy garde le tout-venant).
 *
 * L'outil est un ASSISTANT en 5 etapes qui GENERE du code PHP dans le depot puis active un module.
 * On decoupe les droits par CAPACITE REELLE, chacune adossee a un endpoint :
 *
 *   wizard            acces au parcours de configuration (etapes 1 → 3)
 *   wizard.edit         └─ enregistrer/valider une etape (sinon : assistant en lecture seule)
 *   thumbnail         acces a la vignette du plugin (etape 2)
 *   thumbnail.create    └─ televerser une vignette
 *   thumbnail.delete    └─ retirer la vignette
 *   summary           acces au recapitulatif (etape 4)
 *   summary.list        └─ lire le recapitulatif
 *   finalization      acces a la finalisation (etape 5)
 *   finalization.create └─ GENERER le plugin : ecrit des fichiers PHP dans module/, reecrit
 *                          module.config.php + Module.php + les fichiers de langue, active le
 *                          module et invalide les caches. C'est la capacite sensible.
 *
 * Les label sont des CLES de traduction Melis (tr_...), resolues dans la locale courante cote
 * serveur par MelisReactApi. Les actions (list/create/edit/delete) sont libellees par l'i18n hote.
 */

return [
    'melisReactToolCapabilities' => [
        'melisdashboardplugincreator_tool' => [
            'tabs' => [
                ['key' => 'wizard', 'label' => 'tr_melisdashboardplugincreator_caps_wizard',
                    'actions' => ['edit']],
                ['key' => 'thumbnail', 'label' => 'tr_melisdashboardplugincreator_caps_thumbnail',
                    'actions' => ['create', 'delete']],
                ['key' => 'summary', 'label' => 'tr_melisdashboardplugincreator_caps_summary',
                    'actions' => ['list']],
                ['key' => 'finalization', 'label' => 'tr_melisdashboardplugincreator_caps_finalization',
                    'actions' => ['create']],
            ],
        ],
    ],
];
