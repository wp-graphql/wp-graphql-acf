<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;
use WPGraphQLGravityForms\Types\ConditionalLogic\ConditionalLogic;
use WPGraphQLGravityForms\Types\Enum\FieldVisibilityEnum;

/**
 * Gravity Forms field.
 *
 * @see https://docs.gravityforms.com/field-object/
 * @see https://docs.gravityforms.com/gf_field/
 */
abstract class Field implements Hookable, Type {
    /**
     * Get the global properties that apply to all GF field types.
     */
    protected function get_global_properties() {
        return [
            'adminLabel' => [
                'type'        => 'String',
                'description' => __( 'When specified, the value of this property will be used on the admin pages instead of the label. It is useful for fields with long labels.', 'wp-graphql-gravity-forms' ),
            ],
            'adminOnly' => [
                'type'        => 'Boolean',
                'description' => __( 'Determines if this field should only visible on the administration pages. A value of 1 will mark the field as admin only and will hide it from the public form. Useful for fields such as “status” that help with managing entries, but don’t apply to users filling out the form.', 'wp-graphql-gravity-forms' ),
            ],
            'allowsPrepopulate' => [
                'type'        => 'Boolean',
                'description' => __( 'Determines if the field’s value can be pre-populated dynamically. 1 to allow field to be pre-populated, 0 otherwise.', 'wp-graphql-gravity-forms' ),
            ],
            'conditionalLogic' => [
                'type'        => ConditionalLogic::TYPE,
                'description' => __( 'Controls the visibility of the field based on values selected by the user.', 'wp-graphql-gravity-forms' ),
            ],
            'cssClass' => [
                'type'        => 'String',
                'description' => __( 'String containing the custom CSS classes to be added to the <li> tag that contains the field. Useful for applying custom formatting to specific fields.', 'wp-graphql-gravity-forms' ),
            ],
            'cssClassList' => [
                'type'        => [ 'list_of' => 'String' ],
                'description' => __( 'Array of the custom CSS classes to be added to the <li> tag that contains the field. Useful for applying custom formatting to specific fields.', 'wp-graphql-gravity-forms' ),
            ],
            // @TODO: consider changing this to fieldId so that id can be used for the global Relay ID.
            'id' => [
                'type'        => 'Integer',
                'description' => __( 'Field ID.', 'wp-graphql-gravity-forms' ),
            ],
            'label' => [
                'type'        => 'String',
                'description' => __( 'Field label that will be displayed on the form and on the admin pages.', 'wp-graphql-gravity-forms' ),
            ],
            'type' => [
                'type'        => 'String',
                'description' => __( 'The type of field to be displayed.', 'wp-graphql-gravity-forms' ),
            ],
            'formId' => [
                'type'        => 'Integer',
                'description' => __( 'The ID of the form this field belongs to.', 'wp-graphql-gravity-forms' ),
            ],
            'visibility' => [
                'type'        => 'String',
                'description' => __( 'Field visibility. Possible values: visible, hidden, or administrative.', 'wp-graphql-gravity-forms' ),
            ],
        ];
    }
}
