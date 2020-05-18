<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Number field.
 *
 * @see https://docs.gravityforms.com/gf_field_number/
 */
class NumberField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'NumberField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'number';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Number field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DefaultValueProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\NoDuplicatesProperty::get(),
                FieldProperty\PlaceholderProperty::get(),
                FieldProperty\SizeProperty::get(),
                [
                    /**
                     * Possible values: decimal_dot (9,999.99), decimal_comma (9.999,99), currency.
                     */
                    'numberFormat' => [
                        'type'        => 'String',
                        'description' => __( 'Specifies the format allowed for the number field.', 'wp-graphql-gravity-forms' ),
                    ],
                    'rangeMin' => [
                        'type'        => 'Float',
                        'description' => __( 'Minimum allowed value for a number field. Values lower than the number specified by this property will cause the field to fail validation.', 'wp-graphql-gravity-forms' ),
                    ],
                    'rangeMax' => [
                        'type'        => 'Float',
                        'description' => __( 'Maximum allowed value for a number field. Values higher than the number specified by this property will cause the field to fail validation.', 'wp-graphql-gravity-forms' ),
                    ],
                ]
            ),
        ] );
    }
}
