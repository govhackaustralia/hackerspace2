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
    $jurisdictions = [];
    foreach(\Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('jurisdiction') as $term) {
      $jurisdictions[$term->tid] = $term->name;
    }
    $response = new Response();
    $query = \Drupal::entityQuery('paragraph');
    $query->condition('type', 'team_member');
    $tms = [];

    foreach (\Drupal::entityManager()->getStorage('paragraph')->loadMultiple($query->execute()) as $tm) {
      $tms[] = [
        "project_id" => $tm->parent_id->value,
        "project_name" => $tm->getParentEntity()->title->value,
        "team_name" => $tm->getParentEntity()->field_team_name->value,
        "name" => $tm->field_name[0]->value,
        "email" => $tm->field_email[0]->value,
        "telephone" => $tm->field_telephone[0]->value,
        "captain" => ($tm->field_captain[0]->value == 1 ? "Yes" : "No"),
        "jurisdiction" => $jurisdictions[$tm->getParentEntity()->field_jurisdiction[0]->target_id],
      ];
    }
    $response->setContent(\Drupal::service('serializer')->serialize($tms, 'csv'));
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename=team_export.' . date("Ymd\THis") . '.csv');
    return $response;
  }
  public function project_export()
  {
    $jurisdictions = [];
    foreach(\Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('jurisdiction') as $term) {
      $jurisdictions[$term->tid] = $term->name;
    }
    $event_locations = [];
    foreach(\Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('event_location') as $term) {
      $event_locations[$term->tid] = $term->name;
    }
    $prizes = [];
    foreach(\Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('prize') as $term) {
      $prizes[$term->tid] = $term->name;
    }

    $response = new Response();
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'project');
    $tms = [];

    foreach (\Drupal::entityManager()->getStorage('node')->loadMultiple($query->execute()) as $tm) {
      foreach ($tm->field_prizes as $prize) {
        $tms[] = [
          "project_id" => $tm->nid->value,
          "team_name" => $tm->field_team_name->value,
          "project_name" => $tm->title->value,
          "description" => $tm->body->value,
          "further_opportunities" => $tm->field_further_opportunities->value,
          "jurisdiction" => $jurisdictions[$tm->field_jurisdiction[0]->target_id],
          "event_location" => $event_locations[$tm->field_location[0]->target_id],
          "jurisdiction_id" => $tm->field_jurisdiction[0]->target_id,
          "event_location_id" => $tm->field_location[0]->target_id,
          "prize_id" => $prize->target_id,
          "prize" => $prizes[$prize->target_id]
        ];
      }
    }
    $response->setContent(\Drupal::service('serializer')->serialize($tms, 'csv'));
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename=project_export.' . date("Ymd\THis") . '.csv');
    return $response;
  }
}
