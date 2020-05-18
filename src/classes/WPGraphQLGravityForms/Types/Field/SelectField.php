<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Select field.
 *
 * @see https://docs.gravityforms.com/gf_field_select/
 */
class SelectField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'SelectField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'select';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Select field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\ChoicesProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\EnableChoiceValueProperty::get(),
                FieldProperty\EnableEnhancedUiProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\NoDuplicatesProperty::get(),
                FieldProperty\PlaceholderProperty::get(),
                FieldProperty\SizeProperty::get()
            ),
        ] );
    }
}
