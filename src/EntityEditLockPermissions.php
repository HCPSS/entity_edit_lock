<?php

namespace Drupal\entity_edit_lock;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityType;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\eck\Entity\EckEntityType;
use Drupal\Core\Entity\EntityTypeRepository;

/**
 * Provides dynamic permissions for paragraphs of different types.
 */
class EntityEditLockPermissions {

  use StringTranslationTrait;

  /**
   * Return an array of paragraph type permissions.
   *
   * @return array
   *   The paragraph type permissions.
   */
  public function entityTypePermissions() {
    $entityDefinitions = \Drupal::service('entity_type.manager')->getDefinitions();

    $perms = [];
    foreach ($entityDefinitions as $definition) {
      $types = $definition->getClass()::loadMultiple();
      foreach ($types as $bundle => $type) {
        if ($type instanceof ConfigEntityBundleBase && $type instanceof ThirdPartySettingsInterface) {
          if ($type->getThirdPartySetting('entity_edit_lock', 'lockable', FALSE)) {
            $perms += $this->buildPermissions($type);
          }
        }
      }
    }

    return $perms;
  }

  /**
   * Returns a list of paragraph permissions for the given paragraph type.
   *
   * @param ContentEntityTypeInterface $type
   *   The Entity type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(ConfigEntityBundleBase $definition) {
    $bundle = $definition->getConfigTarget();
    $entity_type_id = $definition->getEntityType()->getBundleOf();

    $type_params = [
      '%entity_type_id' => $entity_type_id,
      '%bundle' => $bundle,
    ];

    return [
      "lock $bundle $entity_type_id" => [
        'title' => $this->t('%bundle: Lock %entity_type_id', $type_params),
      ],
      "edit locked $bundle $entity_type_id" => [
        'title' => $this->t('%bundle: Edit locked %entity_type_id', $type_params),
      ],
      "delete locked $bundle $entity_type_id" => [
        'title' => $this->t('%bundle: Delete locked %entity_type_id', $type_params),
      ],
    ];
  }
}
