<?php

namespace Drupal\entity_name_retriever;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Ban IP manager.
 */
class EntityNameRetriever {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $currentPath;

  /**
   * Construct the EntityNameRetriever.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   */
  public function __construct(EntityTypeManager $entity_type_manager, CurrentPathStack $current_path) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentPath = $current_path;
  }

  /**
   * Return user name from user page.
   */
  public function getUserFromRoute() {
    $result = 'There is no such user';
    $current_path = $this->currentPath->getPath();
    $path_arr = explode('/', $current_path);

    if ($path_arr[1] == 'user') {
      $uid = $path_arr[2];
      $account = $this->entityTypeManager->getStorage('user')->load($uid);
      if (!empty($account)) {
        $result = $account->getUsername();
      }
    }

    return $result;
  }

  /**
   * Return node title from node page.
   */
  public function getNodeFromRoute() {
    $result = 'There is no such node';
    $current_path = $this->currentPath->getPath();
    $path_arr = explode('/', $current_path);

    if ($path_arr[1] == 'node') {
      $nid = $path_arr[2];
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      if (!empty($node)) {
        $result = $node->label();
      }
    }
    return $result;
  }

}
