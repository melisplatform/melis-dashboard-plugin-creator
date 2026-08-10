<?php

namespace MelisDashboardPluginCreator\Controller;

use Laminas\Form\Factory as FormFactory;
use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use Laminas\Session\Container;
use MelisCore\Controller\MelisAbstractActionController;
use MelisCore\Controller\PluginViewController;
use MelisReactApi\Controller\CapabilityGuardTrait;

/**
 * API REST pour l'outil « Dashboard Plugin Creator ».
 *
 * Couche API du back-office React ; l'UI est livrée par la BRIQUE du module (public/ui-react),
 * donc l'outil n'apparaît que si MelisDashboardPluginCreator est ACTIF. Contrat
 * `{ success, data, error }`. Calqué sur MelisReactApiTemplatingPluginCreator.
 *
 * ── Principe directeur ────────────────────────────────────────────────────────────────────────
 * L'outil legacy est un ASSISTANT en 5 étapes qui GÉNÈRE du code (fichiers PHP d'un plugin de
 * dashboard, réécriture de module.config.php / Module.php / fichiers de langue, activation du
 * module). On ne réimplémente RIEN de cette logique côté React :
 *
 *   • la VALIDATION réutilise les formulaires Laminas déclarés dans config/app.tools.php
 *     (`getFormMergedAndOrdered`) — mêmes validateurs, mêmes messages, mêmes règles métier
 *     (mot réservé PHP, module déjà existant, nom de plugin déjà pris, titre déjà pris) ;
 *   • la GÉNÉRATION appelle MelisDashboardPluginCreatorService::generateDashboardPlugin()
 *     (+ MelisToolCreatorService::createTool() pour la branche « nouveau module ») ;
 *   • l'ÉTAT de l'assistant est écrit dans le MÊME conteneur de session que le legacy
 *     (`dashboardplugincreator` → `melis-dashboardplugincreator`), car le service le lit dans
 *     son constructeur. React ne fait que présenter et poster des JSON propres.
 *
 * ⚠ MelisDashboardPluginCreatorService prend un INSTANTANÉ de la session dans son constructeur
 *   et le ServiceManager met l'instance en cache : ne jamais le récupérer AVANT d'avoir fini
 *   d'écrire l'état de l'étape courante dans la même requête.
 *
 * ── Étapes ────────────────────────────────────────────────────────────────────────────────────
 *   1. Plugin      : nom + type (mono / multi-onglets) + nombre d'onglets + destination.
 *   2. Menu        : titre/description par langue (menu déroulant de droite) + vignette.
 *   3. Dashboard   : titre par langue (carte du dashboard) + icône du plugin + icône par onglet.
 *   4. Récapitulatif (lecture seule).
 *   5. Finalisation : génère le plugin, active éventuellement le module.
 *
 * ── Droits ────────────────────────────────────────────────────────────────────────────────────
 * Deux niveaux, tous deux appliqués côté serveur (le React ne fait que masquer les boutons) :
 *   1. accès-outil : `denyUnlessAccess()` → MelisCoreRights::canAccess('melisdashboardplugincreator_tool')
 *   2. capacités avancées : `denyUnlessCan()` sur l'onglet ET son action (cf. react.capabilities.php).
 * Le contrôleur legacy n'avait AUCUN contrôle de droits.
 */
class MelisReactApiDashboardPluginCreatorController extends MelisAbstractActionController
{
    use CapabilityGuardTrait;

    private const MELIS_KEY = 'melisdashboardplugincreator_tool';

    /** Conteneur de session partagé avec le contrôleur legacy (ne pas renommer). */
    private const SESSION_NS   = 'dashboardplugincreator';
    private const SESSION_ROOT = 'melis-dashboardplugincreator';

    private const NEW_MODULE = 'new_module';
    private const MULTI      = 'multi';
    private const MIN_TABS   = 2;   // borne basse du validateur Between de dpc_tab_count
    private const MAX_TABS   = 25;  // borne haute idem

