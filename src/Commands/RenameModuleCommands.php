<?php

namespace Drupal\rename_module\Commands;

use Drush\Commands\DrushCommands;
use Drupal\rename_module\RenameModuleService;

/**
 * A Drush command file for the Rename Module.
 */
class RenameModuleCommands extends DrushCommands {

  /**
   * The Rename Module service.
   *
   * @var \Drupal\rename_module\RenameModuleService
   */
  protected $renameModuleService;

  /**
   * Constructs a new RenameModuleCommands object.
   *
   * @param \Drupal\rename_module\RenameModuleService $rename_module_service
   *   The Rename Module service.
   */
  public function __construct(RenameModuleService $rename_module_service) {
    $this->renameModuleService = $rename_module_service;
  }

  /**
   * Renames a custom Drupal module.
   *
   * @param string $old_name
   *   The old module name.
   * @param string $new_name
   *   The new module name.
   *
   * @command rename-module:rename
   *  * @aliases rm-rename
   * @usage drush rename-module:rename old_module_name new_module_name
   *   Renames the custom Drupal module "old_module_name" to "new_module_name".
   * @description
   *   This command renames a custom Drupal module.
   *   Including its directory, files, and occurrences within the code.
   * @validate-module-enabled rename_module
   */
  public function rename($old_name, $new_name) {
    try {
      $this->renameModuleService->renameModule($old_name, $new_name);
      $this->logger()->success("Module renamed successfully!");
    }
    catch (\Exception $e) {
      $this->logger()->error($e->getMessage());
    }
  }

}
