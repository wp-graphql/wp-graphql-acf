<?php
namespace WPGraphQL\ACF\Fields;

use Exception;
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

		$layouts = isset( $this->field_config['layouts'] ) ? $this->field_config['layouts'] : [];

		if ( empty( $layouts ) ) {
			return null;
		}

		$parent_type = $this->get_parent_type();
		$type_name = $parent_type . '_' . Utils::format_type_name( $this->field_config['name'] );
		$layout_interface_name = $type_name . '_Layout';

		if ( null === $this->registry->get_type_registry()->get_type( $layout_interface_name ) ) {

			$this->registry->get_type_registry()->register_interface_type( $layout_interface_name, [
				'description' => sprintf( __( 'Layouts of the %s Flexible Field Type', 'wp-graphql-acf' ), $layout_interface_name ),
				'fields'      => [
					'layoutName' => [
						'type'        => 'String',
						'description' => __( 'The name of the flexibile field layout', 'wp-graphql-acf' ),
					],
				],
				'resolveType' => function( $object ) use ( $layouts, $type_name ) {
					return $type_name . '_' . Utils::format_type_name( $object['acf_fc_layout'] );
				}
			] );

		}

		// Get the raw fields for the field group so we can
		// determine which fields are clones and which fields are not
		$raw_fields = acf_get_raw_fields( $this->field_config['key'] );
		$layout_type_names = [];

		/**
		 * Iterate over the layouts to determine their GraphQL Type
		 */
		foreach ( $layouts as $layout ) {

			$cloned = false;

			if ( ! isset( $layout['name'] ) ) {
				continue;
			}

			foreach ( $raw_fields as $raw_field ) {
				if ( $layout['key'] === $raw_field['parent_layout'] ) {
					$layout['raw_fields'][] = $raw_field;
					if ( isset( $raw_field['clone'] ) && is_array( $raw_field['clone'] ) && 1 === count( $raw_field['clone'] ) ) {

						if ( 'Hero' === $raw_field['label'] ) {

							if ( false !== strpos( $raw_field['clone'][0], 'group_' ) ) {
								$cloned_group = acf_get_field_group( $raw_field['clone'][0] );
								if ( is_array( $cloned_group ) && ! empty( $cloned_group ) ) {
									$cloned              = true;
									$layout_type_names[] = $this->registry->get_field_group_type_name( $cloned_group );
								}
							}

						}
					}
				}
			}

			if ( true !== $cloned ) {

				$layout_type_name             = $type_name . '_' . Utils::format_type_name( $layout['name'] );
				$layout['title']              = $layout['label'];
				$layout['graphql_field_name'] = $layout_type_name;


				if ( null === $this->registry->get_type_registry()->get_type( $layout_type_name ) ) {

					$this->registry->get_type_registry()->register_object_type( $layout_type_name, [
						'description' => sprintf( __( '%s Flexible Field Layout', 'wp-graphql' ), $layout_type_name ),
						'interfaces'  => [ 'AcfFieldGroup', $layout_interface_name ],
						'fields'      => [
							'layoutName' => [
								'type'        => 'String',
								'description' => __( 'The name of the flexible field layout', 'wp-graphql-acf' ),
								'resolve'     => function() use ( $layout ) {
									return isset( $layout['label'] ) ? $layout['label'] : null;
								}
							],
						]
					] );

				}

				$layout_type_names[] = $layout_type_name;

			}

			if ( ! empty( $layout_type_names ) ) {
				register_graphql_interfaces_to_types( $layout_interface_name, $layout_type_names );
			}

			$this->registry->map_acf_fields_to_field_group( $layout );

		}

		return [ 'list_of' => $layout_interface_name ];
	}

}
