<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Calculation field.
 *
 * @see https://docs.gravityforms.com/gf_field_calculation/
 * @see https://docs.gravityforms.com/using-calculations/
 */
class CalculationField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'CalculationField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'calculation';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Calculation field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionPlacementProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\NoDuplicatesProperty::get(),
                FieldProperty\PlaceholderProperty::get(),
                [
                    'calculationFormula' => [
                        'type'        => 'String',
                        'description' => __( 'The formula to be used.', 'wp-graphql-gravity-forms' ),
                    ],
                    'enableCalculation' => [
                        'type'        => 'Boolean',
                        'description' => __( 'Indicates whether the calculation use is active.', 'wp-graphql-gravity-forms '),
                    ],
                    // @TODO: Convert to an enum.
                    'calculationRounding' => [
                        'type'        => 'String',
                        'description' => __( 'The number of decimal places the number should be rounded to. Possible values: norounding, 0, 1, 2, 3, 4.', 'wp-graphql-gravity-forms '),
                    ],
                ]
            ),
        ] );
    }
}
