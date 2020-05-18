<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

/**
 * Description placement property. Applies to List and MultiSelect fields.
 * This is different from the 'descriptionPlacement' Form field.
 */
abstract class DescriptionPlacementProperty implements FieldProperty {
    public static function get() : array {
        return [
            // @TODO - Convert to enum. Possible values: "above" or "below"
            'choices' => [
                'type'        => 'String',
                'description' => __('The placement of the field description. The description may be placed “above” or “below” the field inputs. If the placement is not specified, then the description placement setting for the Form Layout is used.', 'wp-graphql-gravity-forms'),
            ],
        ];
    }
}
