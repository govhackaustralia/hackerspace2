<?php

namespace Drupal\hackerspace\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class HackerspaceController extends ControllerBase
{
  public function my_projects()
  {
    $projects = [];

    $result = \Drupal::entityQuery('paragraph')
      ->condition('type', 'team_member')
      ->condition('field_email', \Drupal::currentUser()->getEmail())
      ->execute();
    foreach (\Drupal::entityManager()->getStorage('paragraph')->loadMultiple($result) as $tm) {
      $projects[$tm->getParentEntity()->id()] = $tm->getParentEntity();
    }
    $result = \Drupal::entityQuery('node')
      ->condition('type', 'project')
      ->condition('uid', \Drupal::currentUser()->id())
      ->execute();
    foreach (\Drupal::entityManager()->getStorage('node')->loadMultiple($result) as $node) {
      $projects[$node->id()] = $node;
    }
    $markup = '<a class="btn btn-info" href="/node/add/project">Create new project...</a><ul>';
    foreach($projects as $project) {
      $markup .= '<li><a href="'.$project->url().'">'.$project->title->value.'</a></li>';
    }
    $markup .= '</ul>';

    return ['#markup'=> $markup ];
  }
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
        "team_name" => $tm->getParentEntity()->field_team_name->value,
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
