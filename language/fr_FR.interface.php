<?php
    return [	
	    'tr_melisdashboardplugincreator_title' => 'Créateur de Dashboard plugin',
		'tr_melisdashboardplugincreator_desc' => 'Le créateur de Dashboard plugin génère des nouveaux plugins prêt à l\'emploi.',

		//Buttons
		'tr_melisdashboardplugincreator_back' => 'Retour',
		'tr_melisdashboardplugincreator_next' => 'Suivant',
		'tr_melisdashboardplugincreator_finish_and_create_the_plugin' => 'Terminer et créer le plugin',

		 // Warnings
		'tr_melisdashboardplugincreator_fp_title' => 'Problème de droits',
		'tr_melisdashboardplugincreator_fp_msg' => 'Pour créer ce plugin veuillez donner des droits d\'écriture pour les dossiers suivants ou contactez l\'administrateur.',
		'tr_melisdashboardplugincreator_fp_config' => '<b>/config/melis.module.load.php</b> - Ce fichier est nécessaire pour activer le nouveau module après sa création',
		'tr_melisdashboardplugincreator_fp_module' => '<b>/module</b> - Le répertoire dans lequel les modules créés sont enregistrés',
		'tr_melisdashboardplugincreator_fp_temp_thumbnail' => '<b>/melis-dashboard-plugin-creator/public/temp-thumbnail</b> - Le répertoire dans lequel les images sont enregistrées temporairement',

		 // Error messages
		'tr_melisdashboardplugincreator_err_message' => 'Impossible de procéder à l\'étape suivante',
		'tr_melisdashboardplugincreator_err_invalid_name' => 'Seul les caractères alphabetiques sont autorisés',	
		'tr_melisdashboardplugincreator_err_long_50' => 'Valeur trop longue, elle doit être de moins de 50 caractères',
		'tr_melisdashboardplugincreator_err_empty' => 'Valeur requise, ne peut être vide',
		'tr_melisdashboardplugincreator_value_must_be_between_2_to_25' => 'Seules les valeurs entre 2 et 25 sont autorisées',
		'tr_melisdashboardplugincreator_integer_only' => 'La valeur saisie doit être un nombre entier uniquement',

		'tr_melisdashboardplugincreator_save_upload_image_imageFalseType' => 'Format d\'image invalide',
		'tr_melisdashboardplugincreator_save_upload_image_imageNotDetected' => 'Format d\'image inconnu',
		'tr_melisdashboardplugincreator_save_upload_image_imageNotReadable' => 'L\'image n\'existe pas ou n\'est pas lisible',
		'tr_melisdashboardplugincreator_save_upload_file_path_rights_error' => 'Vous n\'avez pas les droits pour éxéctuer cette action, veuillez contacter l\'administrateur',
		'tr_melisdashboardplugincreator_save_upload_empty_file' => 'Veuillez ajouter une image',
		'tr_melisdashboardplugincreator_save_upload_error_encounter' => 'Erreur lors de l\'upload de l\'image.',
		'tr_melisdashboardplugincreator_save_upload_file' => 'Fichier',
		'tr_melisdashboardplugincreator_err_module_exist' => '"%s" existe déjà, veuillez en essayer un autre',
		'tr_melisdashboardplugincreator_err_module_name_reserved_keyword' => '"%s" est un mot clef réservé, veuillez en saisir un autre',
		'tr_melisdashboardplugincreator_err_plugin_name_exist' => '"%s" existe déjà pour le module sélectionné, veuillez en choisir un autre',
		'tr_melisdashboardplugincreator_err_plugin_title_exist' => 'Le titre du plugin "%s" existe déjà pour la langue "%s" du module sélectionné',
		'tr_melisdashboardplugincreator_generate_plugin_error_encountered' => 'Erreurs lors de la génération du plugin.',
		'tr_melisdashboardplugincreator_upload_too_big' => 'La taille de l\'image ne doit pas excéder %s',

		// Steps
		'tr_melisdashboardplugincreator_plugin' => 'Plugin',
		'tr_melisdashboardplugincreator_menu_texts_display' => 'Textes du menu et affichage',
		'tr_melisdashboardplugincreator_dashboard_texts_display' => 'Textes du dashboard et affichage',
		'tr_melisdashboardplugincreator_summary' => 'Récapitulatif',
		'tr_melisdashboardplugincreator_finalization' => 'Finalisation',

		//Step1 Form
		'tr_melisdashboardplugincreator_title_step_1' => 'Propriétés du plugin',
		'tr_melisdashboardplugincreator_desc_step_1' => 'Saisissez le nom du plugin.<br>Veuillez ensuite choisir la destination du code, nouveau module ou module existant.',
		'tr_melisdashboardplugincreator_dpc_plugin_name' => 'Nom du plugin',
		'tr_melisdashboardplugincreator_dpc_plugin_name tooltip' => 'Saisissez le nom du plugin.',
		'tr_melisdashboardplugincreator_dpc_plugin_type' => 'Type de vue',
		'tr_melisdashboardplugincreator_dpc_plugin_type tooltip' => 'Sélectionnez le type de plugin.',
		'tr_melisdashboardplugincreator_single_tab' => 'Simple',
		'tr_melisdashboardplugincreator_multi_tab' => 'Multi-onglets',
		'tr_melisdashboardplugincreator_with' => 'avec',
		'tr_melisdashboardplugincreator_tabs' => 'onglets',
		'tr_melisdashboardplugincreator_tab_count' => 'Nombre d\'onglets',
		'tr_melisdashboardplugincreator_dpc_plugin_destination' => 'Destination',
		'tr_melisdashboardplugincreator_dpc_plugin_destination tooltip' => 'Sélectionnez la destinations du plugin',
		'tr_melisdashboardplugincreator_destination_new_opt' => 'Nouveau module',
		'tr_melisdashboardplugincreator_destination_existing_opt' => 'Module existant',
		'tr_melisdashboardplugincreator_dpc_new_module_name' => 'Nouveau nom de module',
		'tr_melisdashboardplugincreator_dpc_new_module_name tooltip' => 'Saisissez le nom du module à créer',
		'tr_melisdashboardplugincreator_dpc_existing_module_name' => 'Choisissez un module',
		'tr_melisdashboardplugincreator_dpc_existing_module_name tooltip' => 'Sélectionnez un module existant',
		'tr_melisdashboardplugincreator_dpc_existing_module_placeholder' => 'Choisissez un module',

		//Step2 Form
		'tr_melisdashboardplugincreator_title_step_2' => 'Traductions et images du menu des plugins',
		'tr_melisdashboardplugincreator_desc_step_2' => 'Saisissez la traduction du texte dans les différentes langues, au moins une langue doit être saisie.<br>Choisissez l\'image du plugin qui apparaîtra dans le menu de droite.',
		'tr_melisdashboardplugincreator_dpc_plugin_title' => 'Titre du plugin',
		'tr_melisdashboardplugincreator_dpc_plugin_title tooltip' => 'Saisissez le titre du plugin',
		'tr_melisdashboardplugincreator_dpc_plugin_desc' => 'Description du plugin',
		'tr_melisdashboardplugincreator_dpc_plugin_desc tooltip' => 'Saisissez la description',       
		'tr_melisdashboardplugincreator_upload_thumbnail' => 'Image du plugin(190x100 recommandé)',
		'tr_melisdashboardplugincreator_upload_thumbnail tooltip' => 'Uploadez l\'image du plugin',
		'tr_melisdashboardplugincreator_common_label_yes' => 'Oui',
        'tr_melisdashboardplugincreator_common_label_no' => 'Non',
        'tr_melisdashboardplugincreator_delete_plugin_thumbnail_title' => 'Supprimer l\'image',
        'tr_melisdashboardplugincreator_delete_plugin_thumbnail_confirm' => 'Êtes-vous sûr(e) de vouloir supprimer l\'image ?',
        'tr_melisdashboardplugincreator_delete_plugin_thumbnail_error' => 'Erreur lors de la suppression de l\'image',    
        'tr_melisdashboardplugincreator_input_file_uploading' => 'Upload...', 
        'tr_melisdashboardplugincreator_input_file_upload_label' => 'Parcourir...',
        'tr_melisdashboardplugincreator_input_file_upload_placeholder' => 'Aucun fichier sélectionné',

		//Step3 Form
		'tr_melisdashboardplugincreator_title_step_3' => 'Traductions et icône du Dashboard plugin',
		'tr_melisdashboardplugincreator_desc_step_3' => 'Saisissez la traduction du texte dans les différentes langues, au moins une langue doit être saisie.<br>Choisissez l\'icône du plugin qui apparaîtra dans le coin à gauche du plugin.',
		'tr_melisdashboardplugincreator_plugin_icon' => 'Icone du plugin',
		'tr_melisdashboardplugincreator_plugin_icon tooltip' => 'Sélectionnez l\'icone du plugin',
		'tr_melisdashboardplugincreator_dashboard_tab_icon' => 'Icones des onglets du plugin',
		'tr_melisdashboardplugincreator_dashboard_tab_icon tooltip' => 'Sélectionnez les icones de chaque onglet',  
		'tr_melisdashboardplugincreator_dashboard_tab_label' => 'Onglet du plugin',

		 //Step4 Form
		'tr_melisdashboardplugincreator_title_step_4' => 'Récapitulatif',
		'tr_melisdashboardplugincreator_desc_step_4' => 'Vérifiez les paramètres avant la création du plugin',
		'tr_melisdashboardplugincreator_desc_step_4_dashboard_tab' => 'Onglet du plugin',
		'tr_melisdashboardplugincreator_desc_step_4_dashboard_tab_icon' => 'Icone de l\'onglet du plugin',
		'tr_melisdashboardplugincreator_desc_step_4_icon' => 'Icone',

		//Step5 Form
		'tr_melisdashboardplugincreator_title_step_5' => 'Finalisation',
		'tr_melisdashboardplugincreator_desc_step_5' => 'Cochez la case pour activer le plugin à la création.',
		'tr_melisdashboardplugincreator_activate_plugin_after_creation' => 'Activer le plugin à la création',
		'tr_melisdashboardplugincreator_activate_plugin_note' => '<b>Note </b>: Activer le plugin rechargera la plateforme',
		'tr_melisdashboardplugincreator_finalization_success_title' => 'Le plugin a été créé avec succès',
		'tr_melisdashboardplugincreator_finalization_success_desc_with_counter' => 'La plateforme va se recharger dans <strong><span id="dpc-restart-cd">5</span></strong>',
		'tr_melisdashboardplugincreator_finalization_success_desc' => 'Vous pouvez activer le plugin manuellement en rechargeant la page',
		'tr_melisdashboardplugincreator_execute_aadtnl_setup' => 'Derniers réglages en cours',
		'tr_melisdashboardplugincreator_please_wait' => 'Veuillez patienter',
		'tr_melisdashboardplugincreator_refreshing' => 'Rechargement'
    ];