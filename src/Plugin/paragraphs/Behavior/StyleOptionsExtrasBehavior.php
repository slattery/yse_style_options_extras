<?php

namespace Drupal\yse_style_options_extras\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;
use Drupal\style_options\Plugin\paragraphs\Behavior\StyleOptionBehavior;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add a YAML text field to store local config with the paragraph type.
 * This class is called from a plugin_info_alter hook, we don't want it
 * discovered on its own so there is no annotation.
 */

class StyleOptionsExtrasBehavior extends StyleOptionBehavior {

  /**
   * Constructs a ParagraphsViewmodeBehavior object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   *
  */

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_field_manager);
  }
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager')
    );
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state){
    $yaml_default = '';
    if( $yaml_state = $this->configuration['settings'] ){
      try {
        // Attempt to parse the YAML
        $yaml_default = Yaml::dump($yaml_state, 5);
      }
      catch (ParseException $e) {
        $form_state->setErrorByName('yaml_dump', t('Invalid YAML syntax: @error', [
          '@error' => $e->getMessage(),
        ]));
      }
    }

    $form['settings'] = [
      '#type' => 'textarea',
      '#attributes' => ['data-yaml-editor' => 'true'],
      '#title' => t('Style Options Local Settings'),
      '#default_value' => $yaml_default,
      '#description' => t('Add YAML style_options config or leave blank to use globals'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);

    $style_option_strings = $form_state->getValue('settings');
    try {
      // Attempt to parse the YAML
      Yaml::parse($style_option_strings);
    }
    catch (ParseException $e) {
      $form_state->setErrorByName('yaml_parse', t('Invalid YAML syntax: @error', [
        '@error' => $e->getMessage(),
      ]));
    }
  }

  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $style_option_strings = $form_state->getValue('settings');
    $style_option_settings = Yaml::parse($style_option_strings);
    $this->configuration['settings'] = $style_option_settings;

    return parent::submitConfigurationForm($form, $form_state);
  }

}
