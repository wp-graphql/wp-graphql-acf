<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 * An individual property for the 'choices' MultiSelect field property.
 *
 * @see https://docs.gravityforms.com/gf_field_multiselect/
 */
class MultiSelectChoiceProperty implements Hookable, Type {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'MultiSelectChoiceProperty';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __('Gravity Forms input property.', 'wp-graphql-gravity-forms'),
            'fields'      => [
                'text' => [
                    'type'        => 'String',
                    'description' => __('The text that is displayed.', 'wp-graphql-gravity-forms'),
                ],
                'value' => [
                    'type'        => 'String',
                    'description' => __('The value that is used for the multi select when the form is submitted.', 'wp-graphql-gravity-forms'),
                ],
                'isSelected' => [
                    'type'        => 'Boolean',
                    'description' => __('Indicates whether the item is selected.', 'wp-graphql-gravity-forms'),
                ],
            ],
        ] );
    }
}
