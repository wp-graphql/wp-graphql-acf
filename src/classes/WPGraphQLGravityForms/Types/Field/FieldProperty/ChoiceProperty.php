<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 * An individual property for the 'choices' field property.
 *
 * @see https://docs.gravityforms.com/field-object/#basic-properties
 */
class ChoiceProperty implements Hookable, Type {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'ChoiceProperty';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __('Gravity Forms input property.', 'wp-graphql-gravity-forms'),
            'fields'      => [
                'text' => [
                    'type'        => 'String',
                    'description' => __('The text to be displayed to the user when displaying this choice.', 'wp-graphql-gravity-forms'),
                ],
                'value' => [
                    'type'        => 'String',
                    'description' => __('The value to be stored in the database when this choice is selected. Note: This property is only supported by the Drop Down and Post Category fields. Checkboxes and Radio fields will store the text property in the database regardless of the value property.', 'wp-graphql-gravity-forms'),
                ],
                'isSelected' => [
                    'type'        => 'Boolean',
                    'description' => __('Determines if this choice should be selected by default when displayed. The value true will select the choice, whereas false will display it unselected.', 'wp-graphql-gravity-forms'),
                ],
            ],
        ] );
    }
}
