<?php

declare(strict_types=1);

namespace Drupal\yse_style_options_extras\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "yse_css_variable",
 *   label = @Translation("CSS Variable")
 * )
 */
class YseCssVariable extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $form['css_var'] = [
      '#type' => 'textfield',
      '#title' => $this->getLabel(),
      '#default_value' => $this->getValue('css_var') ?? $this->getDefaultValue(),
      '#wrapper_attributes' => [],
      '#description' => $this->getConfiguration('description'),
    ];

    if ($this->hasConfiguration('params')) {
      $form['css_var']['#type'] = 'number';
      $params = $this->getConfiguration('params');

      foreach($params as $idx => $pair){
        $form['css_var'][$pair['param']] = $pair['value'];
      }
    }
    return $form;
  }


  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $css_var = $this->getConfiguration('css_var') ?? 'empty-var';
    $format  = $this->getConfiguration('format');
    $value = $this->getValue('css_var') ?? NULL;
    if (!empty($value)) {
      $value = !empty($format) ? sprintf($format, $value) : $value;
      $build['#attributes']['css_vars'][$css_var] = "--{$css_var}: {$value};";
    }
    return $build;
  }
}
