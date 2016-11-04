<?php
if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class EE_Email_Field_Test
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.1
 */
class EE_Email_Field_Test extends EE_UnitTestCase {

	public function test_prepare_for_set() {
		$email_validation_levels = array(
			'basic' => true,
			'wp_default' => false,
			'i18n' => true,
			'i18n_dns' => true,
		);
		foreach ($email_validation_levels as $email_validation_level => $test_should_pass) {
			EE_Registry::instance()->CFG->registration->email_validation_level = $email_validation_level;
			$this->set_email_field_value($test_should_pass);
		}
	}



	/**
	 * @param bool $test_should_pass
	 * @throws \EE_Error
     */
	public function set_email_field_value($test_should_pass = true) {
		$international_email_address = 'jägerjürgen@deutschland.com';
		/** @var \EE_Email_Field $email_field */
		$email_field = EEM_Attendee::instance()->field_settings_for('ATT_email');
		$actual_email_address = $email_field->prepare_for_set($international_email_address);
		if ($test_should_pass) {
			$this->assertEquals(
				$international_email_address,
				$actual_email_address,
				sprintf(
					'Was ist das? Die E-Mail-Adresse des Teilnehmers sollte "%1$s", nicht "%2$s" sein!',
					$international_email_address,
					$actual_email_address
				)
			// translation:
			// What is this? The attendee's email address should be "jägerjürgen@deutschland.com", not "{actual result}"
			);
		} else {
			$this->assertNotEquals(
				$international_email_address,
				$actual_email_address,
				sprintf(
					'Was ist das? Die E-Mail-Adresse des Teilnehmers sollte "", nicht "%1$s" sein!',
					$actual_email_address
				)
			// translation:
			// What is this? The attendee's email address should be "", not "{actual result}"
			);
		}
	}

}
// End of file EE_Email_Field_Test.php
// Location: tests/testcases/core/db_models/fields/EE_Email_Field_Test.php