<?php
namespace WPGraphQL\ACF\Fields;

use Exception;
use WPGraphQL\Utils\Utils;

/**
 * Class Group
 *
 * @package WPGraphQL\ACF\Fields
 */
class Group extends AcfField {

	/**
	 * Determines the GraphQL Type for nested Field Groups
	 *
	 * @return mixed|string|string[]|null
	 * @throws Exception
	 */
	public function get_graphql_type() {

		$parent_type = $this->get_parent_type();
		$title = $this->field_config['title'] ?? ( isset( $this->field_config['label'] ) ? $this->field_config['label'] : 'no label or title' );
		$this->field_config['graphql_field_name'] = $parent_type . '_' . Utils::format_type_name( $title );

		$type_name = $this->registry->add_acf_field_group_to_graphql( $this->field_config );
		return ! empty( $type_name ) ? $type_name : null;
	}

}
