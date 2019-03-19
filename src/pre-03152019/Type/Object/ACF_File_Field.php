<?php

namespace WPGraphQL\ACF\Type;

use WPGraphQL\Data\DataSource;
use function WPGraphQL\Extensions\ACF\register_graphql_acf_field;

register_graphql_acf_field( 'ACF_File_Field', [
	'description' => __( 'A file field', 'wp-graphql-acf' ),
	'fields'      => [
		'returnFormat' => [
			'type'    => 'String',
			'resolve' => function ( $field ) {
				return ! empty( $field['return_format'] ) ? $field['return_format'] : null;
			}
		],
		'value'        => [
			'type'    => 'Int',
			'resolve' => function ( $field ) {

				if ( 'url' === $field['return_format'] ) {
					return null;
				}

				$resolved = get_field( $field['key'], $field['object_id'] );

				if ( is_array( $resolved ) ) {
					$id = $resolved['id'];
				} else {
					$id = $resolved;
				}

				return isset( $id ) ? absint( $id ) : null;
			}
		],
		'file'        => [
			'type'    => 'MediaItem',
			'resolve' => function ( $field ) {

				if ( 'url' === $field['return_format'] ) {
					return null;
				}

				$resolved = get_field( $field['key'], $field['object_id'] );

				if ( is_array( $resolved ) ) {
					$id = $resolved['id'];
				} else {
					$id = $resolved;
				}

				return isset( $id ) ? DataSource::resolve_post_object( absint( $id ), 'attachment' ) : null;
			}
		],

	],
] );


register_graphql_union_type( 'ACF_FileFieldUnion', [
	'name'        => 'ACF_FileFieldUnion',
	'typeNames'   => [ 'MediaItem', 'String', 'ID' ],
	'resolveType' => function ( $source ) {
		$type = 'MediaItem';
		if ( isset( $source['return_format'] ) ) {
			switch ( $source['return_format'] ) {
				case 'array':
					$type = 'MediaItem';
					break;
				case 'id':
					$type = 'ID';
					break;
				case 'url':
					$type = 'String';
					break;
			}
		}

		return $type;
	},
] );