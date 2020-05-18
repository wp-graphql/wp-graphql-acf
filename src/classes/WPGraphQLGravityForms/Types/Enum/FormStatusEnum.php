<?php

namespace WPGraphQLGravityForms\Types\Enum;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Enum;

class FormStatusEnum implements Hookable, Enum {
    const TYPE = 'FormStatusEnum';

    // Individual elements.
    const ACTIVE           = 'ACTIVE';
    const INACTIVE         = 'INACTIVE';
    const TRASHED          = 'TRASHED';
    const INACTIVE_TRASHED = 'INACTIVE_TRASHED';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register' ] );
    }

    public function register() {
        register_graphql_enum_type(
            self::TYPE,
            [
                'description' => __( 'Status of forms to get. Default is ACTIVE.', 'harness' ),
                'values'      => [
                    self::ACTIVE => [
                        'description' => __( 'Active forms (default).', 'harness' ),
                        'value'       => self::ACTIVE,
                    ],
                    self::INACTIVE => [
                        'description' => __( 'Inactive forms', 'harness' ),
                        'value'       => self::INACTIVE,
                    ],
                    self::TRASHED => [
                        'description' => __( 'Active forms in the trash.', 'harness' ),
                        'value'       => self::TRASHED,
                    ],
                    self::INACTIVE_TRASHED => [
                        'description' => __( 'Inactive forms in the trash.', 'harness' ),
                        'value'       => self::INACTIVE_TRASHED,
                    ],
                ],
            ]
        );
    }
}
