<?php

declare(strict_types=1);

namespace Drupal\yse_style_options_extras\Plugin\StyleOption;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "yse_inline_pair",
 *   label = @Translation("CSS Inline Pair"),
 *   weight = 1000
 * )
 */
class YseInlinePair extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    if (!$this->hasConfiguration('property')) {
      return $form;
    }

    $form['in_pair'] = [
      '#type' => 'textfield',
      '#title' => $this->getLabel(),
      '#default_value' => $this->getValue('in_pair') ?? $this->getDefaultValue(),
      '#wrapper_attributes' => [],
      '#description' => $this->getConfiguration('description'),
    ];

    if ($this->hasConfiguration('params')) {
      $form['in_pair']['#type'] = 'number';
      $params = $this->getConfiguration('params');

      foreach ($params as $idx => $pair) {
        $form['in_pair'][$pair['param']] = $pair['value'];
      }
    }

    if ($this->hasConfiguration('options')) {
      $form['in_pair']['#type'] = 'select';
      $options = $this->getConfiguration()['options'];
      array_walk($options, function (&$option) {
        $option = $option['label'];
      });
      if ($this->hasConfiguration('multiple')) {
        $form['in_pair']['#multiple'] = TRUE;
      }
      $form['in_pair']['#options'] = $options;
    }

    return $form;
  }


  /**
   * {@inheritDoc}
   */
  public function build(array $build) {

    //if we need to use the option ID, it will be presented as a css var with the double dash prefix.
    $property = $this->getConfiguration('property') ?? static::_generate_css_property($this->getOptionId());
    //default is reserved for the style_options options default value, fallback is used for the rule
    $default = $this->hasConfiguration('default') ? $this->getDefaultValue() : NULL;
    $fallback = $this->hasConfiguration(name: 'fallback') ? $this->getConfiguration('fallback') : NULL;
    //likely other plugins at work, break existing string into pairs for key-based overrides.
    $prev_pairs = isset($build['#attributes']['style']) ? static::_parse_inline_css($build['#attributes']['style']) : [];
    //options and format tell us how extract the wanted value from the input.
    $options = $this->hasConfiguration('options') ? $this->getConfiguration('options') : [];
    $format = $this->hasConfiguration('format') ? $this->getConfiguration('format') : NULL;
    $value = $this->getValue('in_pair') ?? $default;

    if (empty($value)) {
      return $build;
    }
    //options mean we used a select
    if (!empty($options)) {
      $idx = $value;
      $value = $options[$idx]['value'] ?? NULL;
    }
    //format could supply the double dash prefix, suffix with 'px' or '%', etc.
    $value = !empty($format) ? sprintf($format, $value) : $value;
    //set the property-value pair
    $inline_pairs = static::_set_property_pair($property, $value, $fallback);
    //build the inline css
    $build['#attributes']['style'] = static::_build_inline_css($inline_pairs, $prev_pairs);

    return $build;
  }

  /**
   * Builds a CSS property-value pair.
   *
   * @param string $property
   *   The CSS property name.
   * @param string $value
   *   The CSS property value.
   * @param string|null $fallback
   *   (optional) A fallback value for the CSS property.
   *
   * @return array
   *   An associative array containing the CSS property-value pair.
   */
  function _set_property_pair($property, $value, $fallback = NULL) {
    if ($property && $value) {
      $assignment = $value;
      if (str_starts_with($value, '--')) {
        //let us see if str_starts_with takes null in stride and gives false.
        $inner = str_starts_with($fallback, '--') ? "var({$fallback})" : $fallback;
        $assignment = $inner ? "var({$value}, {$inner})" : "var({$value})";
      }
      return [$property => $assignment];
    }
    else {
      return [];
    }
  }


  /**
   * Parses an inline CSS string and returns an array of key-value pairs.
   *
   * @param string|null $css_string
   *   The inline CSS string to parse.
   *
   * @return array
   *   An associative array of parsed CSS property-value pairs.
   */
  function _parse_inline_css(?string $css_string): array {
    $parsed_pairs = [];
    if (empty($css_string)) {
      return $parsed_pairs;
    }

    //Ta, Claude.
    $pattern = '/(?:^|;)\s*(?P<property>[-\w]+(?:-\w+)*)\s*:\s*(?P<value>(?:var\(--[^)]+\)|[^;"\']*|"(?:[^"]*|\\")+"|\'(?:[^\']*|\\\')+")+)/m';
    preg_match_all($pattern, $css_string, $matches);
    foreach ($matches['property'] as $i => $property) {
      $parsed_pairs[$property] = $matches['value'][$i];
    }
    return $parsed_pairs;
  }

  /**
   * Builds an inline CSS string from an array of property-value pairs.
   * Takes existing pairs from the style attribute and merges them with the input pairs.
   * Pairs entered in the input_pairs will override values from keys existing prev_pairs
   * if found.
   *
   * @param array $input_pairs
   *   An associative array of CSS property-value pairs to include in the output.
   * @param array $prev_pairs
   *   An associative array of previously defined CSS property-value pairs to include in the output.
   *
   * @return string
   *   The generated inline CSS string.
   */
  function _build_inline_css($input_pairs = [], $prev_pairs = []) {
    $css_string = "";
    $join_pairs = array_merge($prev_pairs, $input_pairs);
    foreach ($join_pairs as $property => $value) {
      $css_string .= "{$property}: {$value}; ";
    }
    return $css_string;
  }

  /**
   * Generates a CSS variable name from the given option ID.
   * Runs the option ID through the Html::cleanCssIdentifier()
   * function to convert it to a valid CSS variable name.
   *
   * @param string $option_id
   *   The option ID to convert to a CSS variable name.
   *
   * @return string
   *   The generated CSS variable name.
   */
  function _generate_css_property(string $option_id): string {
    $converted = mb_strtolower(Html::cleanCssIdentifier($option_id));
    return "--{$converted}";
  }
}
