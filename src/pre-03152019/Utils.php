<?php
namespace WPGraphQL\Extensions\ACF;

class Utils {

	/**
	 * Utility function for formatting a string to be compatible with GraphQL labels (camelCase with lowercase first letter)
	 *
	 * @param $input
	 *
	 * @return mixed|string
	 */
	public static function _graphql_label( $input ) {

		$graphql_label = str_ireplace( '_', ' ', $input );
		$graphql_label = ucwords( $graphql_label );
		$graphql_label = str_ireplace( ' ', '', $graphql_label );
		$graphql_label = lcfirst( $graphql_label );

		return $graphql_label;

	}

}
