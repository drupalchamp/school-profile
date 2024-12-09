<?php

declare(strict_types=1);

namespace Drupal\school_comparison_field_setting\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure School comparison field settings for this site.
 */
final class SchoolComparisonFieldSettingForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'school_comparison_setting';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['school_comparison_field_setting.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('school_comparison_field_setting.settings'); // Retrieve the configuration.

    //Private
    $form['fieldset_data_pvt'] = array(
      '#type' => 'details',
      '#title' => $this->t('Private'),
    );

    $form['fieldset_data_pvt']['data_coeducational'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Coeducational'),
      '#default_value' => $config->get('data_coeducational') ?? '1',
    ];
    $form['fieldset_data_pvt']['data_birthday_cutoff'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Birthday Cut-off'),
      '#default_value' => $config->get('data_birthday_cutoff') ?? '1',
    ];
    $form['fieldset_data_pvt']['data_religious_affiliation'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Religious Affiliation'),
      '#default_value' => $config->get('data_religious_affiliation') ?? '1',
    ];
    $form['fieldset_data_pvt']['data_total_enrollment1'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Total Enrollment'),
      '#default_value' => $config->get('data_total_enrollment1') ?? '1',
    ];
    $form['fieldset_data_pvt']['data_after_school_program'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('After School Program'),
      '#default_value' => $config->get('data_after_school_program') ?? '1',
    ];
    $form['fieldset_data_pvt']['data_summer_programe'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Summer Program'),
      '#default_value' => $config->get('data_summer_programe') ?? '1',
    ];
    $form['fieldset_data_pvt']['data_tuition'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Tuition'),
      '#default_value' => $config->get('data_tuition') ?? '1',
    ];
    $form['fieldset_data_pvt']['data_application_fee'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Application Fee'),
      '#default_value' => $config->get('data_application_fee') ?? '1',
    ];
    $form['fieldset_data_pvt']['data_application_deadline'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Application Deadline'),
      '#default_value' => $config->get('data_application_deadline') ?? '1',
    ];  

    // General Information
    $form['fieldset_data_pub'] = array(
      '#type' => 'details',
      '#title' => $this->t('Public'),
    );

    $form['fieldset_data_pub']['data_type_of_school'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Type of School'),
      '#default_value' => $config->get('data_type_of_school') ?? '1',
    ];

    $form['fieldset_data_pub']['data_total_enroll'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Total Enrollment'),
      '#default_value' => $config->get('data_total_enroll') ?? '1',
    ];

    // Student Demographics
    $form['fieldset_data_pub']['data_student_demographics'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Student Demographics'),
      '#default_value' => $config->get('data_student_demographics') ?? '1',
    ];

    // Student Enrollment
    $form['fieldset_data_pub']['data_student_enrollment'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Student Enrollment'),
      '#default_value' => $config->get('data_student_enrollment') ?? '1',
    ];

    // Average Class Size
    $form['fieldset_data_pub']['data_average_class_size'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Average Class Size'),
      '#default_value' => $config->get('data_average_class_size') ?? '1',
    ];

    // Attendence Rate
    $form['fieldset_data_pub']['data_attendence_rate'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Attendence Rate'),
      '#default_value' => $config->get('data_attendence_rate') ?? '1',
    ];

    // Student Characteristics
    $form['fieldset_data_pub']['data_student_char'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Student Characteristics'),
      '#default_value' => $config->get('data_student_char') ?? '1',
    ];

    // Teacher Characteristics
    $form['fieldset_data_pub']['data_teacher_char'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Teacher Characteristics'),
      '#default_value' => $config->get('data_teacher_char') ?? '1',
    ];

    // Regents Exams
    $form['fieldset_data_pub']['data_regents_exams'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Regents Exams'),
      '#default_value' => $config->get('data_regents_exams') ?? '1',
    ];

    // High School Completion Rates
    $form['fieldset_data_pub']['data_hscr'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('High School Completion Rates'),
      '#default_value' => $config->get('data_hscr') ?? '1',
    ]; 
    
    // Percent of Students in core subjects
    $form['fieldset_data_pub']['data_posics'] = [
      '#type' => 'radios',
      '#options' => [
        '1' => $this->t('Yes'),
        '2' => $this->t('No'),          
      ], 
      '#title' => $this->t('Percent of Students in core subjects'),
      '#default_value' => $config->get('data_posics') ?? '1',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
      $config = $this->config('school_comparison_field_setting.settings');
      // Set the submitted configuration setting.
      //Private
      $config->set("data_coeducational", $form_state->getvalue('data_coeducational'));
      $config->set("data_birthday_cutoff", $form_state->getvalue('data_birthday_cutoff'));
      $config->set("data_religious_affiliation", $form_state->getvalue('data_religious_affiliation'));
      $config->set("data_total_enrollment1", $form_state->getvalue('data_total_enrollment1'));
      $config->set("data_after_school_program", $form_state->getvalue('data_after_school_program'));
      $config->set("data_summer_programe", $form_state->getvalue('data_summer_programe'));
      $config->set("data_tuition", $form_state->getvalue('data_tuition'));
      $config->set("data_application_fee", $form_state->getvalue('data_application_fee'));
      $config->set("data_application_deadline", $form_state->getvalue('data_application_deadline'));

      //Public
      $config->set("data_type_of_school", $form_state->getvalue('data_type_of_school'));
      $config->set("data_total_enroll", $form_state->getvalue('data_total_enroll'));      
      $config->set("data_student_demographics", $form_state->getvalue('data_student_demographics'));
      $config->set("data_student_enrollment", $form_state->getvalue('data_student_enrollment'));
      $config->set("data_average_class_size", $form_state->getvalue('data_average_class_size'));
      $config->set("data_attendence_rate", $form_state->getvalue('data_attendence_rate'));
      $config->set("data_student_char", $form_state->getvalue('data_student_char'));
      $config->set("data_teacher_char", $form_state->getvalue('data_teacher_char'));
      $config->set("data_regents_exams", $form_state->getvalue('data_regents_exams'));
      $config->set("data_hscr", $form_state->getvalue('data_hscr'));
      $config->set("data_posics", $form_state->getvalue('data_posics'));   

      $config->save();      

    parent::submitForm($form, $form_state);
  }
}