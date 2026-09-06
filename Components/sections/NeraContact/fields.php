<?php
namespace Nera\Components\NeraContact;

if (!defined('ABSPATH')) {
    exit;
}

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_NeraContact',
        'name'       => 'NeraContact',
        // Collision-safe: parent monolith is still labelled "Contact".
        'label'      => __('Contact — Details', 'scoop-comps'),
        'display'    => 'block',
        'sub_fields' => [
            ['key' => 'field_pc_NeraContact_tab_info', 'label' => 'Contact Info', 'name' => 'tab_info', 'type' => 'tab'],
            [
                'key'           => 'field_pc_NeraContact_get_in_touch_heading',
                'label'         => __('Get in Touch Heading', 'scoop-comps'),
                'name'          => 'get_in_touch_heading',
                'type'          => 'text',
                'default_value' => 'Get in Touch',
            ],
            [
                'key'           => 'field_pc_NeraContact_get_in_touch_description',
                'label'         => __('Get in Touch Description', 'scoop-comps'),
                'name'          => 'get_in_touch_description',
                'type'          => 'textarea',
                'default_value' => "Have questions about our competitions? We're here to help.",
            ],
            [
                'key'           => 'field_pc_NeraContact_show_contact_cards',
                'label'         => __('Show Contact Cards', 'scoop-comps'),
                'name'          => 'show_contact_cards',
                'type'          => 'true_false',
                'default_value' => 1,
                'ui'            => 1,
            ],
            [
                'key'               => 'field_pc_NeraContact_contact_address',
                'label'             => __('Address', 'scoop-comps'),
                'name'              => 'contact_address',
                'type'              => 'textarea',
                'default_value'     => '123 Innovation Blvd, Tech City, TC 12345',
                'conditional_logic' => [[['field' => 'field_pc_NeraContact_show_contact_cards', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key'               => 'field_pc_NeraContact_contact_email',
                'label'             => __('Email', 'scoop-comps'),
                'name'              => 'contact_email',
                'type'              => 'email',
                'default_value'     => 'support@competition.com',
                'conditional_logic' => [[['field' => 'field_pc_NeraContact_show_contact_cards', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key'               => 'field_pc_NeraContact_contact_phone',
                'label'             => __('Phone', 'scoop-comps'),
                'name'              => 'contact_phone',
                'type'              => 'text',
                'default_value'     => '+1 (555) 012-3456',
                'conditional_logic' => [[['field' => 'field_pc_NeraContact_show_contact_cards', 'operator' => '==', 'value' => '1']]],
            ],
            ['key' => 'field_pc_NeraContact_tab_form', 'label' => 'Form', 'name' => 'tab_form', 'type' => 'tab'],
            [
                'key'           => 'field_pc_NeraContact_form_heading',
                'label'         => __('Form Heading', 'scoop-comps'),
                'name'          => 'form_heading',
                'type'          => 'text',
                'default_value' => 'Send Us a Message',
            ],
            [
                'key'           => 'field_pc_NeraContact_form_description',
                'label'         => __('Form Description', 'scoop-comps'),
                'name'          => 'form_description',
                'type'          => 'textarea',
                'default_value' => '',
            ],
            [
                'key'           => 'field_pc_NeraContact_fluent_form_id',
                'label'         => __('Fluent Form ID', 'scoop-comps'),
                'name'          => 'fluent_form_id',
                'type'          => 'number',
                'default_value' => 0,
                'min'           => 0,
            ],
        ],
    ];
}
