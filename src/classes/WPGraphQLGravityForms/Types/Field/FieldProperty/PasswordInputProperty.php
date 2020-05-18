<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 * An individual input in the Password field 'inputs' property.
 *
 * @see https://docs.gravityforms.com/gf_field_password/
 */
class PasswordInputProperty implements Hookable, Type {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'PasswordInputProperty';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __('Gravity Forms input property.', 'wp-graphql-gravity-forms'),
            'fields'      => [
                'id' => [
                    'type'        => 'Integer',
                    'description' => __('The id of the input field.', 'wp-graphql-gravity-forms'),
                ],
                'label' => [
                    'type'        => 'String',
                    'description' => __('The label for the input.', 'wp-graphql-gravity-forms'),
                ],
                'customLabel' => [
                    'type'        => 'String',
                    'description' => __('The custom label for the input. When set, this is used in place of the label.', 'wp-graphql-gravity-forms'),
                ],
                'placeholder' => [
                    'type'        => 'String',
                    'description' => __('Placeholder text to give the user a hint on how to fill out the field. This is not submitted with the form.', 'wp-graphql-gravity-forms'),
                ],
            ],
        ] );
    }
}
