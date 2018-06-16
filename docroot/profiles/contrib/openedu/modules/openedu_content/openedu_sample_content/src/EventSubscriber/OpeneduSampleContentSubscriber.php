<?php

namespace Drupal\openedu_sample_content\EventSubscriber;

use Drupal\default_content\Event\DefaultContentEvents;
use Drupal\default_content\Event\ImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OpeneduSampleContentSubscriber.
 *
 * @package Drupal\openedu_sample_content\EventSubscriber
 */
class OpeneduSampleContentSubscriber implements EventSubscriberInterface {

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * The array keys are event names and the value can be:
   *
   *  * The method name to call (priority defaults to 0)
   *  * An array composed of the method name to call and the priority
   *  * An array of arrays composed of the method names to call and respective
   *    priorities, or 0 if unset
   *
   * @return array
   *   The event names to listen to
   */
  public static function getSubscribedEvents() {
    $events[DefaultContentEvents::IMPORT][] = ['onImport'];
    return $events;
  }

  /**
   * Publish nodes.
   *
   * Default content doesn't support moderation yet,
   * so set as published here.
   *
   * @param \Drupal\default_content\Event\ImportEvent $event
   *   The Import Event.
   */
  public function onImport(ImportEvent $event) {
    $entities = $event->getImportedEntities();

    foreach ($entities as $entity) {
      if ($entity->getEntityTypeId() == 'node') {
        $entity->set('moderation_state', 'published');
        $entity->save();
      }
    }
  }

}
