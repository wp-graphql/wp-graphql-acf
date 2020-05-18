<?php

namespace WPGraphQLGravityForms\Types\Field\FieldValue;

use GF_Field;
use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;
use WPGraphQLGravityForms\Interfaces\FieldValue;
use WPGraphQLGravityForms\Types\Field\ChainedSelectField;

/**
 * Values for an individual Chained Select field.
 */
class ChainedSelectFieldValue implements Hookable, Type, FieldValue {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = ChainedSelectField::TYPE . 'Value';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Chained Select field values.', 'wp-graphql-gravity-forms' ),
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
        $values = array_map( function( $input ) use ( $entry ) {
            return $entry[ $input['id'] ] ?? '';
        }, $field->inputs );

        return compact( 'values' );
    }
}
