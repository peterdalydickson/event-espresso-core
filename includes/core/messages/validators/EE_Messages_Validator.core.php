<?php

if (!defined('EVENT_ESPRESSO_VERSION') )
	exit('NO direct script access allowed');

/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package		Event Espresso
 * @ author			Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license		http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link			http://www.eventespresso.com
 * @ version		3.2
 *
 * ------------------------------------------------------------------------
 *
 * EE_Messages_Validator class
 *
 * This class is the parent class for handling validation of message template fields.  Children classes follow a certain naming format (i.e. /email/EE_Messages_Email_Payment_Validator.class.php) and they simply serve the function of defining any special validation rules for the context->field for that messenger/message_type combination when templates are edited.  
 *
 * @abstract
 * @package		Event Espresso
 * @subpackage	includes/core/messages/validators/EE_Messages_Validator.core.php
 * @author		Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
abstract class EE_Messages_Validator extends EE_Base {



	/**
	 * These properties just hold the name for the Messenger and Message Type (defined by child classes). These are used for retrieving objects etc.
	 * @var string
	 */
	protected $_m_name;
	protected $_mt_name;



	/**
	 * This will hold any error messages from the validation process.
	 *
	 * The _errors property holds an associative array of error messages listing the field as the key and the messsage as the value.
	 * @var array()
	 */
	private $_errors = array();




	/**
	 * holds an array of fields being validated
	 * @var string
	 */
	protected $_fields;




	/**
	 * this holds an array of fields and the relevant validation information that the incoming fields data get validated against.  This gets setup in the _set_props() method.
	 * @var array
	 */
	protected $_validators;




	/**
	 * holds the messenger object
	 * @var object
	 */
	protected $_MSGR



	/**
	 * holds the message type object
	 * @var object
	 */
	protected $_MSGTYP






	/**
	 * Runs the validator using the incoming fields array as the fields/values to check.
	 *
	 * 
	 * @param array $fields The fields sent by the EEM object.
	 * @return mixed (bool|array)  if errors present we return the array otherwise true
	 */
	public function __construct( $fields ) {
		//check that _m_name and _mt_name have been set by child class otherwise we get out.
		if ( empty($this->_m_name ) || empty( $this->_mt_name) )
			throw new EE_Error( __('EE_Messages_Validator child classes MUST set the $_m_name and $_mt_name property.  Check that the child class is doing this', 'event_espresso') );


		//load messenger and message_type objects and the related shortcode objects.
		$this->_load_objects();


		//let's set validators property
		$this->_set_validators();

		//modify any messenger/message_type specific validation instructions.  This is what child classes define.
		$this->_modify_validator();

		//let's run validation!
		$this->_validate();

		//return any errors or just TRUE if everything validates
		return empty( $this->_errors ) ? TRUE : $this->_errors();
	}



	/**
	 * Child classes instantiate this and use it to modify the _validators array property that has been set in _set_props().  This is so we can specify specific validation instructions for a messenger/message_type combo that aren't handled by the defaults setup in the messenger.	
	 * @abstract
	 * @access protected
	 * @return void 
	 */
	abstract protected function _modify_validator();


	/**
	 * loads all objects used by validator
	 *
	 * @access private
	 * @return void
	 */
	private function _load_objects() {
		//load messenger
		$messenger = ucwords( str_replace( '_', ' ', $this->_m_name ) );
		$messenger = str_replace( ' ', '_', $messenger );
		$messenger = 'EE_' . $messenger . '_messenger';

		if ( !class_exists( $messenger ) ) {
			$msg = sprintf( __('There is no messenger class for the given string (%s)'), $this->_m_name ) );
		}

		$a = new ReflectionClass( $messenger );
		$this->_MSGR = $a->newInstance();

		//load message type
		$message_type = ucwords( str_replace( '_', ' ', $this->_mt_name ) );
		$message_type = str_replace( ' ', '_', $message_type );
		$message_type = 'EE_' . $message_type . '_message_type';

