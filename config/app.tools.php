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
            'forms' => [
                'melisdashboardplugincreator_step1_form' => [
                    'attributes' => [
                        'name' => 'dashboard-plugin-creator-step-1',
                        'id' => 'dashboard-plugin-creator-step-1',
                        'class' => 'dashboard-plugin-creator-step-1',
                        'method' => 'POST',
                        'action' => '',
                    ],
                    'hydrator'  => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => [
                        [
                            'spec' => [
                                'type' => 'MelisText',
                                'name' => 'dpc_plugin_name',
                                'options' => [
                                    'label' => 'tr_melisdashboardplugincreator_dpc_plugin_name',
                                    'tooltip' => 'tr_melisdashboardplugincreator_dpc_plugin_name tooltip',
                                ],
                                'attributes' => [
                                    'id' => 'dpc_plugin_name',
                                    'class' => 'form-control',
                                    'value' => '',
                                    'placeholder' => '',
                                    'required' => 'required',
                                ],
                            ],
                        ],
                        [
                            'spec' => [
                                'type' => 'Laminas\Form\Element\Radio',
                                'name' => 'dpc_plugin_type',
                                'options' => [
                                    'disable_inarray_validator' => true,
                                    'label' => 'tr_melisdashboardplugincreator_dpc_plugin_type',
                                    'tooltip' => 'tr_melisdashboardplugincreator_dpc_plugin_type tooltip',
                                    'radio-button' => true,
                                    'label_options' => [
                                        'disable_html_escape' => true,
                                    ],
                                    'value_options' => [
                                        'single' => 'tr_melisdashboardplugincreator_single_tab',
                                        'multi' => 'tr_melisdashboardplugincreator_multi_tab',                                        
                                    ],
                                ],
                                'attributes' => [
                                    'id' => '',
                                    'class' => 'form-control',
                                    'value' => '',
                                    'required' => 'required',
                                ],
                            ]
                        ],
                        [
                            'spec' => [
                                'type' => 'MelisText',
                                'name' => 'dpc_tab_count',
                                'options' => [
                                    'label' => 'tr_melisdashboardplugincreator_tab_count',
                                    'tooltip' => '',
                                ],
                                'attributes' => [
                                    'id' => 'dpc_tab_count',
                                    'class' => 'form-control',
                                    'value' => '',
                                    'placeholder' => '',
                                    'required' => 'required'
                                ],
                            ],
                        ],            
                        [
                            'spec' => [
                                'type' => 'Radio',
                                'name' => 'dpc_plugin_destination',
                                'options' => [
                                    'disable_inarray_validator' => true,
                                    'label' => 'tr_melisdashboardplugincreator_dpc_plugin_destination',
                                    'tooltip' => 'tr_melisdashboardplugincreator_dpc_plugin_destination tooltip',
                                    'radio-button' => true,
                                    'label_options' => [
                                        'disable_html_escape' => true,
                                    ],
                                    'value_options' => [
                                        'new_module' => 'tr_melisdashboardplugincreator_destination_new_opt',
                                        'existing_module' => 'tr_melisdashboardplugincreator_destination_existing_opt',
                                    ],                                   
                                ],
                                'attributes' => [
                                    'class' => 'form-control',
                                    'value' => '',
                                    'required' => 'required',                                    
                                ],
                            ]
                        ],
                        [
                            'spec' => [
                                'type' => 'MelisText',
                                'name' => 'dpc_new_module_name',
                                'options' => [
                                    'label' => 'tr_melisdashboardplugincreator_dpc_new_module_name',
                                    'tooltip' => 'tr_melisdashboardplugincreator_dpc_new_module_name tooltip',                                   
                                ],
                                'attributes' => [
                                    'id' => 'dpc_new_module_name',
                                    'class' => 'form-control',
                                    'value' => '',
                                    'placeholder' => '',
                                    'required' => 'required',                                   
                                ],
                            ],
                        ],
                        [
                            'spec' => [
                                'type' => 'MelisDashboardPluginCreatorModuleSelect',
                                'name' => 'dpc_existing_module_name',
                                'options' => [
                                    'disable_inarray_validator' => true,
                                    'label' => 'tr_melisdashboardplugincreator_dpc_existing_module_name',
                                    'tooltip' => 'tr_melisdashboardplugincreator_dpc_existing_module_name tooltip',  
                                    'empty_option' => 'tr_melisdashboardplugincreator_dpc_existing_module_placeholder',                                 
                                ],
                                'attributes' => [
                                    'id' => 'dpc_existing_module_name',
                                    'class' => 'form-control',
                                    'value' => '',
                                    'placeholder' => '',
                                    'required' => 'required',                                    
                                ],
                            ],
                        ],
                    ],
                    'input_filter' => [
                        'dpc_plugin_name' => [
                            'name'     => 'dpc_plugin_name',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melisdashboardplugincreator_err_empty',
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'regex',
                                    'options' => [
                                        'pattern' => '/^[a-zA-Z\x7f-\xff][a-zA-Z\x7f-\xff]*$/',
                                        'messages' => [\Laminas\Validator\Regex::NOT_MATCH => 'tr_melisdashboardplugincreator_err_invalid_name'],
                                        'encoding' => 'UTF-8',
                                    ],
                                ],
                                [
                                    'name'    => 'StringLength',
                                    'options' => [
                                        'encoding' => 'UTF-8',
                                        'max'      => 50,
                                        'messages' => [
                                            \Laminas\Validator\StringLength::TOO_LONG => 'tr_melisdashboardplugincreator_err_long_50',
                                        ],
                                    ],
                                ], 
                               
                            ],
                            'filters'  => [                              
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'dpc_plugin_type' => [
                            'name'     => 'dpc_plugin_type',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melisdashboardplugincreator_err_empty',
                                        ],
                                    ],
                                ]                              
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'dpc_tab_count' => [
                            'name'     => 'dpc_tab_count',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melisdashboardplugincreator_err_empty',
                                        ],
                                    ],
                                ],    
                                [
                                'name' => 'IsInt',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\I18n\Validator\IsInt::NOT_INT  => 'tr_melisdashboardplugincreator_integer_only'                                            
                                        ],                                                                       
                                    ],
                                ],                    
                                [
                                    'name' => 'Between',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\Between::NOT_BETWEEN => 'tr_melisdashboardplugincreator_value_must_be_between_2_to_25',
                                            'valueNotNumeric' => 'tr_melisdashboardplugincreator_value_must_be_between_2_to_25',
                                        ],
                                        'min' => 2,
                                        'max' => 25                                  
                                    ],
                                ],
                                                         
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'dpc_plugin_destination' => [
                            'name'     => 'dpc_plugin_destination',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melisdashboardplugincreator_err_empty',
                                        ],
                                    ],
                                ]                              
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'dpc_new_module_name' => [
                            'name'     => 'dpc_new_module_name',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'regex',
                                    'options' => [
                                        'pattern' => '/^[a-zA-Z\x7f-\xff][a-zA-Z\x7f-\xff]*$/',
                                        'messages' => [\Laminas\Validator\Regex::NOT_MATCH => 'tr_melisdashboardplugincreator_err_invalid_name'],
                                        'encoding' => 'UTF-8',
                                    ],
                                ],   
                                [
                                    'name'    => 'StringLength',
                                    'options' => [
                                        'encoding' => 'UTF-8',
                                        'max'      => 50,
                                        'messages' => [
                                            \Laminas\Validator\StringLength::TOO_LONG => 'tr_melisdashboardplugincreator_err_long_50',
                                        ],
                                    ],
                                ],                             
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melisdashboardplugincreator_err_empty',
                                        ],
                                    ],
                                ]                              
                            ],
                            'filters'  => [
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'dpc_existing_module_name' => [
                            'name'     => 'dpc_existing_module_name',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melisdashboardplugincreator_err_empty',
                                        ],
                                    ],
                                ]                              
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                    ],
                ],

                //language form
                'melisdashboardplugincreator_step2_form1' => [
                    'attributes' => [
                        'name' => 'dashboard-plugin-creator-step-2-language-form',
                        'class' => 'dashboard-plugin-creator-step-2',
                        'method' => 'POST',
                        'action' => '',
                    ],
                    'hydrator'  => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => [                        
                        [
                            'spec' => [
                                'name' => 'dpc_plugin_title',
                                'type' => 'MelisText',
                                'options' => [
                                    'label' => 'tr_melisdashboardplugincreator_dpc_plugin_title',
                                    'tooltip' => 'tr_melisdashboardplugincreator_dpc_plugin_title tooltip',
                                ],
                                'attributes' => [
                                    'value' => '',
                                    'placeholder' => '',
                                    'required' => 'required',
                                ],
                            ],
                        ],
                        [
                            'spec' => [
                                'name' => 'dpc_plugin_desc',
                                'type' => 'Textarea',
                                'options' => [
                                    'label' => 'tr_melisdashboardplugincreator_dpc_plugin_desc',
                                    'tooltip' => 'tr_melisdashboardplugincreator_dpc_plugin_desc tooltip',
                                ],
                                'attributes' => [
                                    'value' => '',
                                    'placeholder' => '',
                                    'class' => 'form-control',
                                    'rows' => 4
                                ],
                            ],
                        ],  
                        [
                            'spec' => [
                                'name' => 'dpc_lang_local',
                                'type' => 'Hidden',
                                'id' => ''
                            ],
                        ],                    
                    ],
                    'input_filter' => [
                        'dpc_plugin_title' => [
                            'name'     => 'dpc_plugin_title',
                            'required' => true,
                            'validators' => [
                                [
                                    'name'    => 'StringLength',
                                    'options' => [
                                        'encoding' => 'UTF-8',
                                        'max'      => 100,
                                        'messages' => [
                                            \Laminas\Validator\StringLength::TOO_LONG => 'tr_melisdashboardplugincreator_err_long_100',
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melisdashboardplugincreator_err_empty',
                                        ],
                                    ],
                                ],
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'dpc_plugin_desc' => [
                            'name'     => 'dpc_plugin_desc',
                            'required' => false,
                            'validators' => [
                                [
                                    'name'    => 'StringLength',
                                    'options' => [
                                        'encoding' => 'UTF-8',
                                        'max'      => 250,
                                        'messages' => [
                                            \Laminas\Validator\StringLength::TOO_LONG => 'tr_melisdashboardplugincreator_err_long_250',
                                        ],
                                    ],
                                ]
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                    ],
                ],

               //plugin thumbnail upload form
                'melisdashboardplugincreator_step2_form2' => [
                    'attributes' => array(
                        'name' => 'dashboard-plugin-creator-thumbnail-upload-form',
                        'id' => 'id-dashboard-plugin-creator-thumbnail-upload-form',
                        'class' => 'dashboard-plugin-creator-step-2',
                        'method' => 'POST',
                        'action' => '',
                    ),
                    'hydrator'  => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => [
                        [
                            'spec' => [
                                'name' => 'dpc_plugin_upload_thumbnail',
                                'type' => 'file',
                                'options' => [
                                    'label' => 'tr_melisdashboardplugincreator_upload_thumbnail',
                                    'tooltip' => 'tr_melisdashboardplugincreator_upload_thumbnail tooltip',
                                ],
                                'attributes' => [
                                    'id' => 'dpc_plugin_upload_thumbnail',
                                    'accept' => ".gif,.jpg,.jpeg,.png",
                                    'data-classButton' => 'btn btn-primary',
                                    'class' => 'upload-category-media-image form-control',
                                    'required' => 'required',    
                                ]
                            ]
                        ],
                    ],
                    'input_filter' => [                                          
                    ],
                ],

                //language form
                'melisdashboardplugincreator_step3_form1' => [
                    'attributes' => [
                        'name' => 'dashboard-plugin-creator-step-3-language-form',
                        'class' => 'dashboard-plugin-creator-step-3',
                        'method' => 'POST',
                        'action' => '',
                    ],
                    'hydrator'  => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => [                        
                        [
                            'spec' => [
                                'name' => 'dpc_plugin_title',
                                'type' => 'MelisText',
                                'options' => [
                                    'label' => 'tr_melisdashboardplugincreator_dpc_plugin_title',
                                    'tooltip' => 'tr_melisdashboardplugincreator_dpc_plugin_title tooltip',
                                ],
                                'attributes' => [
                                    'value' => '',
                                    'placeholder' => '',
                                    'required' => 'required',
                                ],
                            ],
                        ],     
                        [
                            'spec' => [
                                'name' => 'dpc_lang_local',
                                'type' => 'Hidden',
                                'id' => ''
                            ],
                        ],                                      
                    ],
                    'input_filter' => [
                        'dpc_plugin_title' => [
                            'name'     => 'dpc_plugin_title',
                            'required' => true,
                            'validators' => [
                                [
                                    'name'    => 'StringLength',
                                    'options' => [
                                        'encoding' => 'UTF-8',
                                        'max'      => 100,
                                        'messages' => [
                                            \Laminas\Validator\StringLength::TOO_LONG => 'tr_melisdashboardplugincreator_err_long_100',
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melisdashboardplugincreator_err_empty',
                                        ],
                                    ],
                                ],
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],                        
                    ],
                ],

                //icon form
                'melisdashboardplugincreator_step3_form2' => [
                    'attributes' => [
                        'name' => 'dashboard-plugin-creator-step-3-icon-form',
                        'class' => 'dashboard-plugin-creator-step-3',
                        'method' => 'POST',
                        'action' => '',
                    ],
                    'hydrator'  => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => [                     
                        [
                            'spec' => [
                                'name' => 'dpc_plugin_icon',
                                'type' => 'Radio',
                                'options' => [
                                    'disable_inarray_validator' => true,
                                    'label' => 'tr_melisdashboardplugincreator_plugin_icon',
                                    'tooltip' => 'tr_melisdashboardplugincreator_plugin_icon tooltip',
                                    'value_options' => [
                                        'fa-bar-chart-o' => 'fa-bar-chart-o',
                                        'fa-calendar' => 'fa-calendar',                                                                       
                                        'fa-warning' => 'fa-warning',
                                        'fa-table' => 'fa-table',
                                        'fa-cog' => 'fa-cog',
                                        'fa-comment' => 'fa-comment',   
                                        'fa-chain' => 'fa-chain',    
                                        'fa-map-marker' => 'fa-map-marker',                                       
                                        'fa-trash-o' => 'fa-trash-o',
                                        'fa-filter' => 'fa-filter',
                                        'fa-search' => 'fa-search',
                                        'fa-table' => 'fa-table',
                                        'fa-tag' => 'fa-tag',
                                        'fa-bookmark' => 'fa-bookmark',
                                        'fa-group' => 'fa-group',
                                        'fa-bell' => 'fa-bell',
                                        'fa-clock-o' => 'fa-clock-o',
                                        'fa-wrench' => 'fa-wrench',
                                        'fa-ban' => 'fa-ban',
                                        'fa-share' => 'fa-share',
                                        'fa-file' => 'fa-file',
                                        'fa-list' => 'fa-list',
                                        'fa-heart' => 'fa-heart',
                                        'fa-inbox' => 'fa-inbox',
                                        'fa-envelope' => 'fa-envelope'
                                    ],    
                                ],
                                'attributes' => [
                                    'value' => '',
                                    'placeholder' => '',
                                    'required' => 'required',
                                    'class' => 'form-control'
                                ],
                            ],
                        ],                                        
                    ],
                    'input_filter' => [
                        'dpc_plugin_icon' => [
                            'name'     => 'dpc_plugin_icon',
                            'required' => true,
                            'validators' => [                             
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melisdashboardplugincreator_err_empty',
                                        ],
                                    ],
                                ],
                            ],                            
                        ],                      
                    ],
                ],

                'melisdashboardplugincreator_step5_form' => [
                    'attributes' => [
                        'name' => 'dashboard-plugin-creator-step-5',
                        'id' => 'dashboard-plugin-creator-step-5',
                        'class' => 'dashboard-plugin-creator-step-5',
                        'method' => 'POST',
                        'action' => '',
                    ],
                    'hydrator'  => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => [
                        [
                            'spec' => [
                                'name' => 'dpc_activate_plugin',
                                'type' => 'Checkbox',
                                'options' => [
                                    'label' => 'tr_melisdashboardplugincreator_activate_plugin_after_creation',
                                    'tooltip' => '',                                                                 
                                    'disable_inarray_validator' => true,
                                ],
                                'attributes' => [
                                    'id' => 'dpc_activate_plugin',
                                    'class' => 'hidden',
                                    'required' => '',    
                                    'value' => ''                                
                                ],
                            ],
                        ],                       
                    ],
                                    
                ],
            ]
        ]
    ]
];

