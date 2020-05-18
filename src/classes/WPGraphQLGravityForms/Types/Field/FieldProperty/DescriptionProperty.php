<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

abstract class DescriptionProperty implements FieldProperty {
    /**
     * Get 'description' property.
     */
    public static function get() : array {
        return [
            'description' => [
                'type'        => 'String',
                'description' => __( 'Field description.', 'wp-graphql-gravity-forms' ),
            ],
        ];
    }
}
