<?php

namespace WPGraphQLGravityForms\Types\FieldError;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 *  Field error.
 */
class FieldError implements Hookable, Type {
    const TYPE = 'FieldError';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Field error.', 'wp-graphql-gravity-forms' ),
            'fields' => [
                'message' => [
                    'type'        => 'String',
                    'description' => __( 'Error message.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
