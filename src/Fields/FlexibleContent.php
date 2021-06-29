<?php
namespace WPGraphQL\ACF\Fields;

use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Utils\Utils;

/**
 * Class FlexibleContent
 *
 * @package WPGraphQL\ACF\Fields
 */
class FlexibleContent extends AcfField {

	/**
	 * Determines the GraphQL Type to return for Flexible Content fields
	 *
	 * @return string[]|null
	 * @throws Exception
	 */
	public function get_graphql_type() {

		$layouts = [];

		if ( isset( $this->field_config['layouts'] ) && is_array( $this->field_config['layouts'] ) ) {
			$layouts = array_map( function( $layout ) {
				$layout['parent'] = $this->field_config['key'];
				$graphql_field_name = $layout['graphql_field_name'] ?? $layout['name'];
				$graphql_field_name = $this->get_parent_type() . '_' . Utils::format_type_name( $this->field_name ) . '_' . Utils::format_type_name( $graphql_field_name );
				$layout['graphql_field_name'] = $graphql_field_name;
				$layout['graphql_types'] = [ $this->get_parent_type() ];
				$layout['isFlexLayout'] = true;
				return $layout;
			}, $this->field_config['layouts'] );
		}

		if ( empty( $layouts ) ) {
			return null;
		}

		$parent_type = $this->get_parent_type();
		$type_name = $parent_type . '_' . Utils::format_type_name( $this->field_config['name'] );
		$layout_interface_name = $type_name . '_Layout';

		if ( ! $this->registry->get_registered_field_group_interface( $this->field_config['key'] ) ) {

			$this->registry->get_type_registry()->register_interface_type( $layout_interface_name, [
				'description' => sprintf( __( 'Layouts of the %s Flexible Field Type', 'wp-graphql-acf' ), $layout_interface_name ),
				'fields'      => [
					'layoutName' => [
						'type'        => 'String',
						'description' => __( 'The name of the flexible field layout', 'wp-graphql-acf' ),
					],
				],
				'resolveType' => function( $object ) use ( $layouts, $type_name ) {
					return $type_name . '_' . Utils::format_type_name( $object['acf_fc_layout'] );
				}
			] );

			$this->registry->add_registered_field_group_interface( $this->field_config['key'], $layout_interface_name );

		}

		$registered = [];
		foreach ( $layouts as $layout ) {
			$registered[] = $this->registry->add_acf_field_group_to_graphql( $layout, [], [ $layout_interface_name ]  );
		}

		if ( ! empty( $registered ) ) {
			register_graphql_interfaces_to_types( $layout_interface_name, $registered );
		}

		return [ 'list_of' => $layout_interface_name ];

	}

	public function resolve( $node, array $args, AppContext $context, ResolveInfo $info ) {
		$value = parent::resolve( $node, $args, $context, $info );
		return ! empty( $value ) && is_array( $value ) ? $value : [];
	}

}
