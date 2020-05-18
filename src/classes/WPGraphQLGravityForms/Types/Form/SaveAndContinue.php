<?php

namespace WPGraphQLGravityForms\Types\Form;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;
use WPGraphQLGravityForms\Types\Button\Button;

/**
 * Form "Save and Continue" data.
 */
class SaveAndContinue implements Hookable, Type {
    const TYPE = 'SaveAndContinue';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms form Save and Continue data.', 'wp-graphql-gravity-forms' ),
            'fields'      => [
                'enabled'   => [
                    'type'        => 'Boolean',
                    'description' => __( 'Whether the Save And Continue feature is enabled.', 'wp-graphql-gravity-forms' ),
                ],
                'button'   => [
                    'type'        => Button::TYPE,
                    'description' => __( 'Contains the button text. Only applicable when type is set to text.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
