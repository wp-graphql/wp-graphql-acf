<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Password field.
 *
 * @see https://docs.gravityforms.com/gf_field_password/
 */
class PasswordField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'PasswordField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'password';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Password field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionPlacementProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\PlaceholderProperty::get(),
                [
                    'inputs' => [
                        'type'        => [ 'list_of' => FieldProperty\PasswordInputProperty::TYPE ],
                        'description' => __('Individual properties for each element of the password field.', 'wp-graphql-gravity-forms'),
                    ],
                    // @TODO: Convert to an enum.
                    'minPasswordStrength' => [
                        'type'        => 'String',
                        'description' => __('Indicates how strong the password should be. The possible values are: short, bad, good, strong.', 'wp-graphql-gravity-forms'),
                    ],
                    'passwordStrengthEnabled' => [
                        'type'        => 'Boolean',
                        'description' => __('Indicates whether the field displays the password strength indicator.', 'wp-graphql-gravity-forms'),
                    ],
                ]
            ),
        ] );
    }
}
