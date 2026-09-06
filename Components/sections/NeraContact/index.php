<?php
namespace Nera\Components\NeraContact;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contact body — address / cards + Fluent Form only (no hero, no &lt;main&gt;).
 *
 * @param array $args
 * @return array{
 *   get_in_touch_heading: string,
 *   get_in_touch_description: string,
 *   show_contact_cards: bool,
 *   contact_address: string,
 *   contact_email: string,
 *   contact_phone: string,
 *   form_heading: string,
 *   form_description: string,
 *   fluent_form_id: int,
 *   facebook_url: string,
 *   twitter_url: string,
 *   instagram_url: string,
 *   linkedin_url: string,
 *   address_html: string,
 *   phone_digits: string,
 *   can_edit: bool,
 *   edit_link: string,
 *   form_html: string,
 *   form_plugin_missing: bool,
 * }
 */
function get_data(array $args = []): array
{
    $get_in_touch_heading = nera_component_field($args, 'get_in_touch_heading', 'get_in_touch_heading', 'Get in Touch');
    $get_in_touch_description = nera_component_field(
        $args,
        'get_in_touch_description',
        'get_in_touch_description',
        "Have questions about our competitions? We're here to help."
    );
    $show_contact_cards = (bool) nera_component_field($args, 'show_contact_cards', 'show_contact_cards', true);
    $contact_address = nera_component_field($args, 'contact_address', 'contact_address', '123 Innovation Blvd, Tech City, TC 12345');
    $contact_email = nera_component_field($args, 'contact_email', 'contact_email', 'support@competition.com');
    $contact_phone = nera_component_field($args, 'contact_phone', 'contact_phone', '+1 (555) 012-3456');
    $form_heading = nera_component_field($args, 'form_heading', 'form_heading', 'Send Us a Message');
    $form_description = nera_component_field($args, 'form_description', 'form_description', '');
    $fluent_form_id = nera_component_field($args, 'fluent_form_id', 'fluent_form_id', 0);

    $facebook_url  = get_theme_mod('nera_facebook_url', '');
    $twitter_url   = get_theme_mod('nera_twitter_url', '');
    $instagram_url = get_theme_mod('nera_instagram_url', '');
    $linkedin_url  = get_theme_mod('nera_linkedin_url', '');

    $address_html = nl2br(esc_html((string) $contact_address));
    $phone_digits = preg_replace('/[^0-9+]/', '', (string) $contact_phone);
    $can_edit     = current_user_can('edit_pages');
    $edit_link    = (string) get_edit_post_link();

    $fluent_form_id = (int) $fluent_form_id;

    if ($fluent_form_id > 0 && shortcode_exists('fluentform')) {
        $form_html = do_shortcode('[fluentform id="' . absint($fluent_form_id) . '"]');
    } else {
        $form_html = '';
    }

    $form_plugin_missing = $fluent_form_id > 0 && !shortcode_exists('fluentform');

    return compact(
        'get_in_touch_heading',
        'get_in_touch_description',
        'show_contact_cards',
        'contact_address',
        'contact_email',
        'contact_phone',
        'form_heading',
        'form_description',
        'fluent_form_id',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'address_html',
        'phone_digits',
        'can_edit',
        'edit_link',
        'form_html',
        'form_plugin_missing'
    );
}
