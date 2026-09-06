<?php
/**
 * ACF — Footer brand + contact fields (child theme)
 *
 * Adds a SEPARATE field group to the parent's existing Footer Settings screen
 * (Theme Settings > Footer, i.e. `acf-options-footer`) rather than filtering the
 * parent's `group_neracompetitions_footer`. A separate group cannot be clobbered
 * when the parent theme auto-updates from GitHub, and it needs no knowledge of
 * the parent's internal field keys.
 *
 * `menu_order => -1` places this group ABOVE the parent's group on that screen,
 * so these fields read before the legal/copyright fields.
 *
 * The options page itself is registered by the parent
 * (inc/acf/footer/acf-footer.php). We deliberately do not re-register it — if the
 * parent ever stops doing so, these fields simply have nowhere to render, which
 * is a visible failure rather than a silent duplicate menu.
 *
 * Field names are prefixed `scoop_footer_*` so they can never collide with a
 * field the parent adds later.
 *
 * NOTE ON PLACEHOLDERS: `placeholder` is admin-only grey hint text and never
 * reaches the front end. Only `scoop_footer_contact_heading` has a real front-end
 * fallback (in the template), because it is a structural label rather than copy —
 * a phone number under no heading reads as broken. Brand text deliberately has NO
 * fallback: inventing marketing copy on a live site is how the inherited lorem
 * ipsum got there in the first place.
 *
 * Consumed by: template-parts/footer.php (child fork).
 *
 * @package Scoop_Competitions_Child
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    /**
     * Social networks rendered in the brand column. Keyed by field suffix so the
     * field definitions and the template's lookup stay in step.
     *
     * Placeholders are prefixed "e.g." on purpose. A placeholder is visually
     * identical to a saved value, so realistic-looking example URLs here read as
     * already-populated fields and hide the fact that nothing is set.
     */
    $social_networks = [
        'facebook'  => [__('Facebook URL', 'scoop-comps'),    'e.g. https://facebook.com/yourpage'],
        'instagram' => [__('Instagram URL', 'scoop-comps'),   'e.g. https://instagram.com/yourpage'],
        'twitter'   => [__('X (Twitter) URL', 'scoop-comps'), 'e.g. https://x.com/yourpage'],
        'youtube'   => [__('YouTube URL', 'scoop-comps'),     'e.g. https://youtube.com/@yourchannel'],
    ];

    $fields = [
        [
            'key'           => 'field_scoop_footer_brand_text',
            'label'         => __('Brand Text', 'scoop-comps'),
            'name'          => 'scoop_footer_brand_text',
            'type'          => 'textarea',
            'instructions'  => __('Short paragraph shown under the footer logo. Leave empty to show the logo alone — nothing is substituted.', 'scoop-comps'),
            'required'      => 0,
            'wrapper'       => ['width' => '100', 'class' => '', 'id' => ''],
            'default_value' => '',
            'placeholder'   => __('Genuinely better odds on prizes worth winning. Every draw livestreamed, every ticket count published up front.', 'scoop-comps'),
            'maxlength'     => '',
            'rows'          => 4,
            'new_lines'     => 'wpautop',
        ],
    ];

    foreach ($social_networks as $network => [$label, $placeholder]) {
        $fields[] = [
            'key'          => 'field_scoop_footer_' . $network,
            'label'        => $label,
            'name'         => 'scoop_footer_' . $network,
            'type'         => 'url',
            'instructions' => __('Leave empty to hide the icon.', 'scoop-comps'),
            'required'     => 0,
            'wrapper'      => ['width' => '50', 'class' => '', 'id' => ''],
            'placeholder'  => $placeholder,
        ];
    }

    // Fourth footer column. Replaces the `footer-4` widget area, which the child
    // fork no longer renders.
    $fields[] = [
        'key'           => 'field_scoop_footer_contact_heading',
        'label'         => __('Contact Heading', 'scoop-comps'),
        'name'          => 'scoop_footer_contact_heading',
        'type'          => 'text',
        'instructions'  => __('Heading for the fourth footer column. Falls back to "Contact Us" when empty.', 'scoop-comps'),
        'required'      => 0,
        'wrapper'       => ['width' => '100', 'class' => '', 'id' => ''],
        'default_value' => '',
        'placeholder'   => __('Contact Us', 'scoop-comps'),
    ];

    $fields[] = [
        'key'          => 'field_scoop_footer_contact_phone',
        'label'        => __('Contact Phone', 'scoop-comps'),
        'name'         => 'scoop_footer_contact_phone',
        'type'         => 'text',
        'instructions' => __('Shown as a tap-to-call link. Leave empty to hide.', 'scoop-comps'),
        'required'     => 0,
        'wrapper'      => ['width' => '50', 'class' => '', 'id' => ''],
        'placeholder'  => __('01234 567890', 'scoop-comps'),
    ];

    $fields[] = [
        'key'          => 'field_scoop_footer_contact_email',
        'label'        => __('Contact Email', 'scoop-comps'),
        'name'         => 'scoop_footer_contact_email',
        'type'         => 'email',
        'instructions' => __('Shown as a mailto link, obfuscated against scrapers. Leave empty to hide.', 'scoop-comps'),
        'required'     => 0,
        'wrapper'      => ['width' => '50', 'class' => '', 'id' => ''],
        'placeholder'  => 'hello@getthescoop.co.uk',
    ];

    acf_add_local_field_group([
        'key'         => 'group_scoop_footer_brand',
        'title'       => __('Footer Brand & Contact', 'scoop-comps'),
        'description' => __('First and fourth footer columns. The logo itself comes from Appearance > Customize > Site Identity.', 'scoop-comps'),
        'fields'      => $fields,
        'location'    => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'acf-options-footer',
                ],
            ],
        ],
        'menu_order'            => -1,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
    ]);
});
