<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * List field.
 *
 * @see https://docs.gravityforms.com/gf_field_list/
 */
class ListField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'ListField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'list';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms List field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionPlacementProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\LabelPlacementProperty::get(),
                [
                    'addIconUrl' => [
                        'type'        => 'String',
                        'description' => __('The URL of the image to be used for the add row button.', 'wp-graphql-gravity-forms'),
                    ],
                    'choices' => [
                        'type'        => [ 'list_of' => FieldProperty\ListChoiceProperty::TYPE ],
                        'description' => __('The column labels. Only used when enableColumns is true.', 'wp-graphql-gravity-forms'),
                    ],
                    'deleteIconUrl' => [
                        'type'        => 'String',
                        'description' => __('The URL of the image to be used for the delete row button.', 'wp-graphql-gravity-forms'),
                    ],
                    'enableColumns' => [
                        'type'        => 'Boolean',
                        'description' => __('Determines if the field should use multiple columns. Default is false.', 'wp-graphql-gravity-forms'),
                    ],
                    'maxRows' => [
                        'type'        => 'Integer',
                        'description' => __( 'The maximum number of rows the user can add to the field.', 'wp-graphql-gravity-forms' ),
                    ],
                    'pageNumber' => [
                        'type'        => 'Integer',
                        'description' => __( 'The form page this field is located on. Default is 1.', 'wp-graphql-gravity-forms' ),
                    ],
                ]
            ),
        ] );
    }
}
