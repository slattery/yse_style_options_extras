<?php

declare(strict_types=1);


namespace Drupal\yse_style_options_extras\Service;

use Drupal\style_options\StyleOptionConfigurationDiscovery;
use Drupal\yse_style_options_extras\Plugin\Discovery\ParagraphTypeConfigBehaviorDiscovery;
use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\YamlDiscoveryDecorator;
use Drupal\yse_style_options_extras\Plugin\Discovery\YamlDiscoverableDecorator;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class StyleOptionConfigurationDiscoveryDecorator extends StyleOptionConfigurationDiscovery {

  /**
   * Original service object.
   *
   * @var \Drupal\style_options\StyleOptionConfigurationDiscovery
   */
  protected $originalService;

  /**
   * OriginalServiceOverride constructor.
   *
   * @param \Drupal\style_options\StyleOptionConfigurationDiscovery $original_service
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */

  public function __construct(StyleOptionConfigurationDiscovery $original_service, ThemeHandlerInterface $theme_handler, ModuleHandlerInterface $module_handler) {
    $this->originalService = $original_service;
    parent::__construct($theme_handler, $module_handler);
  }

  protected function discovery(): YamlDiscoverableDecorator {
    $dirdiscov = new ParagraphTypeConfigBehaviorDiscovery(static::CONFIG_NAME);
    return new YamlDiscoverableDecorator($dirdiscov, static::CONFIG_NAME,
      $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories());
  }

}
