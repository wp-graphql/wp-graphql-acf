<?php

namespace WPGraphQLGravityForms\Types\Entry;

use GFAPI;
use GFFormsModel;
use GraphQLRelay\Relay;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;
use WPGraphQLGravityForms\Interfaces\Field;
use WPGraphQLGravityForms\DataManipulators\EntryDataManipulator;
use WPGraphQLGravityForms\DataManipulators\DraftEntryDataManipulator;
use WPGraphQLGravityForms\Types\Union\ObjectFieldUnion;
use WPGraphQLGravityForms\Types\Form\Form;

/**
 * Gravity Forms form entry.
 *
 * @see https://docs.gravityforms.com/entry-object/
 */
class Entry implements Hookable, Type, Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'GravityFormsEntry';

    /**
     * Field registered in WPGraphQL.
     */
    const FIELD = 'gravityFormsEntry';

    /**
     * EntryDataManipulator instance.
     */
    private $entry_data_manipulator;

    /**
     * DraftEntryDataManipulator instance.
     */
    private $draft_entry_data_manipulator;

    public function __construct(
        EntryDataManipulator $entry_data_manipulator,
        DraftEntryDataManipulator $draft_entry_data_manipulator
    ) {
        $this->entry_data_manipulator       = $entry_data_manipulator;
        $this->draft_entry_data_manipulator = $draft_entry_data_manipulator;
    }

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
        add_action( 'graphql_register_types', [ $this, 'register_field' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms entry.', 'wp-graphql-gravity-forms' ),
            'fields'      => [
                'id' => [
                    'type'        => [ 'non_null' => 'ID' ],
                    'description' => __( 'Unique global ID for the object.', 'wp-graphql-gravity-forms' ),
                ],
                'entryId' => [
                    'type'        => 'Integer',
                    'description' => __( 'The entry ID. Returns null for draft entries.', 'wp-graphql-gravity-forms' ),
                ],
                'formId' => [
                    'type'        => 'Integer',
                    'description' => __( 'The ID of the form that was submitted to generate this entry.', 'wp-graphql-gravity-forms' ),
                ],
                // @TODO: Add field to get post data.
                'postId' => [
                    'type'        => 'Integer',
                    'description' => __( 'For forms with Post fields, this property contains the Id of the Post that was created.', 'wp-graphql-gravity-forms' ),
                ],
                // @TODO: Gravity Forms stores and returns the dateCreated and dateUpdated in UTC time.
                // Change the fields below to be in the blog's local time, and add new ones for
                // dateCreatedUTC and dateUpdatedUTC.
                'dateCreated' => [
                    'type'        => 'String',
                    'description' => __( 'The date and time that the entry was created, in the format "yyyy-mm-dd hh:mi:ss" (i.e. 2010-07-15 17:26:58).', 'wp-graphql-gravity-forms' ),
                ],
                'dateUpdated' => [
                    'type'        => 'String',
                    'description' => __( 'The date and time that the entry was updated, in the format "yyyy-mm-dd hh:mi:ss" (i.e. 2010-07-15 17:26:58).', 'wp-graphql-gravity-forms' ),
                ],
                'isStarred' => [
                    'type'        => 'Boolean',
                    'description' => __( 'Indicates if the entry has been starred (i.e marked with a star). 1 for entries that are starred and 0 for entries that are not starred.', 'wp-graphql-gravity-forms' ),
                ],
                'isRead' => [
                    'type'        => 'Boolean',
                    'description' => __( 'Indicates if the entry has been read. 1 for entries that are read and 0 for entries that have not been read.', 'wp-graphql-gravity-forms' ),
                ],
                'ip' => [
                    'type'        => 'String',
                    'description' => __( 'Client IP of user who submitted the form.', 'wp-graphql-gravity-forms' ),
                ],
                'sourceUrl' => [
                    'type'        => 'String',
                    'description' => __( 'Source URL of page that contained the form when it was submitted.', 'wp-graphql-gravity-forms' ),
                ],
                'userAgent' => [
                    'type'        => 'String',
                    'description' => __( 'Provides the name and version of both the browser and operating system from which the entry was submitted.', 'wp-graphql-gravity-forms' ),
                ],
                // @TODO: Add field to get user data.
                'createdById' => [
                    'type'        => 'Integer',
                    'description' => __( 'ID of the user that submitted of the form if a logged in user submitted the form.', 'wp-graphql-gravity-forms' ),
                ],
                // @TODO: Convert to an enum.
                'status' => [
                    'type'        => 'String',
                    'description' => __( 'The current status of the entry; "active", "spam", or "trash".', 'wp-graphql-gravity-forms' ),
                ],
                'isDraft' => [
                    'type'        => 'Boolean',
                    'description' => __( 'Whether the entry is a draft.', 'wp-graphql-gravity-forms' ),
                ],
                'resumeToken' => [
                    'type'        => 'String',
                    'description' => __( 'The resume token. Only applies to draft entries.', 'wp-graphql-gravity-forms' ),
                ],
                /**
                 * @TODO: Add support for these pricing properties that are only relevant
                 * when a Gravity Forms payment gateway add-on is being used:
                 * https://docs.gravityforms.com/entry-object/#pricing-properties
                 */
            ],
        ] );
    }

    public function register_field() {
        register_graphql_field( 'RootQuery', self::FIELD, [
            'description' => __( 'Get a Gravity Forms entry.', 'wp-graphql-gravity-forms' ),
            'type' => self::TYPE,
            'args' => [
                'id' => [
                    'type'        => [ 'non_null' => 'ID' ],
                    'description' => __( "Unique global ID for the object. Base-64 encode a string like this, where '123' is the entry ID (or the resume token for a draft entry): '{self::TYPE}:123'.", 'wp-graphql-gravity-forms' ),
                ],
            ],
            'resolve' => function( $root, array $args, AppContext $context, ResolveInfo $info ) {
                if ( ! current_user_can( 'gravityforms_view_entries' ) ) {
                    throw new UserError( __( 'Sorry, you are not allowed to view Gravity Forms entries.', 'wp-graphql-gravity-forms' ) );
                }

                $id_parts = Relay::fromGlobalId( $args['id'] );

                if ( ! is_array( $id_parts ) || empty( $id_parts['id'] ) || empty( $id_parts['type'] ) ) {
                    throw new UserError( __( 'A valid global ID must be provided.', 'wp-graphql-gravity-forms' ) );
                }

                $id    = sanitize_text_field( $id_parts['id'] );
                $entry = GFAPI::get_entry( $id );

                if ( ! is_wp_error( $entry ) ) {
                    return $this->entry_data_manipulator->manipulate( $entry );
                }

                $draft_entry = GFFormsModel::get_draft_submission_values( $id );

                if ( ! $draft_entry || ! is_array( $draft_entry ) ) {
                    throw new UserError( __( 'An entry with this ID was not found.', 'wp-graphql-gravity-forms' ) );
                }

                $submission = json_decode( $draft_entry['submission'], true );

                if ( ! $submission ) {
                    throw new UserError( __( 'The submission data for this draft entry could not be read.', 'wp-graphql-gravity-forms' ) );
                }

                return $this->draft_entry_data_manipulator->manipulate( $submission['partial_entry'], $id );                
            }
        ] );
    }
}
