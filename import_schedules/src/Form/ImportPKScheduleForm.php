<?php

namespace Drupal\import_schedules\Form;

use Drupal\Core\Form\FormBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;

class ImportPKScheduleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'import_pk_schedule_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add a delete button to remove all existing data
    $form['delete_all_data'] = [
      '#type' => 'button',
      '#value' => $this->t('Delete All Existing Data'),
      '#ajax' => [
        'callback' => '::deleteAllDataCallback',
        'wrapper' => 'messages',
        'effect' => 'fade',
      ],
      '#attributes' => [
        'class' => ['button--danger'],        
      ],
    ];
    $form['delete_all_data_description'] = [
      '#markup' => $this->t('Please delete all existing Preschool-schedule data before uploading the CSV file.'),
      '#prefix' => '<div class="description-text">',
      '#suffix' => '</div>',
    ]; 
    
    $form['messages'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'messages'], // The wrapper defined in the AJAX callback
    ];  


    $form['csv_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload CSV file'),
      '#description' => $this->t('Please upload the CSV file to import PK schedule data.'),
      '#upload_location' => 'public://import_schedules/',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],      
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function deleteAllDataCallback(array &$form, FormStateInterface $form_state) {
    // Load all nodes of type 'school_profile'
    $school_profiles = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'school_profile']);

    if (!empty($school_profiles)) {
      foreach ($school_profiles as $school_profile) {

        // Check if the node supports translations and use the default language if available
        if ($school_profile->isTranslatable() && $school_profile->hasTranslation('en')) {
          $school_profile = $school_profile->getTranslation('en');
        }

        // Load existing paragraphs related to the 'field_schedule' of the school profile
        $existing_paragraphs = $school_profile->get('field_schedule')->referencedEntities();

        foreach ($existing_paragraphs as $existing_paragraph) {
          // Check if the paragraph type is 'preschool_schedule'
          if ($existing_paragraph instanceof Paragraph && $existing_paragraph->bundle() === 'preschool_schedule') {
            $existing_paragraph->delete();            
          }
        }
        
        // Clear references to the deleted paragraphs
        $school_profile->set('field_schedule', []);
        $school_profile->save();
      }

      $this->messenger()->addMessage($this->t('All existing preschool schedule data has been deleted.'));
    } else {
      $this->messenger()->addMessage($this->t('No school profiles found.'));
    }

    // Return the form with updated messages
    return $form['messages'];
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fid = $form_state->getValue('csv_file')[0];
    $file = File::load($fid);

    if ($file) {
      if (is_array($file)) {
        $file = reset($file);
      }
      
      $file->setPermanent();
      $file->save();

      $csv_file_path = $file->getFileUri();

      if (($handle = fopen($csv_file_path, 'r')) !== FALSE) {
        $row = 0;
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          
          if ($row > 0) { 
            
            $school_profile_id = $data[0];
            $age_from = $data[1];
            $age_to = $data[2];
            $start_hour = $data[3];
            $start_minute = $data[4];
            $start_am = $data[5];
            $stop_hour = $data[6];
            $stop_minute = $data[7];
            $stop_pm = $data[8];
            $tution_from = $data[9];
            $tution_to = $data[10];
            $days_per_wk = $data[11];
            $teacher_student_ratio = $data[12];          
            
            $days_array = array_map('trim', explode(',', $days_per_wk));
            $ratios = array_map('trim', explode(':', $teacher_student_ratio));
            
            if (count($ratios) == 2) {
              list($from_ratio, $to_ratio) = $ratios;
            } else {
                
                $from_ratio = $ratios[0] ?? null;
                $to_ratio = null;
            }           
          
            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['field_school_profile_id' => $school_profile_id]);          

            $node = reset($nodes);

            if ($node) {           
              $paragraph = Paragraph::create([
                'type' => 'preschool_schedule',
                'field_age_range' => [
                  'from' => $age_from,
                  'to' => $age_to,
                ],
                'field_start_hour' => $start_hour,
                'field_start_minute' => $start_minute,
                'field_start_am_pm' => $start_am,
                'field_stop_hour' => $stop_hour,
                'field_stop_minute' => $stop_minute,
                'field_stop_am_pm' => $stop_pm,
                'field_tuition_from' => $tution_from,
                'field_tuition_to' => $tution_to,
                'field_days' => $days_array,
                'field_teacher_student_ratios' => [
                  'from' => $from_ratio,
                  'to' => $to_ratio,
                ],
              ]);

              $paragraph->save();           

              $node->get('field_schedule')->appendItem($paragraph);
              $node->save();
            }
          }
          $row++;
        }
        fclose($handle);
      }

      \Drupal::messenger()->addMessage($this->t('CSV data import completed successfully'));
    } else {
      \Drupal::messenger()->addError($this->t('Failed to upload CSV file. Please ensure it is a valid CSV file.'));
    }
  }
}