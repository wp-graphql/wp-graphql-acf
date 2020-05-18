<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

/**
 * Label Placement field property.
 */
abstract class LabelPlacementProperty implements FieldProperty {
    public static function get() : array {
        return [
            // @TODO - Convert to enum. See corresponding Form 'labelPlacement' field.
            'labelPlacement' => [
                'type'        => 'String',
                'description' => __( 'The field label position. Empty when using the form defaults or a value of "hidden_label".', 'wp-graphql-gravity-forms' ),
            ],
        ];
    }
}
