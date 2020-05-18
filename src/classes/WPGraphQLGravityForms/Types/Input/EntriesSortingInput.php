<?php

namespace WPGraphQLGravityForms\Types\Input;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\InputType;

/**
 * Sorting input type for Entries queries.
 */
class EntriesSortingInput implements Hookable, InputType {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'EntriesSortingInput';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_input_type' ] );
    }

    public function register_input_type() {
        register_graphql_input_type( self::TYPE, [
            'description' => __('Sorting input fields for Entries queries.', 'wp-graphql-gravity-forms'),
            'fields'      => [
                'key' => [
                    'type'        => 'String',
                    'description' => __( 'The key of the field to sort by.', 'wp-graphql-gravity-forms' ),
                ],
                // @TODO: Convert to enum.
                'direction' => [
                    'type'        => 'String',
                    'description' => __( 'The sorting direction. Possible values: DESC for descending (default), or ASC for ascending.', 'wp-graphql-gravity-forms' ),
                ],
                'isNumeric' => [
                    'type'        => 'Boolean',
                    'description' => __( 'Whether the sorting field\'s values are numeric.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
