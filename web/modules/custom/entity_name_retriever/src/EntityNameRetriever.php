<?php

namespace Drupal\entity_name_retriever;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Ban IP manager.
 */
class EntityNameRetriever {

  protected $entityTypeManager;

  /**
   * Construct the EntityNameRetriever.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserFromRoute() {
    $result = 'There is no such user';
    $current_path = \Drupal::service('path.current')->getPath();
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
   * {@inheritdoc}
   */
  public function getNodeFromRoute() {
    $result = 'There is no such node';
    $current_path = \Drupal::service('path.current')->getPath();
    $path_arr = explode('/', $current_path);

    if ($path_arr[1] == 'node') {
      $nid = $path_arr[2];
      $node = $this->entityTypeManager->getStorage('node')->load($nid); // pass your uid
      if (!empty($node)) {
        $result = $node->label();
      }
    }
    return $result;
  }

}
