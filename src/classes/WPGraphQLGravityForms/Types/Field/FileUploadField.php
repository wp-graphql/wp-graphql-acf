<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * File upload field.
 *
 * @see https://docs.gravityforms.com/gf_field_fileupload/
 */
class FileUploadField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'FileUploadField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'fileupload';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms File Upload field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\SizeProperty::get()
            ),
        ] );
    }
}
