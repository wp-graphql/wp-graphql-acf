<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 * An individual property for the 'choices' field property of the List field.
 *
 * @see https://docs.gravityforms.com/gf_field_list/#highlighter_635805
 */
class ListChoiceProperty implements Hookable, Type {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'ListChoiceProperty';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __('List field column labels.', 'wp-graphql-gravity-forms'),
            'fields'      => [
                'text' => [
                    'type'        => 'String',
                    'description' => __('The text to be displayed in the column header. Required.', 'wp-graphql-gravity-forms'),
                ],
                'value' => [
                    'type'        => 'String',
                    'description' => __('The text to be displayed in the column header.', 'wp-graphql-gravity-forms'),
                ],
            ],
        ] );
    }
}
