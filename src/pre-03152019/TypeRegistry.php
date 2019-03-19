<?php

namespace WPGraphQL\Extensions\ACF;

function register_graphql_acf_field( $name, $args = [] ) {
	$default_fields = [
		'key' => [
			'type' => 'String',
		],
		'label' => [
			'type' => 'String',
		],
		'name' => [
			'type' => 'String',
		],
		'prefix' => [
			'type' => 'String',
		],
		'value' => [
			'type' => 'String',
		],
		'menuOrder' => [
			'type' => 'Integer',
			'resolve' => function( $field ) {
				return $field['menu_order'];
			},
		],
		'instructions' => [
			'type' => 'String',
		],
		'required' => [
			'type' => 'Boolean',
		],
		'class' => [
			'type' => 'String',
		],
		'conditionalLogic' => [
			'type' => [
				'list_of' => 'ACF_ConditionalLogic',
			],
		],
		'parent' => [
			'type' => 'ID',
		],
		'wrapper' => [
			'type' => 'ACF_FieldWrapper',
		],
	];

	if ( isset( $args['fields'] ) ) {
		$args['fields'] = array_merge( $default_fields, $args['fields'] );
	} else {
		$args['fields'] = $default_fields;
	}

	register_graphql_object_type( $name, $args );
};

class TypeRegistry {
	public static function init() {

		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Enum/ACF_LayoutEnum.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Accordion_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_ButtonGroup_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Checkbox_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_ColorPicker_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_ConditionalLogic.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_DatePicker_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_DateTimePicker_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Email_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_FieldChoice.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_FieldWrapper.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_File_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_FlexibleContent_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Gallery_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_GoogleMap_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Group_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Image_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Link_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_OEmbed_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_PageLink_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_PostObject_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_RadioButton_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Range_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Relationship_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Repeater_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Select_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Tab_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Taxonomy_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Text_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_TextArea_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_TimePicker_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_TrueFalse_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_Url_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_User_Field.php' );
		require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'src/Type/Object/ACF_WYSIWYG_Field.php' );

	}
}


