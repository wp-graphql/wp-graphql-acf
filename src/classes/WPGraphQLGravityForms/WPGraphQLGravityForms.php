<?php

namespace WPGraphQLGravityForms;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\DataManipulators;
use WPGraphQLGravityForms\Types\Button\Button;
use WPGraphQLGravityForms\Types\ConditionalLogic;
use WPGraphQLGravityForms\Types\Enum;
use WPGraphQLGravityForms\Types\Form;
use WPGraphQLGravityForms\Types\Field;
use WPGraphQLGravityForms\Types\Field\FieldProperty;
use WPGraphQLGravityForms\Types\Field\FieldValue;
use WPGraphQLGravityForms\Types\FieldError\FieldError;
use WPGraphQLGravityForms\Types\Union;
use WPGraphQLGravityForms\Types\Connection;
use WPGraphQLGravityForms\Types\Entry;
use WPGraphQLGravityForms\Types\Input;
use WPGraphQLGravityForms\Mutations;

/**
 * Main plugin class.
 */
final class WPGraphQLGravityForms {
	/**
	 * Class instances.
	 */
    private $instances = [];

	/**
	 * Main method for running the plugin.
	 */
	public function run() {
		$this->create_instances();
		$this->register_hooks();
    }

	private function create_instances() {
		// Data manipulators
		$this->instances['fields_data_manipulator']       = new DataManipulators\FieldsDataManipulator();
		$this->instances['form_data_manipulator']         = new DataManipulators\FormDataManipulator( $this->instances['fields_data_manipulator'] );
		$this->instances['entry_data_manipulator']        = new DataManipulators\EntryDataManipulator();
		$this->instances['draft_entry_data_manipulator']  = new DataManipulators\DraftEntryDataManipulator( $this->instances['entry_data_manipulator'] );

		// Buttons
		$this->instances['button'] = new Button();

		// Conditional Logic
		$this->instances['conditional_logic']      = new ConditionalLogic\ConditionalLogic();
		$this->instances['conditional_logic_rule'] = new ConditionalLogic\ConditionalLogicRule();

		// Forms
		$this->instances['save_and_continue']         = new Form\SaveAndContinue();
		$this->instances['form_notification_routing'] = new Form\FormNotificationRouting();
		$this->instances['form_notification']         = new Form\FormNotification();
		$this->instances['form_confirmation']         = new Form\FormConfirmation();
		$this->instances['form_pagination']           = new Form\FormPagination();
		$this->instances['form']                      = new Form\Form( $this->instances['form_data_manipulator'] );

		// Fields
		$this->instances['address_field']        = new Field\AddressField();
		$this->instances['calculation_field']    = new Field\CalculationField();
		$this->instances['captcha_field']        = new Field\CaptchaField();
		$this->instances['chained_select_field'] = new Field\ChainedSelectField();
		$this->instances['checkbox_field']       = new Field\CheckboxField();
		$this->instances['date_field']           = new Field\DateField();
		$this->instances['email_field']          = new Field\EmailField();
		$this->instances['file_upload_field']    = new Field\FileUploadField();
		$this->instances['hidden_field']         = new Field\HiddenField();
		$this->instances['html_field']           = new Field\HtmlField();
		$this->instances['list_field']           = new Field\ListField();
		$this->instances['multi_select_field']   = new Field\MultiSelectField();
		$this->instances['name_field']           = new Field\NameField();
		$this->instances['number_field']         = new Field\NumberField();
		$this->instances['page_field']           = new Field\PageField();
		$this->instances['password_field']       = new Field\PasswordField();
		$this->instances['phone_field']          = new Field\PhoneField();
		$this->instances['post_category_field']  = new Field\PostCategoryField();
		$this->instances['post_content_field']   = new Field\PostContentField();
		$this->instances['post_custom_field']    = new Field\PostCustomField();
		$this->instances['post_excerpt_field']   = new Field\PostExcerptField();
		$this->instances['post_image_field']     = new Field\PostImageField();
		$this->instances['post_tags_field']      = new Field\PostTagsField();
		$this->instances['post_title_field']     = new Field\PostTitleField();
		$this->instances['radio_field']          = new Field\RadioField();
		$this->instances['section_field']        = new Field\SectionField();
		$this->instances['signature_field']      = new Field\SignatureField();
		$this->instances['select_field']         = new Field\SelectField();
		$this->instances['text_area_field']      = new Field\TextAreaField();
		$this->instances['text_field']           = new Field\TextField();
		$this->instances['time_field']           = new Field\TimeField();
		$this->instances['website_field']        = new Field\WebsiteField();

		// Field Properties
		$this->instances['chained_select_choice_property'] = new FieldProperty\ChainedSelectChoiceProperty();
		$this->instances['checkbox_input_property']        = new FieldProperty\CheckboxInputProperty();
		$this->instances['choice_property']                = new FieldProperty\ChoiceProperty();
		$this->instances['input_property']                 = new FieldProperty\InputProperty();
		$this->instances['list_choice_property']           = new FieldProperty\ListChoiceProperty();
		$this->instances['multi_select_choice_property']   = new FieldProperty\MultiSelectChoiceProperty();
		$this->instances['password_input_property']        = new FieldProperty\PasswordInputProperty();

		// Field Values
		$this->instances['address_field_value']        = new FieldValue\AddressFieldValue();
		$this->instances['chained_select_field_value'] = new FieldValue\ChainedSelectFieldValue();
		$this->instances['checkbox_field_values']      = new FieldValue\CheckboxFieldValue();
		$this->instances['date_field_values']          = new FieldValue\DateFieldValue();
		$this->instances['email_field_value']          = new FieldValue\EmailFieldValue();
		$this->instances['file_upload_field_value']    = new FieldValue\FileUploadFieldValue();
		$this->instances['multi_select_field_value']   = new FieldValue\MultiSelectFieldValue();
		$this->instances['name_field_value']           = new FieldValue\NameFieldValue();
		$this->instances['number_field_value']         = new FieldValue\NumberFieldValue();
		$this->instances['phone_field_values']         = new FieldValue\PhoneFieldValue();
		$this->instances['radio_field_values']         = new FieldValue\RadioFieldValue();
		$this->instances['select_field_value']         = new FieldValue\SelectFieldValue();
		$this->instances['signature_field_value']      = new FieldValue\SignatureFieldValue();
		$this->instances['text_area_field_value']      = new FieldValue\TextAreaFieldValue();
		$this->instances['text_field_value']           = new FieldValue\TextFieldValue();
		$this->instances['time_field_value']           = new FieldValue\TimeFieldValue();
		$this->instances['website_field_value']        = new FieldValue\WebsiteFieldValue();

		// Entries
		$this->instances['entry']                      = new Entry\Entry( $this->instances['entry_data_manipulator'], $this->instances['draft_entry_data_manipulator'] );
		$this->instances['entry_form']                 = new Entry\EntryForm( $this->instances['form_data_manipulator'] );
		$this->instances['entry_user']                 = new Entry\EntryUser();

		// Input
		$this->instances['checkbox_input']             = new Input\CheckboxInput();
		$this->instances['entries_date_fiters_input']  = new Input\EntriesDateFiltersInput();
		$this->instances['entries_field_fiters_input'] = new Input\EntriesFieldFiltersInput();
		$this->instances['entries_sorting_input']      = new Input\EntriesSortingInput();

		// Unions
		$this->instances['object_field_union']       = new Union\ObjectFieldUnion( $this->instances );
		$this->instances['object_field_value_union'] = new Union\ObjectFieldValueUnion( $this->instances );

		// Connections
		$this->instances['entry_field_connection']        = new Connections\EntryFieldConnection( $this->instances );
		$this->instances['form_field_connection']         = new Connections\FormFieldConnection();
		$this->instances['root_query_entries_connection'] = new Connections\RootQueryEntriesConnection();
		$this->instances['root_query_forms_connection']   = new Connections\RootQueryFormsConnection();

		// Enums
		$this->instances['form_status_enum']                  = new Enum\FormStatusEnum();
		$this->instances['field_filters_operator_input_enum'] = new Enum\FieldFiltersOperatorInputEnum();

		// Field errors
		$this->instances['field_error'] = new FieldError();

		// Draft entry mutations
		$this->instances['create_draft_entry']                          = new Mutations\CreateDraftEntry();
		$this->instances['delete_draft_entry']                          = new Mutations\DeleteDraftEntry();
		$this->instances['submit_draft_entry']                          = new Mutations\SubmitDraftEntry( $this->instances['entry_data_manipulator'] );
		$this->instances['update_draft_entry_checkbox_field_value']     = new Mutations\UpdateDraftEntryCheckboxFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_date_field_value']         = new Mutations\UpdateDraftEntryDateFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_email_field_value']        = new Mutations\UpdateDraftEntryEmailFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_multi_select_field_value'] = new Mutations\UpdateDraftEntryMultiSelectFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_number_field_value']       = new Mutations\UpdateDraftEntryNumberFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_phone_field_value']        = new Mutations\UpdateDraftEntryPhoneFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_radio_field_value']        = new Mutations\UpdateDraftEntryRadioFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_select_field_value']       = new Mutations\UpdateDraftEntrySelectFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_signature_field_value']    = new Mutations\UpdateDraftEntrySignatureFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_text_area_field_value']    = new Mutations\UpdateDraftEntryTextAreaFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_text_field_value']         = new Mutations\UpdateDraftEntryTextFieldValue( $this->instances['draft_entry_data_manipulator'] );
		$this->instances['update_draft_entry_website_field_value']      = new Mutations\UpdateDraftEntryWebsiteFieldValue( $this->instances['draft_entry_data_manipulator'] );
	}

	private function register_hooks() {
		foreach ( $this->get_hookable_instances() as $instance ) {
			$instance->register_hooks();
		}
	}

	private function get_hookable_instances() {
		return array_filter( $this->instances, function( $instance ) {
			return $instance instanceof Hookable;
		} );
	}
}

// @TODO: Handle this more gracefully to bump up the number of form fields returned.
add_filter( 'graphql_connection_max_query_amount', function() {
	return 500;
}, 11 );
