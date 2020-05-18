<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

abstract class EnableChoiceValueProperty implements FieldProperty {
    /**
     * Get 'enableChoiceValue' property.
     *
     * Applies to: checkbox, select and radio
     */
    public static function get() : array {
        return [
            'enableChoiceValue' => [
                'type'        => 'Boolean',
                'description' => __('Determines if the field (checkbox, select or radio) have choice values enabled, which allows the field to have choice values different from the labels that are displayed to the user.', 'wp-graphql-gravity-forms'),
            ],
        ];
    }
}
