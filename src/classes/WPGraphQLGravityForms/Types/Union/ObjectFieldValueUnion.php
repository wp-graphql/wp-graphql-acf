<?php

namespace WPGraphQLGravityForms\Types\Union;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\TypeRegistry;
use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;
use WPGraphQLGravityForms\Types\Field\Field;

/**
 * Union between an object and a Gravity Forms field value.
 */
class ObjectFieldValueUnion implements Hookable, Type {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'ObjectFieldValueUnion';

    /**
     * WPGraphQL for Gravity Forms plugin's class instances.
     *
     * @var array
     */
    private $instances;

    /**
     * @param array WPGraphQL for Gravity Forms plugin's class instances.
     */
    public function __construct( array $instances ) {
        $this->instances = $instances;
    }

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ], 11 );
    }

    public function register_type( $type_registry ) {
        $field_value_types = $this->get_field_value_types();
        register_graphql_union_type( self::TYPE, [
            'typeNames'   => array_unique( array_values( $field_value_types ) ),
            'resolveType' => function( $object ) use ( $field_value_types, $type_registry ) {
                if ( isset( $field_value_types[ $object['type'] ] ) ) {
                    return $type_registry->get_type( $field_value_types[ $object['type'] ] );
                }

                return null;
            },
        ] );
    }


    /**
     * Get field types and their related field value types.
     * Example: [ 'AddressField' => 'AddressFieldValues' ]
     *
     * @return array Field value types.
     */
    private function get_field_value_types() : array {
        $fields_with_value_types = array_filter( $this->instances, function( $instance ) {
            return $instance instanceof Field && $this->does_field_have_value_type( $instance );
        } );

        return array_reduce( $fields_with_value_types, function( $value_types, $field ) {
            $value_types[ $field::TYPE ] = $field::TYPE . 'Value';

            return $value_types;
        }, [] );
    }

    /**
     * @param Field $field Gravity Forms field.
     *
     * @return bool Whether $field has a corresponding field value type.
     */
    private function does_field_have_value_type( Field $field ) : bool {
        return class_exists( 'WPGraphQLGravityForms\Types\Field\FieldValue\\' . $field::TYPE . 'Value' );
    }
}
