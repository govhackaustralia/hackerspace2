<?php

namespace Drupal\hackerspace\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class HackerspaceController extends ControllerBase
{

  public function team_export()
  {
    $response = new Response();
    $query = \Drupal::entityQuery('paragraph');


    $query->condition('type', 'team_member');
    $tms = [];

    foreach (\Drupal::entityManager()->getStorage('paragraph')->loadMultiple($query->execute()) as $tm) {
      $tms[] = [
        "team_id" => $tm->parent_id->value,
        "project_name" => $tm->getParentEntity()->title->value,
        "team_name" => $tm->getParentEntity()->title->value,
        "name" => $tm->field_name[0]->value,
        "email" => $tm->field_email[0]->value,
        "telephone" => $tm->field_telephone[0]->value,
        "captain" => ($tm->field_captain[0]->value == 1 ? "Yes" : "No")
      ];
    }
    $response->setContent(\Drupal::service('serializer')->serialize($tms, 'csv'));
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename=team_export.' . date("Ymd\THis") . '.csv');
    return $response;
  }
}
