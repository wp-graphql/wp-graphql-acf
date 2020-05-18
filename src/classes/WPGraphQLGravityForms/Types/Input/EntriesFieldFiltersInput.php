<?php

namespace WPGraphQLGravityForms\Types\Input;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\InputType;
use WPGraphQLGravityForms\Types\Enum\FieldFiltersOperatorInputEnum;

/**
 * Field Filters input type for Entries queries.
 */
class EntriesFieldFiltersInput implements Hookable, InputType {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'EntriesFieldFiltersInput';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_input_type' ] );
    }

    public function register_input_type() {
        register_graphql_input_type( self::TYPE, [
            'description' => __( 'Field Filters input fields for Entries queries.', 'wp-graphql-gravity-forms' ),
            'fields'      => [
                'key' => [
                    'type'        => 'String',
                    'description' => __( 'The ID of the field to filter by. Use "0" to search all keys. You can also use the names of the columns in Gravity Forms\' database table for entries, such as "date_created", "is_read, "created_by", etc.', 'wp-graphql-gravity-forms' ),
                ],
                'operator' => [
                    'type'        => FieldFiltersOperatorInputEnum::TYPE,
                    'description' => __( 'The operator to use for filtering.', 'wp-graphql-gravity-forms' ),
                ],
                // @TODO - Is there a cleaner way to do this? Values can be any of these types.
                'stringValues' => [
                    'type'        => [ 'list_of' => 'String' ],
                    'description' => __( 'The field value(s) to filter by. Must be string values. If using this field, do not also use intValues, floatValues or boolValues.', 'wp-graphql-gravity-forms' ),
                ],
                'intValues' => [
                    'type'        => [ 'list_of' => 'Int' ],
                    'description' => __( 'The field value(s) to filter by. Must be integer values. If using this field, do not also use stringValues, floatValues or boolValues.', 'wp-graphql-gravity-forms' ),
                ],
                'floatValues' => [
                    'type'        => [ 'list_of' => 'Float' ],
                    'description' => __( 'The field value(s) to filter by. Must be float values. If using this field, do not also use stringValues, intValues or boolValues.', 'wp-graphql-gravity-forms' ),
                ],
                'boolValues' => [
                    'type'        => [ 'list_of' => 'Boolean' ],
                    'description' => __( 'The field value(s) to filter by. Must be boolean values. If using this field, do not also use stringValues, intValues or floatValues.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
