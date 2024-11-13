<?php

namespace Drupal\yse_style_options_extras\Plugin\Discovery;

use Drupal\Component\Discovery\DiscoverableInterface;
use Drupal\Core\Discovery\YamlDiscovery;

/**
 * Enables YAML discovery for plugin definitions.
 *
 * You should normally extend this class to add validation for the values in the
 * YAML data or to restrict use of the class or derivatives keys.
 */
class YamlDiscoverableDecorator extends YamlDiscovery {

  /**
   * The Discovery object being decorated.
   *
   * @var \Drupal\Component\Discovery\DiscoverableInterface;
   */
  protected $decorated;

  /**
   * Constructs a YamlDiscoverableDecorator object.
   *
   * @param \Drupal\Component\Discovery\DiscoverableInterface $decorated
   *   The discovery object that is being decorated.
   * @param string $name
   *   The file name suffix to use for discovery; for instance, 'test' will
   *   become 'MODULE.test.yml'.
   * @param array $directories
   *   An array of directories to scan.
   */
  public function __construct(DiscoverableInterface $decorated, $name, array $directories) {
    parent::__construct($name, $directories);

    $this->decorated = $decorated;
  }

  /**
   * {@inheritdoc}
   */
  public function findAll() {
    \Drupal::logger('yse_style_options_extras')->notice('decorating all');

    return parent::findAll() + $this->decorated->findAll();
  }

  /**
   * Passes through all unknown calls onto the decorated object.
   */
  public function __call($method, $args) {
    return call_user_func_array([$this->decorated, $method], $args);
  }

}
