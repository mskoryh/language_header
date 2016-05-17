<?php
/**
 * @file
 * Language Cookie admin settings form.
 */

/**
 * The URL language provider configuration form.
 */
function language_header_admin_form($form, &$form_state) {
  $form['language_header_header'] = array(
    '#title' => t('HTTP header name'),
    '#type' => 'textfield',
    '#default_value' => variable_get('language_header_header', 'X-Accept-Language'),
    '#description' => t('HTTP header name.'),
  );

  $form_state['redirect'] = 'admin/config/regional/language/configure';
  return system_settings_form($form);
}
