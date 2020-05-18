<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Address field.
 *
 * @see https://docs.gravityforms.com/gf_field_address/
 */
class AddressField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'AddressField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'address';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Address field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\InputsProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\LabelPlacementProperty::get(),
                FieldProperty\SizeProperty::get(),
                [
                    // @TODO - Convert to an enum. Possible values: international, us, canadian
                    'addressType' => [
                        'type'        => 'String',
                        'description' => __('Determines the type of address to be displayed.', 'wp-graphql-gravity-forms'),
                    ],
                    'defaultCountry' => [
                        'type'        => 'String',
                        'description' => __('Contains the country that will be selected by default. Only applicable when "addressType" is set to "international".', 'wp-graphql-gravity-forms'),
                    ],
                    'defaultProvince' => [
                        'type'        => 'String',
                        'description' => __('Contains the province that will be selected by default. Only applicable when "addressType" is set to "canadian".', 'wp-graphql-gravity-forms'),
                    ],
                    'defaultState' => [
                        'type'        => 'String',
                        'description' => __('Contains the state that will be selected by default. Only applicable when "addressType" is set to "us".', 'wp-graphql-gravity-forms'),
                    ],
                    'subLabelPlacement'   => [
                        'type'        => 'String',
                        'description' => __( 'The placement of the labels for the fields (street, city, zip/postal code, etc.) within the address group. This setting controls all of the address pieces, they cannot be set individually. They may be aligned above or below the inputs. If this property is not set, the “Sub-Label Placement” setting on the Form Settings->Form Layout page is used. If no setting is specified, the default is above inputs.', 'wp-graphql-gravity-forms' ),
                    ],
                    // @TODO - add placeholders.
                ]
            ),
        ] );
    }
}
