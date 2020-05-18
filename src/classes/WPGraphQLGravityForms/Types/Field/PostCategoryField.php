<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Post Category field.
 *
 * @see https://docs.gravityforms.com/post-category/
 */
class PostCategoryField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'PostCategoryField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'post_category';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Post Category field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\ChoicesProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\SizeProperty::get(),
                [
                    'displayAllCategories' => [
                        'type'        => 'Boolean',
                        'description' => __('Determines if all categories should be displayed on the Post Category drop down. 1 to display all categories, 0 otherwise. If this property is set to 1 (display all categories), the Post Category drop down will display the categories hierarchically.', 'wp-graphql-gravity-forms'),
                    ],
                ]
            ),
        ] );
    }
}
