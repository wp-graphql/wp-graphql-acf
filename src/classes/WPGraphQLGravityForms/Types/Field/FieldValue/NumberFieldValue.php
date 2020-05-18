<?php

namespace WPGraphQLGravityForms\Types\Field\FieldValue;

use GF_Field;
use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;
use WPGraphQLGravityForms\Interfaces\FieldValue;
use WPGraphQLGravityForms\Types\Field\NumberField;

/**
 * Value for a number field.
 */
class NumberFieldValue implements Hookable, Type, FieldValue {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = NumberField::TYPE . 'Value';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Number field value.', 'wp-graphql-gravity-forms' ),
            'fields'      => [
                'value' => [
                    'type'        => 'String',
                    'description' => __( 'The value.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }

    /**
     * Get the field value.
     *
     * @param array    $entry Gravity Forms entry.
     * @param GF_Field $field Gravity Forms field.
     *
     * @return array Entry field value.
     */
    public static function get( array $entry, GF_Field $field ) : array {
        return [
            'value' => (string) $entry[ $field['id'] ],
        ];
    }
}
