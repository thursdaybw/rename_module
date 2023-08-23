<?php

namespace Drupal\rename_module;

use \Drupal\Core\Extension\ModuleHandlerInterface;
use Drush\Drupal\ExtensionDiscovery;
use Symfony\Component\Yaml\Yaml;

/**
 * Service class for renaming custom Drupal modules.
 */
class RenameModuleService {

  /**
   * Constructs a new RenameModuleService object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(
    readonly ModuleHandlerInterface $module_handler
  ) {}

  /**
   * Renames a custom Drupal module.
   *
   * @param string $old_name
   *   The old module name.
   * @param string $new_name
   *   The new module name.
   */
  public function renameModule($old_name, $new_name) {
    $module_path = $this->locateModule($old_name);
    $this->replaceOccurrences($module_path, $old_name, $new_name);
    $this->renameFiles($module_path, $old_name, $new_name);
    $this->renameDirectory($module_path, $old_name, $new_name);
  }

  /**
   * Locates the module directory.
   *
   * @param string $old_name
   *   The old module name.
   *
   * @return string
   *   The module path.
   */
  protected function locateModule($old_name) {
    $extension_discovery = new ExtensionDiscovery(\Drupal::root());
    $extension_discovery->setProfileDirectories([]);
    $modules = $extension_discovery->scan('module');

    if (isset($modules[$old_name])) {
      $module_path = $modules[$old_name]->getPath();
      if (strpos($module_path, 'custom') !== FALSE) {
        return $module_path;
      }
      else {
        $this->logger()
          ->warning("Module not located in the 'custom' directory: " . $old_name);
      }
    }

    return NULL;
  }

  /**
   * Renames the directory containing the module.
   *
   * @param string $module_path
   *   The module path.
   * @param string $old_name
   *   The old module name.
   * @param string $new_name
   *   The new module name.
   */
  protected function renameDirectory($module_path, $old_name, $new_name) {
    // Construct the new module path.
    $new_module_path = str_replace($old_name, $new_name, $module_path);

    // Check if the new module path already exists.
    if (is_dir($new_module_path)) {
      throw new \Exception("The target directory already exists: " . $new_name);
    }

    // Rename the directory.
    if (rename($module_path, $new_module_path)) {
      return TRUE;
    }
    else {
      throw new \Exception("Failed to rename the directory from " . $old_name . " to " . $new_name);
    }
  }

  /**
   * Renames the files within the module directory.
   *
   * @param string $module_path
   *   The module path.
   * @param string $old_name
   *   The old module name.
   * @param string $new_name
   *   The new module name.
   */
  protected function renameFiles($module_path, $old_name, $new_name) {
    $files = glob($module_path . '/' . $old_name . '*');
    foreach ($files as $file) {
      $path_parts = pathinfo($file);
      $new_filename = str_replace($old_name, $new_name, $path_parts['basename']);
      $new_filepath = $path_parts['dirname'] . '/' . $new_filename;
      if (!rename($file, $new_filepath)) {
        throw new \Exception("Failed to rename file: " . $file);
      }
    }
  }

  /**
   * Replaces occurrences of the old module name within the files.
   *
   * @param string $module_path
   *   The module path.
   * @param string $old_name
   *   The old module name.
   * @param string $new_name
   *   The new module name.
   *
   * @todo add "excludes" feature for extenstions, and subdirectories.
   *   - Could be config or parameters.
   */
  protected function replaceOccurrences($module_path, $old_name, $new_name) {
    $directory_iterator = new \RecursiveDirectoryIterator($module_path);
    $iterator = new \RecursiveIteratorIterator($directory_iterator);

    // List of extensions to treat as PHP code.
    $php_extensions = [
      'php',
      'module',
      'inc',
      'install',
      'profile',
      'engine',
      'theme'
    ];

    foreach ($iterator as $file) {
      if ($file->isFile()) {
        $content = file_get_contents($file->getPathname());
        $extension = pathinfo($file->getPathname(), PATHINFO_EXTENSION);

        // Handle YAML files.
/*        if ($extension === 'yml') {
          $parsed_yaml = Yaml::parse($content);
          array_walk_recursive($parsed_yaml, function(&$value) use ($old_name, $new_name) {
            $value = str_replace($old_name, $new_name, $value);
          });
          $new_content = Yaml::dump($parsed_yaml, 2, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        }*/
        // Handle PHP files.
        if (in_array($extension, $php_extensions)) {
          $tokens = token_get_all($content);
          $new_content = $this->processTokens($tokens, $old_name, $new_name);
        }
        // Handle everything else with simple string replacement.
        else {
          $new_content = str_replace($old_name, $new_name, $content);
        }

        file_put_contents($file->getPathname(), $new_content);
      }
    }
  }

  /**
   * Processes the PHP tokens to replace occurrences of the old module name.
   *
   * @param array $tokens
   *   The PHP tokens.
   * @param string $old_name
   *   The old module name.
   * @param string $new_name
   *   The new module name.
   *
   * @return string
   *   The updated content.
   */
  protected function processTokens($tokens, $old_name, $new_name) {
    $new_content = '';

    foreach ($tokens as $token) {
      if (is_array($token)) {
        [$token_id, $token_text, $line_number] = $token;

        // Check if the token text contains the old module name.
        if (strpos($token_text, $old_name) !== false) {
          $token_text = str_replace($old_name, $new_name, $token_text); // Replace with the new module name.
        }
      } else {
        $token_text = $token;
      }

      $new_content .= $token_text;
    }

    return $new_content;
  }

}

