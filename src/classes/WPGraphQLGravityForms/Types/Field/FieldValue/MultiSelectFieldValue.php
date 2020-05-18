<?php

namespace WPGraphQLGravityForms\Types\Field\FieldValue;

use GF_Field;
use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;
use WPGraphQLGravityForms\Interfaces\FieldValue;
use WPGraphQLGravityForms\Types\Field\MultiSelectField;

/**
 * Values for an individual MultiSelect field.
 */
class MultiSelectFieldValue implements Hookable, Type, FieldValue {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = MultiSelectField::TYPE . 'Value';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Multiselect field values.', 'wp-graphql-gravity-forms' ),
            'fields'      => [
                'values' => [
                    'type'        => [ 'list_of' => 'String' ],
                    'description' => __( 'Field values.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }

    /**
     * Get the field values.
     *
     * @param array    $entry Gravity Forms entry.
     * @param GF_Field $field Gravity Forms field.
     *
     * @return array Entry field values.
     */
    public static function get( array $entry, GF_Field $field ) : array {
        return [
            'values' => json_decode( $entry[ $field['id'] ], true ),
        ];
    }
}
