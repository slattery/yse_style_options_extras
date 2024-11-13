<?php

declare(strict_types=1);

namespace Drupal\yse_style_options_extras\Plugin\StyleOption;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the data attribute option plugin.
 *
 * @StyleOption(
 *   id = "yse_data_attribute",
 *   label = @Translation("Data Attribute"),
 *   weight = 1000
 * )
 */
class YseDataAttribute extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    if (!$this->hasConfiguration('attribute')) {
      return $form;
    }

    $method = $this->hasConfiguration('method') ? $this->getConfiguration('method') : NULL;

    $form['css_attr'] = [
      '#type' => 'textarea',
      '#title' => $this->getLabel(),
      '#default_value' => $this->getValue('css_attr') ?? $this->getDefaultValue(),
      '#description' => $this->getConfiguration('description'),
    ];

    if ($method && $method == 'set_boolean_true') {
      $form['css_attr']['#type'] = 'checkbox';
      $form['css_attr']['#return_value'] = 'true';
    }
    elseif ($method && $method == 'set_boolean_string') {
      $form['css_attr']['#type'] = 'radios';
      $form['css_attr']['#options'] = ['true' => $this->t('True'), 'false' => $this->t('False')];
      $form['css_attr']['#default_value'] = $this->getValue('css_attr') ?? 'false';
    }

    if ($this->hasConfiguration('params')) {
      $form['css_attr']['#type'] = 'number';
      $params = $this->getConfiguration('params');

      foreach ($params as $idx => $pair) {
        $form['css_attr'][$pair['param']] = $pair['value'];
      }
    }

    if ($this->hasConfiguration('options')) {
      $options = $this->getConfiguration()['options'];

      if ($method && $method == 'set_boolean_string') {
        //make key:val from option list of arrays, grab true/false, use labels
        $ac = array_column($options, 'label', 'value');
        $tf = array_intersect_key($ac, $form['css_attr']['#options']);
        $form['css_attr']['#options'] = array_merge($form['css_attr']['#options'], $tf);
      }
      else {
        //make an indexed list of labels
        $form['css_attr']['#type'] = 'select';
        array_walk($options, function (&$option) {
          $option = $option['label'];
        });
        if ($this->hasConfiguration('multiple')) {
          $form['css_attr']['#multiple'] = TRUE;
        }
        $form['css_attr']['#options'] = $options;
      }
    }

    if ($form['css_attr']['#type'] == 'textarea' && $this->hasConfiguration('placeholder')) {
      $form['css_attr']['#placeholder'] = $this->getConfiguration('placeholder');
    }

    return $form;
  }


  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    //we do not add prefixed dashed to variable names, those must be part of the config or value.

    $attribute = $this->getConfiguration('attribute') ?? $this->getOptionId();
    $method = $this->hasConfiguration('method') ? $this->getConfiguration('method') : NULL;
    $options = $this->hasConfiguration('options') ? $this->getConfiguration('options') : [];
    $format = $this->hasConfiguration('format') ? $this->getConfiguration('format') : NULL;

    //default is reserved for the style_options options default value, fallback is used for the rule
    $default = $this->hasConfiguration('default') ? $this->getDefaultValue() : NULL;
    $value = $this->getValue('css_attr') ?? $default;


    if (empty($value)) {
      return $build;
    }

    if (!empty($options) && $method != 'set_boolean_string') {
      //options without boolean mean we used a select
      $idx = $value;
      $value = $options[$idx]['value'] ?? NULL;
    }

    //params or other inputs
    $value = !empty($format) ? sprintf($format, $value) : $value;


    switch ($method) {
      //may grow a set_library method someday.
      case 'set_boolean_true':
        //do not print if false, the presence of a attribute is true
        if ($value) {
          $build['#attributes'][$attribute] = TRUE;
        }
        //what happens if you just assign the true/false will it not store?
        break;
      case 'set_boolean_string':
        //print string that reflects boolean state
        $build['#attributes'][$attribute] = $value ?? 'false';
        break;
      case 'set_textentry':
        $free_pairs = static::_parse_textentry($value);
        if (!empty($free_pairs)) {
          foreach ($free_pairs as $k => $v) {
            $build['#attributes'][$k] = $v;
          }
        }
        break;
      case 'set_attribute':
      default:
        if ($value) {
          $build['#attributes'][$attribute] = $value;
        }
        break;
    }

    return $build;
  }

  /**
   * Parses a string of data attribute pairs.
   *
   * The string should be in the format:
   * "data-foo|bar\ndata-baz|qux"
   *
   * This method will return an associative array where the keys are the data
   * attribute names and the values are the corresponding attribute values.
   * If no value is provided, the value will be set to TRUE.
   *
   * @param string $textfield_lines
   *   A string containing one or more data attribute pairs.
   *
   * @return array
   *   An associative array of parsed data attributes.
   */
  function _parse_textentry($textfield_lines) {
    $attr_pairs = [];
    $attr_lines = preg_split('/\R/', $textfield_lines);
    foreach ($attr_lines as $attr_line) {
      $attr_pair = explode('|', trim($attr_line));
      if (str_starts_with($attr_line, 'data-')) {
        // Values are optional for data-* attributes.
        $attr_pairs[$attr_pair[0]] = $attr_pair[1] ?? TRUE;
      }
    }
    return $attr_pairs;
  }

}

//Someday maybe we can use optgroups