		if ( !class_exists( $message_type ) ) {
			$msg = sprintf( __('There is no message type class for the given string (%s)'), $this->_mt_name ) );
		}

		$a = new ReflectionClass( $message_type );
		$this->_MSGTYP = $a->newInstance();
		
	}



	/**
	 * used to set the $_validators property
	 *
	 * @access private
	 * @return void
	 */
	private function _set_validators() {
		//let's get all valid shortcodes from mt and message type (messenger will have its set in the _validator_config property for the messenger)
		$mt_codes = $this->_MSGTYP->get_valid_shortcodes();


		//get messenger validator_config
		$msgr_validator = $this->_MSGR->get_validator_config();

		
		//we only want the valid shortcodes for the given context!
		$context = $this->_fields['MTP_context'];
		$mt_codes = $mt_codes[$context];


		//in this first loop we're just getting all shortcode group indexes from the msgr_validator into a single array (so we can get the appropriate shortcode objects for the groups)
		$shrtcode_grps = $mt_codes; //start off with the mt_codes group.
		foreach ( $msgr_validator as $field => $config ) {
			if ( empty($config) || !isset($config['specific_shortcodes']) )
				continue;  //Nothing to see here.

			$shrtcode_grps = array_merge( $config['shortcodes'], $shrtcode_grps );
		}

		//okay now we've got our grps. Let's get the codes from the objects into an array indexed by group for easy retrieval later.
		$codes_from_objs = array();
		foreach ( $shrtcode_groups as $group ) {
			$ref = ucwords( str_replace('_', ' ', $shortcode_ref ) );
			$ref = str_replace( ' ', '_', $ref );
			$classname = 'EE_' . $ref . '_Shortcodes';
			if ( class_exists( $classname ) ) {
				$a = new ReflectionClass( $classname );
				$obj = $a->newInstance();
				$codes_from_objs[$group] = array_merge( $codes_from_objs, array_keys($obj->get_shortcodes()) );
			}
		}


		//let's just replace the $mt shortcode group indexes with the actual shortcodes (unique)
		$final_mt_codes = array();
		foreach ( $mt_codes as $group ) {
			$final_mt_codes = array_merge( $final_mt_codes, $codes_from_objs[$group] );
		}

		$mt_codes = $final_mt_codes;


		//k now in this next loop we're going to loop through $msgr_validator again and setup the _validators property from the data we've setup so far.
		foreach ( $msgr_validator as $field => $config ) {
			//if empty config then we're assuming we're just going to use the shortcodes from the message type context
			if ( empty( $config ) ) {
				$this->_validators[$field]['shortcodes'] = $mt_codes;
			}

			//if we have specific shortcodes then we need to use them
			else if ( isset($config['specific_shortcodes'] ) ) {
				$this->_validators[$field]['shortcodes'] = $config['specific_shortcodes'];
			}

			//otherwise the shortcodes are what is set by the messenger for that field
			else {
				foreach ( $config['shortcodes'] as $group ) {
					$this->_validators[$field]['shortcodes'] = array_merge( $this->_validators[$field]['shortcodes'], $codes_from_objs[$group] );
				}
			}

			//hey! don't forget to include the type if present!
			$this->_validators[$field]['type'] = isset( $config['type'] ) ? $config['type'] : NULL;
		}
	}



	/**
	 * This is the main method that handles validation
	 *
	 * What it does is loop through the _fields (the ones that get validated) and checks them against the shortcodes array for the field and the 'type' indicated by the 
	 * 
	 * @return void
	 */
	private function _validate() {
		//some defaults
		$invalid_shortcodes = '';
		$template_fields = $this->_MSGR->get_template_fields();
		//loop through the fields and check!
		foreach ( $this->_fields as $field => $value ) {
			$this->_errors[$field] = '';
			$field_label = '';

			//if field is not present in the _validators array then we continue
			if ( !isset( $this->_validators[$field] ) ) continue;

			//get the translated field label!
			//first check if it's in the main fields list
			if ( isset( $template_fields[$field] ) ) {
				if ( empty( $template_fields[$field] ) ) $field_label = $field; //most likely the field is found in the 'extra' array.
				$field_label = $template_fields[$field]['label'];
			} 

			//if field label is empty OR is equal to the current field then we need to loop through the 'extra' fields in the template_fields config (if present)
			if ( isset( $template_fields['extra'] ) && ( empty($field_label) ) || $field_label == $field ) {
				foreach( $template_fields['extra'] as $main_field => $secondary_field ) {
					if ( $secondary_field == $field ) {
						$field_label = $secondary_field['label'];
					}

					//if we've got a 'main' secondary field, let's see if that matches what field we're on which means it contains the label for this field.
					if ( $secondary_field == 'main' && $main_field == $field_label ) 
						$field_label = $secondary_field['label'];
				}
			} 

			//field is present. Let's validate shortcodes first (but only if shortcodes present).
			if ( isset( $this->_validators[$field]['shortcodes'] ) && !empty( $this->_validators[$field]['shortcodes'] ) ) {
				$invalid_shortcodes = $this->_invalid_shortcodes( $value, $shortcodes );
				//if true then that means there is a returned error message that we'll need to add to the _errors array for this field.
				if ( $invalid_shortcodes ) {
					$this->_errors[$field] = sprintf( __('<p>The following shortcodes were found in the %s field that ARE not valid: %s</p>', 'event_espresso'), $field_label, $invalid_shortcodes );
					$this->_errors[$field] .= sprintf( __('<p>Valid shortcodes for this field are: %s', 'event_espresso'), implode(',', $shortcodes ) );
				}
			}

			//if there's a "type" to be validated then let's do that too.
			if ( isset( $this->_validators[$field]['type'] ) && !empty( $this->_validators[$field]['type'] ) ) {
				switch ( $this->_validators[$field]['type'] ) {
					case 'number' :
						if ( !is_numeric($value) )
							$this->_errors[$field] .= sprintf( __('<p>The %s field is supposed to be a number. The value given (%s)  is not.  Please doublecheck and make sure the field contains a number</p>', 'event_espresso'), $field_label, $value );
						break;
					case 'email' :
						$invalid_email = $this->_validate_email($value);
						if ( $invalid_email )
							$this->_errors[$field] .= sprintf( __('<p>The %s field has strings that are not valid emails.  Valid emails are in the format: "Name <email@something.com>" or "email@something.com" and multiple emails can be separated by a comma.'), $field_label );
						break;
					default :
						break;
				}
			}

			//if _errors is empty for this field then let's make sure it gets unset so we don't accidentally trigger a validation fail!
			if ( empty($this->_errors[$field] ) ) unset($this->_errors[$field]);
		}
	}



	/**
	 * Validates a string against a list of accepted shortcodes
	 *
	 * This function takes in an array of shortcodes and makes sure that the given string ONLY contains shortcodes in that array.
	 * 	
	 * @param  string $value      string to evaluate
	 * @param  array  $valid_shortcodes array of shortcodes that are acceptable.
	 * @return mixed (bool|string)  return either a list of invalid shortcodes OR false if the shortcodes validate.
	 */
	private function _invalid_shortcodes($value, $valid_shortcodes) {
		//first we need to go through the string and get the shortcodes in the string
		$sc = preg_match_all( '/(\[.+?\])/', $value, $matches );
		$incoming_shortcodes = (array) $matches[0];

		//get a diff of the shortcodes in the string vs the valid shortcodes
		$diff = array_diff( $incoming_shortcodes, $valid_shortcodes );

		if ( empty( $diff ) ) return FALSE; //there is no diff, we have no invalid shortcodes, so return

		//made it here? then let's assemble the error message
		$invalid_shortcodes = implode( '</strong>,<strong>', $diff );
		$invalid_shortcodes = '<strong>' . $invalid_shortcodes . '</strong>';
		return $invalid_shortcodes;
	}




	/**
	 * Validates an incoming string and makes sure we have valid emails in the string.
	 * @param  string $value incoming value to validate
	 * @return bool        true if the string validates, false if it doesn't
	 */
	private function _validate_email( $value ) {
		$validate = TRUE;

		//first we need to split up the string if its comma delimited.
		$emails = explode(',', $value);

		//now let's loop through the emails and do our checks
		foreach ( $emails as $email ) {

			//first just check if this is a straight email address and continue if is
			if ( $validate = is_email( $email ) ) continue;

			//if false then we move to the next check.  Is this in the format "Foo <email@somethign.com)"?
			$validate = preg_match( '/(.*)<(.+)>/', $value, $matches ) ) ? TRUE : FALSE;

			//if FALSE here then we return because there is an invalid email.  Otherwise we do a final check on the email address.
			if ( !$validate ) return FALSE;
			
			$validate = is_email($matches[2]);

			if ( !$validate ) return FALSE;
		}

		return $validate;

	}

}