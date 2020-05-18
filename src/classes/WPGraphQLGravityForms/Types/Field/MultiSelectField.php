<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * MultiSelect field.
 *
 * @see https://docs.gravityforms.com/gf_field_multiselect/
 */
class MultiSelectField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'MultiSelectField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'multiselect';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Multi-Select field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionPlacementProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\EnableChoiceValueProperty::get(),
                FieldProperty\EnableEnhancedUiProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\SizeProperty::get(),
                [
                    'choices' => [
                        'type'        => [ 'list_of' => FieldProperty\MultiSelectChoiceProperty::TYPE ],
                        'description' => __('The individual properties for each item in the multi-select.', 'wp-graphql-gravity-forms'),
                    ],
                ]
            ),
        ] );
    }
}
