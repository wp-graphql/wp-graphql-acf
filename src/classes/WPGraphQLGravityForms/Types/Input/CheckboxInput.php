<?php

namespace WPGraphQLGravityForms\Types\Input;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\InputType;

/**
 * Input fields for a single checkbox.
 */
class CheckboxInput implements Hookable, InputType {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'CheckboxInput';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_input_type' ] );
    }

    public function register_input_type() {
        register_graphql_input_type( self::TYPE, [
            'description' => __( 'Input fields for a single checkbox.', 'wp-graphql-gravity-forms' ),
            'fields'      => [
                'inputId' => [
                    'type'        => 'Float',
                    'description' => __( 'Input ID.', 'wp-graphql-gravity-forms' ),
                ],
                'value' => [
                    'type'        => 'String',
                    'description' => __( 'Input value', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
