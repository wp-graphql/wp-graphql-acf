<?php

namespace WPGraphQLGravityForms\Types\Enum;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Enum;

class FieldFiltersOperatorInputEnum implements Hookable, Enum {
    const TYPE = 'FieldFiltersOperatorInputEnum';

    // Individual elements.
    const IN           = 'in';
    const NOT_IN       = 'not in';
    const CONTAINS     = 'contains';
    const GREATER_THAN = '>';
    const LESS_THAN    = '<';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register' ] );
    }

    public function register() {
        register_graphql_enum_type(
            self::TYPE,
            [
                'description' => __( 'The operator to use for filtering.', 'harness' ),
                'values'      => [
                    'IN' => [
                        'description' => __( 'Find field values that match those in the values array (default).', 'harness' ),
                        'value'       => self::IN,
                    ],
                    'NOT_IN' => [
                        'description' => __( 'Find field values that do NOT match those in the values array.', 'harness' ),
                        'value'       => self::NOT_IN,
                    ],
                    'CONTAINS' => [
                        'description' => __( 'Find field values that contain the value in the values array. Only the first value in the values array will be used; any others will be disregarded.', 'harness' ),
                        'value'       => self::CONTAINS,
                    ],
                    'GREATER_THAN' => [
                        'description' => __( 'Find field values that are greater than the value in the values array. Only the first value in the values array will be used; any others will be disregarded.', 'harness' ),
                        'value'       => self::GREATER_THAN,
                    ],
                    'LESS_THAN' => [
                        'description' => __( 'Find field values that are less than the value in the values array. Only the first value in the values array will be used; any others will be disregarded.', 'harness' ),
                        'value'       => self::LESS_THAN,
                    ],
                ],
            ]
        );
    }
}