    // ════════════════════════════════════════════════════════════════════════
    //  CONTEXTE / ÉTAT
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Tout ce dont la brique a besoin pour se peindre : préflight d'environnement (le legacy
     * bloque l'outil si `melis.module.load.php` n'est pas inscriptible, si `module/` ne l'est pas,
     * ou si le dossier de vignettes ne l'est pas), méta des 5 étapes, langues de la plateforme,
     * modules existants (mêmes que le select legacy), et les icônes (plugin + onglets), lues
     * depuis le formulaire / la config legacy → une seule source.
     */
    public function contextAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        try {
            $sm         = $this->getServiceManager();
            $translator = $sm->get('translator');
            $config     = $sm->get('MelisCoreConfig');

            // ── Préflight (mêmes contrôles que renderToolContentAction) ──
            $blocking = [];
            if (!is_writable($_SERVER['DOCUMENT_ROOT'] . '/../config/melis.module.load.php')) {
                $blocking[] = $translator->translate('tr_melisdashboardplugincreator_fp_config');
            }
            if (!is_writable($_SERVER['DOCUMENT_ROOT'] . '/../module')) {
                $blocking[] = $translator->translate('tr_melisdashboardplugincreator_fp_module');
            }
            $thumbCfg = (array) $config->getItem('melisdashboardplugincreator/datas/plugin_thumbnail');
            $thumbDir = (string) ($thumbCfg['path'] ?? '');
            if ($thumbDir !== '' && !is_dir($thumbDir)) {
                @mkdir($thumbDir, 0755, true);
            }
            if ($thumbDir === '' || !is_dir($thumbDir) || !is_writable($thumbDir)) {
                $blocking[] = $translator->translate('tr_melisdashboardplugincreator_fp_temp_thumbnail');
            }

            // ── Étapes (libellés traduits) ──
            $steps = [];
            foreach ((array) $config->getItem('melisdashboardplugincreator/datas/steps') as $key => $step) {
                $steps[] = [
                    'key'  => $key,
                    'name' => $translator->translate($step['name'] ?? $key),
                    'icon' => $step['icon'] ?? '',
                ];
            }

            // ── Icônes : celles du plugin (formulaire) et celles des onglets (config) ──
            // Icône du plugin : clé == valeur == classe `fa-...`, écrite telle quelle dans la config
            // générée (`'icon' => 'fa fa-calendar'`).
            $iconForm    = $this->form('melisdashboardplugincreator_step3_form2');
            $pluginIcons = array_values(array_unique(array_map(
                'strval',
                array_keys((array) $iconForm->get('dpc_plugin_icon')->getValueOptions()),
            )));
            // Icônes d'onglet : la config `dashboardTabIcons` est un couple
            // `<classe Glyphicons> => <classe FontAwesome>`. Le legacy stocke la CLÉ (le radio a pour
            // valeur `$optKey`) et n'affiche la valeur `fa-...` que dans le sélecteur — car la vue
            // générée écrit `<a class="glyphicons <clé>">…<i></i></a>`, une icône Glyphicons.
            // Envoyer `fa-...` comme valeur produirait `class="glyphicons fa-calendar"` → aucun glyphe.
            // On renvoie donc les deux : `value` (ce qui est stocké/généré) et `preview` (l'aperçu).
            $tabIcons = [];
            foreach ((array) $config->getItem('melisdashboardplugincreator/datas/dashboardTabIcons') as $glyph => $fa) {
                $tabIcons[] = ['value' => (string) $glyph, 'preview' => (string) $fa];
            }

            return $this->ok([
                'blocking'      => $blocking,   // non vide ⇒ la brique affiche l'erreur et rien d'autre
                'steps'         => $steps,
                'languages'     => $this->languages(),
                'currentLocale' => $this->currentLocale(),
                'siteModules'   => $this->existingModules(),
                'pluginIcons'   => $pluginIcons,
                'tabIcons'      => $tabIcons,
                'minTabs'       => self::MIN_TABS,
                'maxTabs'       => self::MAX_TABS,
                'thumbnail'     => [
                    'minSize' => (int) ($thumbCfg['min_size'] ?? 1),
                    'maxSize' => (int) ($thumbCfg['max_size'] ?? 512000),
                    'accept'  => '.gif,.jpg,.jpeg,.png',
                ],
            ]);
        } catch (\Throwable $e) { return $this->errorResponse($e); }
    }

    /** État courant de l'assistant (conteneur de session) → la brique restaure l'UI après un reload. */
    public function stateAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        try {
            return $this->ok($this->publicState());
        } catch (\Throwable $e) { return $this->errorResponse($e); }
    }

    /** Réinitialise l'assistant : purge la session ET la vignette temporaire sur disque. */
    public function resetAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($deny = $this->denyUnlessCan('wizard')) { return $deny; }
        if ($deny = $this->denyUnlessCan('wizard.edit')) { return $deny; }
        try {
            $this->deleteThumbnailDir();
            $this->setState(['sessionID' => $this->sessionId()]);
            return $this->ok($this->publicState());
        } catch (\Throwable $e) { return $this->errorResponse($e); }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ÉTAPES 1 → 3 (validation + persistance)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Valide et enregistre une étape de configuration. `POST /melis/react-api/dpc/step/:step`.
     * Réponse : `{ success:true, data:{ valid:bool, errors:{...} } }` — `valid:false` n'est PAS une
     * erreur HTTP, c'est un résultat de validation métier (la brique affiche les messages).
     */
    public function stepAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($deny = $this->denyUnlessCan('wizard')) { return $deny; }
        if ($deny = $this->denyUnlessCan('wizard.edit')) { return $deny; }
        try {
            $step    = (int) $this->params()->fromRoute('step', 0);
            $payload = $this->jsonBody();

            $result = match ($step) {
                1 => $this->saveStep1($payload),
                2 => $this->saveStep2($payload),
                3 => $this->saveStep3($payload),
                default => null,
            };
            if ($result === null) { return $this->bad('Unknown step'); }

            return $this->ok($result);
        } catch (\Throwable $e) { return $this->errorResponse($e); }
    }

    /**
     * Étape 1 — nom + type d'affichage (mono / multi-onglets) + nombre d'onglets + destination.
     * Reprend les 3 règles métier du legacy : mot réservé PHP, module déjà présent sur la
     * plateforme, plugin déjà déclaré dans le module existant choisi.
     */
    private function saveStep1(array $p): array
    {
        $data = [
            'dpc_plugin_name'          => trim((string) ($p['dpc_plugin_name'] ?? '')),
            'dpc_plugin_type'          => (string) ($p['dpc_plugin_type'] ?? ''),
            'dpc_tab_count'            => trim((string) ($p['dpc_tab_count'] ?? '')),
            'dpc_plugin_destination'   => (string) ($p['dpc_plugin_destination'] ?? ''),
            'dpc_new_module_name'      => trim((string) ($p['dpc_new_module_name'] ?? '')),
            'dpc_existing_module_name' => trim((string) ($p['dpc_existing_module_name'] ?? '')),
        ];

        $form = $this->form('melisdashboardplugincreator_step1_form');
        $form->setData($data);
        $filter = $form->getInputFilter();

        // Le nombre d'onglets ne concerne que le type « multi-onglets » (idem legacy).
        if ($data['dpc_plugin_type'] === self::MULTI) {
            // multi + nombre vide → on retire seulement le Between (NotEmpty/IsInt restent).
            if ($data['dpc_tab_count'] === '') {
                $chain = new \Laminas\Validator\ValidatorChain();
                foreach ($filter->get('dpc_tab_count')->getValidatorChain()->getValidators() as $validator) {
                    if (!($validator['instance'] instanceof \Laminas\Validator\Between)) {
                        $chain->addValidator($validator['instance'], true);
                    }
                }
                $filter->get('dpc_tab_count')->setValidatorChain($chain);
            }
        } else {
            $filter->remove('dpc_tab_count');
        }

        // Le champ non concerné par la destination choisie ne doit pas être validé (idem legacy).
        if ($data['dpc_plugin_destination'] === self::NEW_MODULE) {
            $filter->remove('dpc_existing_module_name');
        } elseif ($data['dpc_plugin_destination'] !== '') {
            $filter->remove('dpc_new_module_name');
        } else {
            $filter->remove('dpc_new_module_name');
            $filter->remove('dpc_existing_module_name');
        }

        if (!$form->isValid()) {
            return ['valid' => false, 'errors' => $this->formErrors($form)];
        }

        $sm         = $this->getServiceManager();
        $translator = $sm->get('translator');
        $errors     = [];

        if ($data['dpc_plugin_destination'] === self::NEW_MODULE && $data['dpc_new_module_name'] !== '') {
            $newModule = strtolower($data['dpc_new_module_name']);

            $reserved = (array) $sm->get('MelisCoreConfig')->getItem('melisdashboardplugincreator/datas/reserved_keywords');
            if (in_array($newModule, $reserved, true)) {
                $errors['dpc_new_module_name'] = $this->fieldError($form, 'dpc_new_module_name', sprintf(
                    $translator->translate('tr_melisdashboardplugincreator_err_module_name_reserved_keyword'),
                    $data['dpc_new_module_name'],
                ));
            }

            $modulesSvc = $sm->get('ModulesService');
            $existing   = array_map('strtolower', array_merge(
                (array) $modulesSvc->getModulePlugins(),
                (array) $modulesSvc->getAllModules(),
            ));
            if (in_array($newModule, $existing, true)) {
                $errors['dpc_new_module_name'] = $this->fieldError($form, 'dpc_new_module_name', sprintf(
                    $translator->translate('tr_melisdashboardplugincreator_err_module_exist'),
                    $data['dpc_new_module_name'],
                ));
            }
        }

        if ($data['dpc_plugin_destination'] !== self::NEW_MODULE && $data['dpc_existing_module_name'] !== '') {
            $existingModule = $data['dpc_existing_module_name'];
            $modulePlugins  = (array) $this->dpcService()->getModuleExistingPlugins($existingModule);
            $pluginIds      = (array) ($modulePlugins[$existingModule]['pluginId'] ?? []);
            if ($pluginIds) {
                $newPluginName = (string) $this->dpcService()->generateModuleNameCase($data['dpc_plugin_name']);
                if (in_array(trim($newPluginName), $pluginIds, true)) {
                    $errors['dpc_plugin_name'] = $this->fieldError($form, 'dpc_plugin_name', sprintf(
                        $translator->translate('tr_melisdashboardplugincreator_err_plugin_name_exist'),
                        $data['dpc_plugin_name'],
                    ));
                }
            }
        }

        if ($errors) {
            return ['valid' => false, 'errors' => $errors];
        }

        // On enregistre les données filtrées du formulaire (le champ non retenu reste vide).
        $clean = $form->getData();
        // Le type « mono » n'a pas d'onglets : on neutralise le compteur pour l'étape 3.
        if (($clean['dpc_plugin_type'] ?? '') !== self::MULTI) {
            $clean['dpc_tab_count'] = '';
        }

        $state = $this->state();
        // Changer la destination / le type / le nombre d'onglets invalide l'étape 3
        // (titres du dashboard + icônes par onglet en dépendent).
        $prev = $state['step_1'] ?? [];
        if ($prev
            && (($prev['dpc_plugin_type'] ?? null) !== ($clean['dpc_plugin_type'] ?? null)
                || ($prev['dpc_tab_count'] ?? null) !== ($clean['dpc_tab_count'] ?? null)
                || ($prev['dpc_plugin_destination'] ?? null) !== ($clean['dpc_plugin_destination'] ?? null))) {
            unset($state['step_3']);
        }
        $state['step_1'] = $clean;
        $this->setState($state);

        return ['valid' => true, 'errors' => (object) []];
    }

    /**
     * Étape 2 — titre + description par langue (menu de droite) + vignette (déjà téléversée via
     * /dpc/thumbnail). Règle legacy : l'étape passe dès qu'UNE langue est valide (les autres restent
     * facultatives). La vignette est obligatoire. Sur module existant, on refuse un titre déjà pris.
     */
    private function saveStep2(array $p): array
    {
        $langs  = $this->languages();
        $posted = (array) ($p['languages'] ?? []);
        $errors = [];
        $saved  = [];
        $valid  = 0;

        // Titres déjà utilisés (module existant) → refus de doublon, par langue.
        $existingTitles = [];
        $state          = $this->state();
        $existingModule = (string) ($state['step_1']['dpc_existing_module_name'] ?? '');
        $isExisting     = ($state['step_1']['dpc_plugin_destination'] ?? '') !== self::NEW_MODULE && $existingModule !== '';
        if ($isExisting) {
            $existingTitles = (array) $this->dpcService()->getExistingTranslatedPluginTitle(
                $this->dpcService()->getModuleExistingPlugins($existingModule),
                $existingModule,
            );
        }

        foreach ($langs as $lang) {
            $locale = $lang['locale'];
            $form   = $this->form('melisdashboardplugincreator_step2_form1');
            $form->setData([
                'dpc_plugin_title' => trim((string) ($posted[$locale]['dpc_plugin_title'] ?? '')),
                'dpc_plugin_desc'  => trim((string) ($posted[$locale]['dpc_plugin_desc'] ?? '')),
                'dpc_lang_local'   => $locale,
            ]);

            if (!$form->isValid()) {
                $errors[$locale] = $this->formErrors($form);
                continue;
            }

            $clean = $form->getData();

            // Doublon de titre pour ce module existant + cette langue (règle legacy).
            if ($isExisting && $existingTitles) {
                $title = (string) $this->dpcService()->removeExtraSpace((string) $clean['dpc_plugin_title']);
                if (in_array($title, (array) ($existingTitles[$locale] ?? []), true)) {
                    $errors[$locale]['dpc_plugin_title'] = $this->fieldError($form, 'dpc_plugin_title', sprintf(
                        $this->getServiceManager()->get('translator')->translate('tr_melisdashboardplugincreator_err_plugin_title_exist'),
                        $clean['dpc_plugin_title'],
                        $lang['name'],
                    ));
                    continue;
                }
            }

            $valid++;
            $saved[$locale] = $clean;
        }

        // La vignette est obligatoire (téléversée séparément via /dpc/thumbnail).
        if (empty($state['step_2']['plugin_thumbnail'])) {
            $translator = $this->getServiceManager()->get('translator');
            return ['valid' => false, 'errors' => [
                'dpc_plugin_upload_thumbnail' => [
                    'label'    => $translator->translate('tr_melisdashboardplugincreator_upload_thumbnail'),
                    'messages' => [$translator->translate('tr_melisdashboardplugincreator_err_empty')],
                ],
            ]];
        }

        if (!$valid) {
            return ['valid' => false, 'errors' => $errors];
        }

        // Au moins une langue valide ⇒ étape valide (on préserve la vignette déjà en session).
        $thumbnail = $state['step_2']['plugin_thumbnail'];
        $state['step_2'] = $saved + ['plugin_thumbnail' => $thumbnail];
        $this->setState($state);

        return ['valid' => true, 'errors' => (object) []];
    }

    /**
     * Étape 3 — titre du dashboard par langue + icône du plugin + icône par onglet (si multi).
     * Deux formulaires legacy : la langue (step3_form1) et les icônes (step3_form2, augmenté des
     * champs `dpc_plugin_tab_icon_N` quand le type est multi-onglets).
     */
    private function saveStep3(array $p): array
    {
        $state = $this->state();
        if (empty($state['step_1'])) {
            return ['valid' => false, 'errors' => ['__step' => ['label' => '', 'messages' => ['Step 1 is required']]]];
        }

        // ── Langue : au moins une langue complète ──
        $langs  = $this->languages();
        $posted = (array) ($p['languages'] ?? []);
        $errors = [];
        $saved  = [];
        $valid  = 0;

        foreach ($langs as $lang) {
            $locale = $lang['locale'];
            $form   = $this->form('melisdashboardplugincreator_step3_form1');
            $form->setData([
                'dpc_plugin_title' => trim((string) ($posted[$locale]['dpc_plugin_title'] ?? '')),
                'dpc_lang_local'   => $locale,
            ]);
            if ($form->isValid()) {
                $valid++;
                $saved[$locale] = $form->getData();
            } else {
                $errors[$locale] = $this->formErrors($form);
            }
        }

        // ── Icônes : icône du plugin + icône par onglet (si multi) ──
        $isMulti  = ($state['step_1']['dpc_plugin_type'] ?? '') === self::MULTI;
        $tabCount = $isMulti ? max(0, (int) ($state['step_1']['dpc_tab_count'] ?? 0)) : 0;
        $iconForm = $this->form('melisdashboardplugincreator_step3_form2');
        $this->addTabIconElements($iconForm, $tabCount);

        $iconData = ['dpc_plugin_icon' => (string) ($p['icon']['dpc_plugin_icon'] ?? '')];
        for ($i = 1; $i <= $tabCount; $i++) {
            $iconData['dpc_plugin_tab_icon_' . $i] = (string) ($p['icon']['dpc_plugin_tab_icon_' . $i] ?? '');
        }
        $iconForm->setData($iconData);

        $iconErrors = [];
        if (!$iconForm->isValid()) {
            $iconErrors = $this->formErrors($iconForm);
        }

        if (!$valid || $iconErrors) {
            // Aucune langue valide → on renvoie les erreurs par langue ; sinon rien côté langue.
            $out = $valid ? [] : $errors;
            if ($iconErrors) { $out['icon'] = $iconErrors; }
            return ['valid' => false, 'errors' => $out ?: (object) []];
        }

        $step3 = $saved;
        $step3['icon_form'] = $iconForm->getData();
        $state['step_3']    = $step3;
        $this->setState($state);

        return ['valid' => true, 'errors' => (object) []];
    }

    /**
     * Ajoute les champs radio `dpc_plugin_tab_icon_N` (1..$tabCount) au formulaire d'icônes, avec
     * un validateur NotEmpty chacun. Transcription de `setDashboardTabIconElements()` du legacy.
     */
    private function addTabIconElements(\Laminas\Form\Form $form, int $tabCount): void
    {
        if ($tabCount < 1) { return; }

        $sm         = $this->getServiceManager();
        $translator = $sm->get('translator');
        $options    = (array) $sm->get('MelisCoreConfig')->getItem('melisdashboardplugincreator/datas/dashboardTabIcons');
        $filter     = $form->getInputFilter();

        for ($i = 1; $i <= $tabCount; $i++) {
            $name    = 'dpc_plugin_tab_icon_' . $i;
            $element = new \Laminas\Form\Element\Radio($name);
            $element->setLabel($translator->translate('tr_melisdashboardplugincreator_dashboard_tab_label') . ' ' . $i);
            $element->setValueOptions($options);
            $element->setDisableInArrayValidator(true);
            $form->add($element);

            $filter->add([
                'name'       => $name,
                'required'   => true,
                'validators' => [[
                    'name'    => 'NotEmpty',
                    'options' => ['messages' => [
                        \Laminas\Validator\NotEmpty::IS_EMPTY
                            => $translator->translate('tr_melisdashboardplugincreator_err_empty'),
                    ]],
                ]],
            ]);
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  VIGNETTE (étape 2)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Téléverse la vignette du plugin. Mêmes contraintes que le legacy (taille min/max de la
     * config, contenu réellement décodable en image), plus deux durcissements : liste blanche
     * d'extensions et nom de fichier assaini, dossier en 0755.
     */
    public function thumbnailAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($deny = $this->denyUnlessCan('thumbnail')) { return $deny; }
        if ($deny = $this->denyUnlessCan('thumbnail.create')) { return $deny; }
        try {
            $translator = $this->getServiceManager()->get('translator');
            $files      = $this->getRequest()->getFiles()->toArray();
            $file       = $files['dpc_plugin_upload_thumbnail'] ?? $files['file'] ?? null;

            if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
                return $this->bad($translator->translate('tr_melisdashboardplugincreator_save_upload_empty_file'));
            }

            $cfg     = (array) $this->getServiceManager()->get('MelisCoreConfig')->getItem('melisdashboardplugincreator/datas/plugin_thumbnail');
            $minSize = (int) ($cfg['min_size'] ?? 1);
            $maxSize = (int) ($cfg['max_size'] ?? 512000);
            $size    = (int) ($file['size'] ?? 0);

            if ($size < $minSize || $size > $maxSize) {
                return $this->bad(sprintf($translator->translate('tr_melisdashboardplugincreator_upload_too_big'), $this->formatBytes($maxSize)));
            }

            // Le contenu doit être une image (le legacy sniffe avec GD ; on garde ce contrôle) ET
            // porter une extension autorisée, cohérente avec le type réel.
            $info = @getimagesize($file['tmp_name']);
            $ext  = match ($info[2] ?? null) {
                IMAGETYPE_GIF  => 'gif',
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG  => 'png',
                default        => null,
            };
            if ($ext === null) {
                return $this->bad($translator->translate('tr_melisdashboardplugincreator_save_upload_image_imageFalseType'));
            }

            $base = pathinfo((string) ($file['name'] ?? 'thumbnail'), PATHINFO_FILENAME);
            $base = $this->dpcService()->removeAccents(str_replace(' ', '_', trim($base)));
            $base = preg_replace('/[^A-Za-z0-9_-]/', '', $base) ?: 'thumbnail';

            $dir = $this->thumbnailDir();
            if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                return $this->bad($translator->translate('tr_melisdashboardplugincreator_fp_temp_thumbnail'));
            }
            // Une seule vignette par assistant : on purge les précédentes.
            foreach ((array) glob($dir . '/*') as $old) { @unlink($old); }

            $target = $dir . '/' . $base . '.' . $ext;
            if (!@move_uploaded_file($file['tmp_name'], $target)) {
                return $this->bad($translator->translate('tr_melisdashboardplugincreator_save_upload_error_encounter'));
            }
            @chmod($target, 0644);

            // Chemin WEB (c'est ce que lit le générateur pour retrouver l'extension + le fichier).
            $webPath = '/dpc/temp-thumbnail/' . $this->sessionId() . '/' . $base . '.' . $ext;
            $state   = $this->state();
            $state['step_2']['plugin_thumbnail'] = $webPath;
            $this->setState($state);

            return $this->ok(['thumbnail' => $webPath]);
        } catch (\Throwable $e) { return $this->errorResponse($e); }
    }

    /**
     * Retire la vignette. Le legacy ne vidait QUE la clé de session et laissait le fichier orphelin
     * sur disque : on supprime aussi le fichier.
     */
    public function thumbnailRemoveAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($deny = $this->denyUnlessCan('thumbnail')) { return $deny; }
        if ($deny = $this->denyUnlessCan('thumbnail.delete')) { return $deny; }
        try {
            $this->deleteThumbnailDir();
            $state = $this->state();
            unset($state['step_2']['plugin_thumbnail']);
            $this->setState($state);
            return $this->ok(['thumbnail' => null]);
        } catch (\Throwable $e) { return $this->errorResponse($e); }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  RÉCAPITULATIF (étape 4) + GÉNÉRATION (étape 5)
    // ════════════════════════════════════════════════════════════════════════

    /** Récapitulatif lisible des étapes 1 → 3 (l'étape 4 legacy est un simple rendu en lecture seule). */
    public function summaryAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($deny = $this->denyUnlessCan('summary')) { return $deny; }
        if ($deny = $this->denyUnlessCan('summary.list')) { return $deny; }
        try {
            $state = $this->state();
            return $this->ok($this->publicState() + [
                'languages'                => $this->languages(),
                'targetModule'             => empty($state['step_1']) ? null : $this->destinationModule($state['step_1']),
                'existingModule'           => (string) ($state['step_1']['dpc_existing_module_name'] ?? '') ?: null,
                'isInactiveExistingModule' => $this->isInactiveExistingModule($state),
            ]);
        } catch (\Throwable $e) { return $this->errorResponse($e); }
    }

    /**
     * Étape 5 — GÉNÈRE le plugin. Transcription fidèle de `processStep5()` :
     *   • branche « nouveau module » : amorce le conteneur `melistoolcreator`, appelle
     *     MelisToolCreatorService::createTool() (échafaude le module), génère le plugin, active le
     *     module, invalide le cache de chemins de modules ET le cache du menu du dashboard ;
     *   • branche « module existant » : génère simplement le plugin dans ce module.
     *
     * Les services legacy écrivent sur le disque et peuvent émettre des warnings PHP (mkdir/copy)
     * directement sur la sortie : on les capture (`ob_start`) pour ne pas corrompre le JSON.
     */
    public function generateAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($deny = $this->denyUnlessCan('finalization')) { return $deny; }
        if ($deny = $this->denyUnlessCan('finalization.create')) { return $deny; }
        try {
            $sm         = $this->getServiceManager();
            $translator = $sm->get('translator');
            $state      = $this->state();

            // L'assistant doit être complet : on ne génère jamais depuis un état partiel.
            foreach (['step_1', 'step_2', 'step_3'] as $required) {
                if (empty($state[$required])) {
                    return $this->bad($translator->translate('tr_melisdashboardplugincreator_err_message'));
                }
            }
            if (empty($state['step_2']['plugin_thumbnail'])) {
                return $this->bad($translator->translate('tr_melisdashboardplugincreator_err_message'));
            }

            $payload  = $this->jsonBody();
            $activate = !empty($payload['dpc_activate_plugin']);
            $isNew    = ($state['step_1']['dpc_plugin_destination'] ?? '') === self::NEW_MODULE;

            ob_start();
            try {
                if ($isNew) {
                    // MelisToolCreator échafaude le module vide que le plugin viendra remplir.
                    $toolContainer = new Container('melistoolcreator');
                    $toolContainer['melis-toolcreator'] = ['step1' => [
                        'tcf-name'           => $state['step_1']['dpc_new_module_name'],
                        'tcf-tool-type'      => 'blank',
                        'tcf-tool-edit-type' => 'modal',
                    ]];
                    $toolCreatorSrv = $sm->get('MelisToolCreatorService');
                    $toolCreatorSrv->createTool();
                }

                // ⚠ après l'écriture de session : le service snapshot l'état dans son constructeur.
                $generated = (bool) $this->dpcService()->generateDashboardPlugin();

                if ($generated && $isNew) {
                    $sm->get('ModulesService')->activateModule($toolCreatorSrv->moduleName());

                    // Force la reconstruction du cache de chemins de modules.
                    $pathCache = $_SERVER['DOCUMENT_ROOT'] . '/../config/melis.modules.path.php';
                    if (file_exists($pathCache)) { @unlink($pathCache); }
                    unset($toolContainer['melis-toolcreator']);

                    // Vide le cache du menu du dashboard (le nouveau plugin doit y apparaître).
                    $sm->get('MelisCoreCacheSystemService')
                        ->deleteCacheByPrefix('meliscore_dashboard_menu_content_', PluginViewController::cacheConfig);
                }
            } finally {
                ob_end_clean();
            }

            if (empty($generated)) {
                return $this->bad($translator->translate('tr_melisdashboardplugincreator_generate_plugin_error_encountered'));
            }

            $pluginName   = $state['step_1']['dpc_plugin_name'];
            $targetModule = $this->destinationModule($state['step_1']);

            // Succès : l'assistant repart de zéro (le générateur a déjà purgé la vignette temporaire).
            $this->setState(['sessionID' => $this->sessionId()]);

            return $this->ok([
                'generated'       => true,
                'module'          => $targetModule,
                'plugin'          => $pluginName,
                'restartRequired' => $activate,   // la brique recharge la plateforme
                'notices'         => [],
            ]);
        } catch (\Throwable $e) { return $this->errorResponse($e); }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  HELPERS — session / formulaires / données de référence
    // ════════════════════════════════════════════════════════════════════════

    /** Instancie un formulaire de l'outil depuis sa config `app.tools.php` (mêmes validateurs). */
    private function form(string $key): \Laminas\Form\Form
    {
        $factory = new FormFactory();
        $factory->setFormElementManager($this->getServiceManager()->get('FormElementManager'));
        $config = $this->getServiceManager()->get('MelisCoreConfig')
            ->getFormMergedAndOrdered('melisdashboardplugincreator/forms/' . $key, $key);

        return $factory->createForm($config);
    }

    /** Module de destination : le nouveau (normalisé) ou le module existant. */
    private function destinationModule(array $step1): string
    {
        return ($step1['dpc_plugin_destination'] ?? '') === self::NEW_MODULE
            ? (string) $this->dpcService()->generateModuleNameCase($step1['dpc_new_module_name'] ?? '')
            : (string) ($step1['dpc_existing_module_name'] ?? '');
    }

    /** 1 si le module existant choisi n'est pas actif (note affichée à l'étape 5, comme le legacy). */
    private function isInactiveExistingModule(array $state): int
    {
        $module = (string) ($state['step_1']['dpc_existing_module_name'] ?? '');
        if ($module === '') { return 0; }
        $active = (array) $this->getServiceManager()->get('ModulesService')->getActiveModules();
        return in_array($module, $active, true) ? 0 : 1;
    }

    /** Modules « utilisateur » proposés au select (mêmes exclusions que le factory legacy). */
    private function existingModules(): array
    {
        $modules = (array) $this->getServiceManager()->get('ModulesService')->getUserModules();
        $out     = [];
        foreach ($modules as $module) {
            if (in_array($module, ['MelisModuleConfig', 'MelisSites'], true)) { continue; }
            $out[] = (string) $module;
        }
        return array_values($out);
    }

    /**
     * ⚠ N'appeler qu'après avoir écrit l'état de session de la requête : le service en prend un
     * instantané dans son constructeur et le ServiceManager met l'instance en cache.
     */
    private function dpcService(): \MelisDashboardPluginCreator\Service\MelisDashboardPluginCreatorService
    {
        return $this->getServiceManager()->get('MelisDashboardPluginCreatorService');
    }

    /** Langues de la plateforme, locale courante en tête (comme `getLanguageForms`). */
    private function languages(): array
    {
        $rows    = $this->getServiceManager()->get('MelisCoreTableLang')->fetchAll()->toArray();
        $current = $this->currentLocale();

        $langs = array_map(static fn ($r) => [
            'id'     => (int) $r['lang_id'],
            'locale' => (string) $r['lang_locale'],
            'name'   => (string) $r['lang_name'],
        ], $rows);

        usort($langs, static fn ($a, $b) => ($b['locale'] === $current ? 1 : 0) <=> ($a['locale'] === $current ? 1 : 0));

        return $langs;
    }

    private function currentLocale(): string
    {
        $c = new Container('meliscore');
        return (string) ($c['melis-lang-locale'] ?? 'en_EN');
    }

    // ── État de session (partagé avec le contrôleur legacy) ──────────────────

    private function state(): array
    {
        $c = new Container(self::SESSION_NS);
        return (array) ($c[self::SESSION_ROOT] ?? []);
    }

    /** Écriture explicite (lire → muter → réécrire) : `$c['root']['step_1'] = …` ne persiste pas. */
    private function setState(array $state): void
    {
        $state['sessionID'] = $state['sessionID'] ?? $this->sessionId();
        $c = new Container(self::SESSION_NS);
        $c[self::SESSION_ROOT] = $state;
    }

    /**
     * Jeton unique de l'assistant — sert UNIQUEMENT à nommer le dossier temporaire des vignettes
     * (`/dpc/temp-thumbnail/<jeton>/…`). Volontairement décorrélé de l'ID de session PHP : celui-ci
     * finirait dans une URL publique, et le contrôleur legacy le régénérait (cf. renderToolAction).
     */
    private function sessionId(): string
    {
        $c     = new Container(self::SESSION_NS);
        $state = (array) ($c[self::SESSION_ROOT] ?? []);
        if (!empty($state['sessionID'])) {
            return (string) $state['sessionID'];
        }
        // Persisté dès la 1ʳᵉ génération : `thumbnailDir()` et l'URL publique de la vignette
        // doivent tomber sur le MÊME dossier.
        $state['sessionID']  = bin2hex(random_bytes(16));
        $c[self::SESSION_ROOT] = $state;
        return $state['sessionID'];
    }

    /**
     * État exposé à la brique, NORMALISÉ : la forme interne de la session (`icon_form`,
     * `plugin_thumbnail`) est un détail du générateur legacy — la brique reçoit des tableaux/objets
     * plats. `completedStep` permet de reprendre l'assistant après un rechargement de page.
     */
    private function publicState(): array
    {
        $state = $this->state();

        // Étape 2 : textes par langue (hors vignette).
        $step2 = (object) array_diff_key((array) ($state['step_2'] ?? []), ['plugin_thumbnail' => 1]);

        // Étape 3 : textes par langue (hors `icon_form`) + icônes séparées.
        $rawStep3 = (array) ($state['step_3'] ?? []);
        $iconForm = (array) ($rawStep3['icon_form'] ?? []);
        unset($rawStep3['icon_form']);
        $tabIcons = [];
        foreach ($iconForm as $key => $value) {
            if (str_starts_with($key, 'dpc_plugin_tab_icon_')) {
                $tabIcons[(int) substr($key, strlen('dpc_plugin_tab_icon_'))] = (string) $value;
            }
        }
        ksort($tabIcons);

        $completed = 0;
        foreach (['step_1', 'step_2', 'step_3'] as $i => $key) {
            if (!empty($state[$key])) { $completed = $i + 1; }
        }

        return [
            'step1'         => (object) ($state['step_1'] ?? []),
            'step2'         => $step2,
            'step3'         => [
                'texts'    => (object) $rawStep3,
                'icon'     => (string) ($iconForm['dpc_plugin_icon'] ?? ''),
                'tabIcons' => (object) $tabIcons,
            ],
            'thumbnail'     => $state['step_2']['plugin_thumbnail'] ?? null,
            'completedStep' => $completed,
        ];
    }

    private function thumbnailDir(): string
    {
        $cfg = (array) $this->getServiceManager()->get('MelisCoreConfig')->getItem('melisdashboardplugincreator/datas/plugin_thumbnail');
        return rtrim((string) ($cfg['path'] ?? ''), '/') . '/' . $this->sessionId();
    }

    private function deleteThumbnailDir(): void
    {
        $dir = $this->thumbnailDir();
        if (!is_dir($dir)) { return; }
        foreach ((array) glob($dir . '/*') as $file) { @unlink($file); }
        @rmdir($dir);
    }

    // ── Erreurs de formulaire ────────────────────────────────────────────────

    /**
     * Aplati les messages Laminas en `{ champ: { label, messages: [texte…] } }`.
     * Les validateurs de `app.tools.php` portent des CLÉS `tr_…` : on les traduit ici.
     */
    private function formErrors(\Laminas\Form\Form $form): array
    {
        $translator = $this->getServiceManager()->get('translator');
        $out        = [];

        foreach ($form->getMessages() as $field => $messages) {
            $texts = [];
            foreach ((array) $messages as $message) {
                $texts[] = is_string($message) ? $translator->translate($message) : (string) json_encode($message);
            }
            $out[$field] = [
                'label'    => $form->has($field) ? (string) $form->get($field)->getLabel() : '',
                'messages' => $texts,
            ];
        }
        return $out;
    }

    /** Erreur métier ponctuelle attachée à un champ (message déjà traduit). */
    private function fieldError(\Laminas\Form\Form $form, string $field, string $message): array
    {
        return [
            'label'    => $form->has($field) ? (string) $form->get($field)->getLabel() : '',
            'messages' => [$message],
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'kB', 'MB', 'GB'];
        $i     = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $i     = min($i, count($units) - 1);
        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
    }

    // ── Socle HTTP / droits (gabarit MelisReactApi) ──────────────────────────

    private function isAuthenticated(): bool
    {
        return $this->getServiceManager()->get('MelisCoreAuth')->hasIdentity();
    }

    private function denyUnlessAccess(): ?HttpResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        try {
            if (!$this->getServiceManager()->get('MelisCoreRights')->canAccess(self::MELIS_KEY)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
            }
        } catch (\Throwable) {}
        return null;
    }

    /** Corps JSON de la requête (les endpoints d'upload lisent `getFiles()` à la place). */
    private function jsonBody(): array
    {
        $raw = (string) $this->getRequest()->getContent();
        if ($raw === '') { return []; }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function ok($data, int $status = 200): HttpResponse
    {
        return $this->jsonResponse(['success' => true, 'data' => $data], $status);
    }

    private function bad(string $message): HttpResponse
    {
        return $this->jsonResponse(['success' => false, 'error' => $message], 400);
    }

    private function jsonResponse(array $data, int $status = 200): HttpResponse
    {
        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $response->setStatusCode($status);
        $response->getHeaders()->addHeaders([
            'Content-Type'           => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'no-store',
        ]);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response;
    }

    private function errorResponse(\Throwable $e, int $status = 500): HttpResponse
    {
        return $this->jsonResponse([
            'success' => false,
            'error'   => $e->getMessage(),
            'file'    => basename($e->getFile()) . ':' . $e->getLine(),
        ], $status);
    }
}
