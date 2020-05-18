<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 * An individual property for the 'choices' Chained Select field property.
 */
class ChainedSelectChoiceProperty implements Hookable, Type {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'ChainedSelectChoiceProperty';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __('Gravity Forms Chained Select field choice property.', 'wp-graphql-gravity-forms'),
            'fields'      => [
                'text' => [
                    'type'        => 'String',
                    'description' => __('The text to be displayed to the user when displaying this choice.', 'wp-graphql-gravity-forms'),
                ],
                'value' => [
                    'type'        => 'String',
                    'description' => __('The value to be stored in the database when this choice is selected.', 'wp-graphql-gravity-forms'),
                ],
                'isSelected' => [
                    'type'        => 'Boolean',
                    'description' => __('Determines if this choice should be selected by default when displayed. The value true will select the choice, whereas false will display it unselected.', 'wp-graphql-gravity-forms'),
                ],
                'choices' => [
                    'type'        => [ 'list_of' => self::TYPE ],
                    'description' => __('Choices used to populate the dropdown field. These can be nested multiple levels deep.', 'wp-graphql-gravity-forms'),
                ],
            ],
        ] );
    }
}
