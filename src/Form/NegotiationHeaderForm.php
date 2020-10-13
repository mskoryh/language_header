<?php

namespace Drupal\language_header\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure the browser language negotiation method for this site.
 *
 * @internal
 */
class NegotiationHeaderForm extends ConfigFormBase {

  /**
   * The configurable language manager.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ConfigurableLanguageManagerInterface $language_manager) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'language_negotiation_configure_header_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['language_header.mappings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    // Initialize a language list to the ones available, including English.
    $languages = $this->languageManager->getLanguages();

    $existing_languages = [];
    foreach ($languages as $langcode => $language) {
      $existing_languages[$langcode] = $language->getName();
    }

    // If we have no languages available, present the list of predefined languages
    // only. If we do have already added languages, set up two option groups with
    // the list of existing and then predefined languages.
    if (empty($existing_languages)) {
      $language_options = $this->languageManager->getStandardLanguageListWithoutConfigured();
    }
    else {
      $language_options = [
        (string) $this->t('Existing languages') => $existing_languages,
        (string) $this->t('Languages not yet added') => $this->languageManager->getStandardLanguageListWithoutConfigured(),
      ];
    }

    $form['mappings'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Header name'),
        $this->t('Header value'),
        $this->t('Site language'),
      ],
      '#attributes' => ['id' => 'language-negotiation-browser'],
      '#empty' => $this->t('No header language mappings available.'),
    ];

    $mappings = $this->getMappings();
    foreach ($mappings as $header => $drupal_langcode) {
      list($header_name, $header_value) = explode(':', $header);
      $form['mappings'][$header] = [
        'header_name' => [
          '#title' => $this->t('Header name'),
          '#title_display' => 'invisible',
          '#type' => 'textfield',
          '#default_value' => $header_name,
          '#size' => 40,
        ],
        'header_value' => [
          '#title' => $this->t('Header value'),
          '#title_display' => 'invisible',
          '#type' => 'textfield',
          '#default_value' => $header_value,
          '#size' => 10,
        ],
        'drupal_langcode' => [
          '#title' => $this->t('Site language'),
          '#title_display' => 'invisible',
          '#type' => 'select',
          '#options' => $language_options,
          '#default_value' => $drupal_langcode,
        ],
      ];
    }

    // Add empty row.
    $form['new_mapping'] = [
      '#type' => 'details',
      '#title' => $this->t('Add a new mapping'),
      '#tree' => TRUE,
    ];
    $form['new_mapping']['header_name'] = [
      '#title' => $this->t('Header name'),
      '#type' => 'textfield',
      '#size' => 40,
    ];
    $form['new_mapping']['header_value'] = [
      '#title' => $this->t('Header value'),
      '#type' => 'textfield',
      '#size' => 10,
    ];
    $form['new_mapping']['drupal_langcode'] = [
      '#type' => 'select',
      '#title' => $this->t('Site language'),
      '#options' => $language_options,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $result = [];
    $mappings = $form_state->getValue('mappings');

    foreach ($mappings as $mapping) {
      if (($key = $this->getMappingKey($mapping))) {
        $result[$key] = $mapping['drupal_langcode'];
      }
    }

    $new_mapping = $form_state->getValue('new_mapping');
    if (($new_key = $this->getMappingKey($new_mapping))) {
      $result[$new_key] = $new_mapping['drupal_langcode'];
    }

    if (!empty($result)) {
      ksort($result);
      $config = $this->config('language_header.mappings');
      $config->setData(['map' => $result]);
      $config->save();
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Retrieves the http header langcode mapping.
   *
   * @return array
   *   The http header langcode mapping.
   */
  protected function getMappings() {
    $config = $this->config('language_header.mappings');
    if ($config->isNew()) {
      return [];
    }
    return $config->get('map');
  }

  protected function getMappingKey($mapping) {
    if (empty($mapping['header_name'])) {
      return FALSE;
    }
    $name = trim($mapping['header_name']);
    $value = trim($mapping['header_value']);
    return "$name:$value";
  }

}
