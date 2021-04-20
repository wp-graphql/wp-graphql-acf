<?php
namespace WPGraphQL\ACF\Fields;

use WPGraphQL\Utils\Utils;

class Repeater extends AcfField {

	public function get_graphql_type() {
		$parent_type = $this->get_parent_type();
		$title = isset( $this->field_config['title'] ) ? $this->field_config['title'] : ( isset( $this->field_config['label'] ) ? $this->field_config['label'] : 'no label or title' );
		$this->field_config['graphql_field_name'] = $parent_type . '_' . Utils::format_type_name( $title );
		$type_name = $this->registry->add_acf_field_group_to_graphql( $this->field_config );
		return ! empty( $type_name ) ? [ 'list_of' => $type_name ] : null;
	}

}
