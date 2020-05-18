<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * CAPTCHA field.
 *
 * @see https://docs.gravityforms.com/gf_field_captcha/
 */
class CaptchaField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'CaptchaField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'captcha';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms CAPTCHA field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\ErrorMessageProperty::get(),
                [
                    /**
                     * Possible values: recaptcha, simple_captcha, math
                     */
                    'captchaType' => [
                        'type'        => 'String',
                        'description' => __('Determines the type of CAPTCHA field to be used.', 'wp-graphql-gravity-forms'),
                    ],
                    /**
                     * Possible values: red, white, blackglass, clean
                     */
                    'captchaTheme' => [
                        'type'        => 'String',
                        'description' => __('Determines the theme to be used for the reCAPTCHA field. Only applicable to the recaptcha captcha type.', 'wp-graphql-gravity-forms'),
                    ],
                    /**
                     * Possible values: small, medium, large
                     */
                    'simpleCaptchaSize' => [
                        'type'        => 'String',
                        'description' => __('Determines the CAPTCHA image size. Only applicable to simple_captcha and math captcha types.', 'wp-graphql-gravity-forms'),
                    ],
                    'simpleCaptchaFontColor' => [
                        'type'        => 'String',
                        'description' => __('Determines the image’s font color, in HEX format (i.e. #CCCCCC). Only applicable to simple_captcha and math captcha types.', 'wp-graphql-gravity-forms'),
                    ],
                    'simpleCaptchaBackgroundColor' => [
                        'type'        => 'String',
                        'description' => __('Determines the image’s background color, in HEX format (i.e. #CCCCCC). Only applicable to simple_captcha and math captcha types.', 'wp-graphql-gravity-forms'),
                    ],
                ]
            ),
        ] );
    }
}
