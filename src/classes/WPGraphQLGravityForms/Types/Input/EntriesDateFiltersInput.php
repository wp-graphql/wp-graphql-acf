<?php

namespace WPGraphQLGravityForms\Types\Input;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\InputType;

/**
 * Date Filters input type for Entries queries.
 */
class EntriesDateFiltersInput implements Hookable, InputType {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'EntriesDateFiltersInput';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_input_type' ] );
    }

    public function register_input_type() {
        register_graphql_input_type( self::TYPE, [
            'description' => __('Date Filters input fields for Entries queries.', 'wp-graphql-gravity-forms'),
            'fields'      => [
                'startDate' => [
                    'type'        => 'String',
                    'description' => __( 'Start date in Y-m-d H:i:s format.', 'wp-graphql-gravity-forms' ),
                ],
                'endDate' => [
                    'type'        => 'String',
                    'description' => __( 'End date in Y-m-d H:i:s format.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
