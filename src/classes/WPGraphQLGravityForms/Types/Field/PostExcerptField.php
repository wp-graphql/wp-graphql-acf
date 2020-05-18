<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Post Excerpt field.
 *
 * @see https://docs.gravityforms.com/post-excerpt/
 */
class PostExcerptField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'PostExcerptField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'post_excerpt';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Post Excerpt field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DefaultValueProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\PlaceholderProperty::get(),
                FieldProperty\SizeProperty::get()
            ),
        ] );
    }
}
