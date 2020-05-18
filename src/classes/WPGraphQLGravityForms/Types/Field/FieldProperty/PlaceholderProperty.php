<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

abstract class PlaceholderProperty implements FieldProperty {
    /**
     * Get 'placeholder' property.
     */
    public static function get() : array {
        return [
            'placeholder' => [
                'type'        => 'String',
                'description' => __( 'Field placeholder.', 'wp-graphql-gravity-forms' ),
            ],
        ];
    }
}
