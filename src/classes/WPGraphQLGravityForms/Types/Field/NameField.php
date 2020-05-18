<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Name field.
 *
 * @see https://docs.gravityforms.com/gf_field_name/
 */
class NameField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'NameField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'name';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Name field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\InputsProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\SizeProperty::get(),
                [
                    /**
                     * Possible values: normal, extended, simple
                     */
                    'nameFormat' => [
                        'type'        => 'String',
                        'description' => __('Determines the format of the name field.', 'wp-graphql-gravity-forms'),
                    ],
                    // @TODO: Add placeholders.
                ]
            ),
        ] );
    }
}
