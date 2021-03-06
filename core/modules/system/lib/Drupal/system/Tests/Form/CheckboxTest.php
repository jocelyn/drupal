<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Form\CheckboxTest.
 */

namespace Drupal\system\Tests\Form;

use Drupal\simpletest\WebTestBase;

/**
 * Tests checkbox element.
 */
class CheckboxTest extends WebTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Form API checkbox',
      'description' => 'Tests form API checkbox handling of various combinations of #default_value and #return_value.',
      'group' => 'Form API',
    );
  }

  function setUp() {
    parent::setUp('form_test');
  }

  function testFormCheckbox() {
    // Ensure that the checked state is determined and rendered correctly for
    // tricky combinations of default and return values.
    foreach (array(FALSE, NULL, TRUE, 0, '0', '', 1, '1', 'foobar', '1foobar') as $default_value) {
      // Only values that can be used for array indeces are supported for
      // #return_value, with the exception of integer 0, which is not supported.
      // @see form_process_checkbox().
      foreach (array('0', '', 1, '1', 'foobar', '1foobar') as $return_value) {
        $form_array = drupal_get_form('form_test_checkbox_type_juggling', $default_value, $return_value);
        $form = drupal_render($form_array);
        if ($default_value === TRUE) {
          $checked = TRUE;
        }
        elseif ($return_value === '0') {
          $checked = ($default_value === '0');
        }
        elseif ($return_value === '') {
          $checked = ($default_value === '');
        }
        elseif ($return_value === 1 || $return_value === '1') {
          $checked = ($default_value === 1 || $default_value === '1');
        }
        elseif ($return_value === 'foobar') {
          $checked = ($default_value === 'foobar');
        }
        elseif ($return_value === '1foobar') {
          $checked = ($default_value === '1foobar');
        }
        $checked_in_html = strpos($form, 'checked') !== FALSE;
        $message = t('#default_value is %default_value #return_value is %return_value.', array('%default_value' => var_export($default_value, TRUE), '%return_value' => var_export($return_value, TRUE)));
        $this->assertIdentical($checked, $checked_in_html, $message);
      }
    }

    // Ensure that $form_state['values'] is populated correctly for a checkboxes
    // group that includes a 0-indexed array of options.
    $results = json_decode($this->drupalPost('form-test/checkboxes-zero', array(), 'Save'));
    $this->assertIdentical($results->checkbox_off, array(0, 0, 0), t('All three in checkbox_off are zeroes: off.'));
    $this->assertIdentical($results->checkbox_zero_default, array('0', 0, 0), t('The first choice is on in checkbox_zero_default'));
    $this->assertIdentical($results->checkbox_string_zero_default, array('0', 0, 0), t('The first choice is on in checkbox_string_zero_default'));
    $edit = array('checkbox_off[0]' => '0');
    $results = json_decode($this->drupalPost('form-test/checkboxes-zero', $edit, 'Save'));
    $this->assertIdentical($results->checkbox_off, array('0', 0, 0), t('The first choice is on in checkbox_off but the rest is not'));

    // Ensure that each checkbox is rendered correctly for a checkboxes group
    // that includes a 0-indexed array of options.
    $this->drupalPost('form-test/checkboxes-zero/0', array(), 'Save');
    $checkboxes = $this->xpath('//input[@type="checkbox"]');
    foreach ($checkboxes as $checkbox) {
      $checked = isset($checkbox['checked']);
      $name = (string) $checkbox['name'];
      $this->assertIdentical($checked, $name == 'checkbox_zero_default[0]' || $name == 'checkbox_string_zero_default[0]', t('Checkbox %name correctly checked', array('%name' => $name)));
    }
    $edit = array('checkbox_off[0]' => '0');
    $this->drupalPost('form-test/checkboxes-zero/0', $edit, 'Save');
    $checkboxes = $this->xpath('//input[@type="checkbox"]');
    foreach ($checkboxes as $checkbox) {
      $checked = isset($checkbox['checked']);
      $name = (string) $checkbox['name'];
      $this->assertIdentical($checked, $name == 'checkbox_off[0]' || $name == 'checkbox_zero_default[0]' || $name == 'checkbox_string_zero_default[0]', t('Checkbox %name correctly checked', array('%name' => $name)));
    }
  }
}
