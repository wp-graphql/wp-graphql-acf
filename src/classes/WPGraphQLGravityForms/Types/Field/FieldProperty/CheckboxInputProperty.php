<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 * An individual property for the 'inputs' Checkbox field property.
 */
class CheckboxInputProperty implements Hookable, Type {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'CheckboxInputProperty';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __('Gravity Forms Chained Select field choice property.', 'wp-graphql-gravity-forms'),
            'fields'      => [
                'id' => [
                    'type'        => 'Float',
                    'description' => __('The input id. Input Ids follow the following naming convention: “FIELDID.INPUTID” (i.e. 5.1), where FIELDID is the id of the containing field and INPUTID specifies the input field.', 'wp-graphql-gravity-forms'),
                ],
                'label' => [
                    'type'        => 'String',
                    'description' => __('Input label.', 'wp-graphql-gravity-forms'),
                ],
                'name' => [
                    'type'        => 'String',
                    'description' => __('When the field is configured with allowsPrepopulate set to 1, this property contains the parameter name to be used to populate this field (equivalent to the inputName property of single-input fields).', 'wp-graphql-gravity-forms'),
                ],
            ],
        ] );
    }
}
