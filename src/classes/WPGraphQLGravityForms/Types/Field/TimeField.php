<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Time field.
 *
 * @see https://docs.gravityforms.com/gf_field_time/
 */
class TimeField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'TimeField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'time';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Time field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\NoDuplicatesProperty::get(),
                FieldProperty\SizeProperty::get()
                // @TODO: Add placeholders.
            ),
        ] );
    }
}
