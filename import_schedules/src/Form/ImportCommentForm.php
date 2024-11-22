<?php

namespace Drupal\import_schedules\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystemInterface;

class ImportCommentForm extends FormBase {
  protected $fileSystem;
  
  public function __construct(FileSystemInterface $file_system) {
    $this->fileSystem = $file_system;
  }
  
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system')
    );
  }

  public function getFormId() {
    return 'import_comment_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload CSV file'),
      '#description' => $this->t('Please upload the CSV file to import comment schedule data.'),
      '#upload_location' => 'public://import_schedules/',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#required' => TRUE,
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

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fid = $form_state->getValue('csv_file')[0];
    $file = File::load($fid);

    if ($file) {
      $file->setPermanent();
      $file->save();

      $file_path = $this->fileSystem->realpath($file->getFileUri());

      if (($handle = fopen($file_path, 'r')) !== FALSE) {
        $header = fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== FALSE) {
          $row = array_combine($header, $data);         
          $school_profile_id = $row['School profile id'];
          $comment = $row['comment'];

          // Ensure the school_profile_id exists and is not empty or blank
          if (!empty($school_profile_id)) {
            // Fetch the node using the School Profile ID
            $nids = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
              'type' => 'school_profile',
              'field_school_profile_id' => $school_profile_id,
            ]);

            if (!empty($nids)) {
              // Existing node found, load it
              $node = reset($nids);

              if ($node instanceof Node && !empty($node->get('field_school_profile_id')->value)) {
                // If node has a value for field_school_profile_id, update it
                $node->set('field_comment', $comment);
                $node->save();
                \Drupal::messenger()->addMessage($this->t('Node for School Profile ID @id has been updated.', ['@id' => $school_profile_id]), 'status');
              } else {
                \Drupal::messenger()->addMessage($this->t('Node for School Profile ID @id exists but has no value in field_school_profile_id. No update made.', ['@id' => $school_profile_id]), 'warning');
              }
            } else {
              // Node does not exist, skip creation
              \Drupal::messenger()->addMessage($this->t('No existing node found for School Profile ID @id. No node will be created.', ['@id' => $school_profile_id]), 'warning');
            }
          } else {
            // Skip if school_profile_id is blank
            \Drupal::messenger()->addMessage($this->t('Blank or missing School Profile ID in the CSV row. Skipping row.'), 'warning');
          }
        }

        fclose($handle);
        \Drupal::messenger()->addMessage($this->t('CSV import completed successfully.'));
      } else {
        \Drupal::messenger()->addMessage($this->t('Could not open the file.'), 'error');
      }
    } else {
      \Drupal::messenger()->addMessage($this->t('No file uploaded or incorrect format.'), 'error');
    }
  }
}
