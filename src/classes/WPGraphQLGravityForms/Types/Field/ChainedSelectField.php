<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Chained Select field.
 *
 * @see https://www.gravityforms.com/add-ons/chained-selects/
 * @see https://docs.gravityforms.com/category/add-ons-gravity-forms/chained-selects/
 */
class ChainedSelectField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'ChainedSelectField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'chainedselect';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Chained Select field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputsProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\SizeProperty::get(),
                [
                    'choices' => [
                        'type'        => [ 'list_of' => FieldProperty\ChainedSelectChoiceProperty::TYPE ],
                        'description' => __('Choices used to populate the dropdown field. These can be nested multiple levels deep.', 'wp-graphql-gravity-forms'),
                    ],
                    // @TODO: Convert to an enum.
                    'chainedSelectsAlignment' => [
                        'type'        => 'String',
                        'description' => __('Alignment of the dropdown fields. Possible values: "horizontal" (in a row) or "vertical" (in a column).', 'wp-graphql-gravity-forms'),
                    ],
                    'chainedSelectsHideInactive' => [
                        'type'        => 'Boolean',
                        'description' => __('Whether inactive dropdowns should be hidden.', 'wp-graphql-gravity-forms'),
                    ],
                ]
            ),
        ] );
    }
}
