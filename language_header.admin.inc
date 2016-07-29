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
    '#type' => 'textfield',
    '#title' => t('HTTP header name'),
    '#default_value' => variable_get('language_header_header', LANGUAGE_HEADER_HEADER),
    '#description' => t('The name of request HTTP header which should contain <a href="@link" target="_blank">ISO 639-1</a> language code.', array(
      '@link' => 'https://en.wikipedia.org/wiki/ISO_639-1',
    )),
  );

  // Redirect to the overview table after submission.
  $form_state['redirect'] = dirname(LANGUAGE_HEADER_CONFIG_PATH);

  return system_settings_form($form);
}
