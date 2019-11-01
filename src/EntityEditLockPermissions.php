<?php

namespace Drupal\entity_edit_lock;

use Drupal\Core\StringTranslation\StringTranslationTrait;

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
    $perms = [];

    $fids = \Drupal::entityQuery('field_config')
      ->condition('field_name', 'entity_edit_lock_locked')
      ->execute();

    if (!empty($fids)) {
      $fields = \Drupal::entityTypeManager()
        ->getStorage('field_config')
        ->loadMultiple($fids);

      foreach ($fields as $field) {
        /** @var \Drupal\field\Entity\FieldConfig $field */
        $perms += $this->buildPermissions(
          $field->getTargetEntityTypeId(),
          $field->getTargetBundle()
        );
      }
    }

    return $perms;
  }

  /**
   * Returns a list of paragraph permissions for the given paragraph type.
   *
   * @param string $entity_type_id
   *   The Entity type id.
   *
   * @param string $bundle
   *   The bundle.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions($entity_type_id, $bundle) {
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
