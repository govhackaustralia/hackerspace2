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
    foreach ($projects as $project) {
      $markup .= '<li><a href="' . $project->url() . '">' . $project->title->value . '</a></li>';
    }
    $markup .= '</ul>';

    return ['#markup' => $markup];
  }

  public function team_export()
  {
    $jurisdictions = [];
    foreach (\Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('jurisdiction') as $term) {
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
    foreach (\Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('jurisdiction') as $term) {
      $jurisdictions[$term->tid] = $term->name;
    }
    $event_locations = [];
    foreach (\Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('event_location') as $term) {
      $event_locations[$term->tid] = $term->name;
    }
    $prizes = [];
    foreach (\Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('prize') as $term) {
      $prizes[$term->tid] = $term->name;
    }

    $response = new Response();
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'project');
    $query->condition('field_jurisdiction', '39');
    $tms = [];

    foreach (\Drupal::entityManager()->getStorage('node')->loadMultiple($query->execute()) as $tm) {

      $project = [
        "project_id" => $tm->nid->value,
        "team_name" => $tm->field_team_name->value,
        "project_name" => $tm->title->value,
        "project_image" => ($tm->field_project_image[0] ?
          \Drupal\file\Entity\File::load($tm->field_project_image[0]->getValue()['target_id'])->url()
        : ""),
        "description" => $tm->body->value,
        "further_opportunities" => $tm->field_further_opportunities->value,
        "jurisdiction" => $jurisdictions[$tm->field_jurisdiction[0]->target_id],
        "event_location" => $event_locations[$tm->field_location[0]->target_id],
        "jurisdiction_id" => $tm->field_jurisdiction[0]->target_id,
        "event_location_id" => $tm->field_location[0]->target_id,
        "datasets_used" => Array(),
        "entry_explanations" => Array(),
        "source_url" => $tm->field_sources[0]->uri,
        "source_file" => ($tm->field_source_file[0] ?
          \Drupal\file\Entity\File::load($tm->field_source_file[0]->getValue()['target_id'])->url()
          : ""),

        "video_url" => $tm->field_video[0]->value,
        "video_file" => ($tm->field_video_file[0] ?
          \Drupal\file\Entity\File::load($tm->field_video_file[0]->getValue()['target_id'])->url()
        : "" ),
        "website" => $tm->field_website[0]->uri,

        "prize_id" => Array(),
        "prize" => Array()
      ];
      foreach ($tm->field_datasets_used as $dataset_id) {
        $dataset =  \Drupal::entityManager()->getStorage('paragraph')->load($dataset_id->target_id);
        $project["datasets_used"][] =  $dataset->field_dataset_name[0]->value.' @ '.$dataset->field_dataset_url[0]->uri;
      };
      foreach ($tm->field_entry_explanations as $entry_explanation_id) {
        $entry_explanation =  \Drupal::entityManager()->getStorage('paragraph')->load($entry_explanation_id->target_id);
        $project["entry_explanations"][] =   $prizes[$entry_explanation->field_alpha_award[0]->target_id].': '.$entry_explanation->field_alpha_entry_explanation[0]->value;
      };

      foreach ($tm->field_prizes as $prize) {
        $project["prize_id"][] = $prize->target_id;
        $project["prize"][] = $prizes[$prize->target_id];
      };

      $tms[] = $project;

      //var_dump($project);
    }
    $response->setContent(\Drupal::service('serializer')->serialize($tms, 'csv'));
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename=project_export.' . date("Ymd\THis") . '.csv');
    return $response;
  }
}
