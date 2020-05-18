<?php

namespace WPGraphQLGravityForms\Types\Field;

/**
 * Section field.
 *
 * @see https://docs.gravityforms.com/gf_field_section/
 */
class SectionField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'SectionField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'section';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Section field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionProperty::get()
            ),
        ] );
    }
}
