<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Hidden field.
 *
 * @see https://docs.gravityforms.com/gf_field_hidden/
 */
class HiddenField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'HiddenField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'hidden';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Hidden field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DefaultValueProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\NoDuplicatesProperty::get(),
                FieldProperty\SizeProperty::get()
            ),
        ] );
    }
}
