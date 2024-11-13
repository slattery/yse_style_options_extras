<?php

namespace Drupal\yse_style_options_extras\Plugin\Discovery;

use Drupal\Component\Discovery\DiscoverableInterface;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Allows ParagraphType settings to issue plugin definitions
 * For use with YamlDiscoveryDecorator
 * uses DiscoverableInterface not DiscoveryInterface!
 */
class ParagraphTypeConfigBehaviorDiscovery implements DiscoverableInterface {

  /**
   * The enabled behavior key to look for in ParagraphType configuration.
   *
   * @var string
   */
  protected $behaviorPluginId;

  /**
   * Constructs a ParagraphTypeConfigBehaviorDiscovery object.
   *
   * @param string $behavior_plugin_id
   */
  public function __construct($behavior_plugin_id) {
    $this->behaviorPluginId = $behavior_plugin_id;
  }

  /**
   * {@inheritdoc}
   *  TODO: figure out hardcoded 'settings' key
   *   go to two fields (options, contexts) or get concensus on key
   */
  public function findAll() {
    $definitions = [];
    $paragraph_types = ParagraphsType::loadMultiple();
    foreach ($paragraph_types as $type_id => $paragraph_type) {
      if ( array_key_exists($this->behaviorPluginId, $paragraph_type->getEnabledBehaviorPlugins()) ){
        $plugin = $paragraph_type->getBehaviorPlugin($this->behaviorPluginId);
        $config = $plugin->getConfiguration();
        if ($config && !empty($config['settings'])) {
          //Make these resemble the plugin format
          $settings = $config['settings'];
          $settings['options']['provider']  = $type_id;
          $settings['options']['id']        =  'options';
          $settings['contexts']['provider'] = $type_id;
          $settings['contexts']['id']       =  'contexts';
          $definitions[$type_id] = $settings;
        }
      }
    }
    return $definitions;
  }
}
