<?php
/**
 * @file
 * Language header admin settings form.
 */

/**
 * Configuration form of "language_header" provider.
 */
function language_header_admin_form(array $form, array &$form_state) {
  $form['language_header_header'] = array(
    '#title' => t('HTTP header name'),
    '#type' => 'textfield',
    '#default_value' => variable_get('language_header_header', LANGUAGE_HEADER_HEADER),
  );

  $form_state['redirect'] = 'admin/config/regional/language/configure';

  return system_settings_form($form);
}
