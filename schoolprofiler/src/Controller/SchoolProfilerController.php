<?php

namespace Drupal\schoolprofiler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;

class SchoolProfilerController extends ControllerBase {

	protected $requestStack;

	public function __construct(RequestStack $request_stack) {
		$this->requestStack = $request_stack;
	}

	// Dependency injection of the request stack.
	public static function create(ContainerInterface $container) {
		return new static(
			$container->get('request_stack')
		);
	}

	public function content($school_type, $grade, $node_ids) {
		$request = $this->requestStack->getCurrentRequest(); // Extract the current request.
		$node_ids_array = explode('|', $node_ids); // Split the node IDs by the '|' delimiter.

		// Initialize arrays to hold the node data.
		$link = [];
		$node_title_grade_address = [];
		$table_headers = [];		
        $coeducational_data = [];
		$birthday_cutoff_data = [];
		$religious_affiliation_data = [];
		$total_enrollment_data1 = [];
		$application_fee_data = [];
		$application_deadline_data = [];
		$type_of_school_data = [];
		$total_enrollment_data2 = [];
		$table_rows_average_class_size = [];
		$table_rows_attendence_rate = [];
		$table_rows_student_demographics = [];
		$student_enrollment_data = [];
		$table_rows_student_characteristics = [];
		$table_rows_teacher_characteristics = [];
		$table_rows_regents = [];		
		$table_rows_hs_grad_rates = [];
		$s_p_data = [];
        

    foreach ($node_ids_array as $nid) {
      if ($node = Node::load($nid)) {
          $url = Url::fromRoute('entity.node.canonical', ['node' => $nid]);
          $link = Link::fromTextAndUrl($node->getTitle(), $url)->toString();

          $grade_values = $node->get('field_grades_served')->getValue();
          $grades = [];
          foreach ($grade_values as $grade_value) {
              $grades[] = $grade_value['value'];
          }
          $grade_string = implode(',', $grades);

          $address_values = $node->get('field_address')->getValue();
          $address_string = '';
          if (!empty($address_values)) {
              $address = $address_values[0];
              $address_string = Html::escape($address['address_line1']) . ', ' .
                                Html::escape($address['locality']) . ', ' .
                                Html::escape($address['administrative_area']) . ' ' .
                                Html::escape($address['postal_code']);
          }
          $node_title_grade_address[] = '<div class="schoolname-grade-address">'. $link . ' (' . $grade_string . ') '.$address_string.'</div>';
          $table_headers[] = '<th>' . $link . '</th>';

          $coeducational_value = $node->get('field_coeducational')->getString();
          switch ($coeducational_value) {
              case '1':
                  $coeducational_data[] = 'Boys only';
                  break;
              case '2':
                  $coeducational_data[] = 'Girls only';
                  break;
              case '3':
                  $coeducational_data[] = 'Yes';
                  break;
              default:
                  $coeducational_data[] = Html::escape($coeducational_value);
          }

          $birthday_cutoff_value = $node->get('field_birthday_cutoff')->getString();
          $birthday_cutoff_data[] = Html::escape($birthday_cutoff_value);

          $religious_affiliation_value = $node->get('field_religious_affiliation')->getString();
          switch ($religious_affiliation_value) {
              case '1':
                  $religious_affiliation_data[] = 'Nonsectarian';
                  break;
              case '2':
                  $religious_affiliation_data[] = 'Jewish';
                  break;
              case '3':
                  $religious_affiliation_data[] = 'Presbyterian';
                  break;
              case '4':
                  $religious_affiliation_data[] = 'Quaker';
                  break;
              case '5':
                  $religious_affiliation_data[] = 'Catholic';
                  break;
              case '6':
                  $religious_affiliation_data[] = 'Methodist';
                  break;
              case '7':
                  $religious_affiliation_data[] = 'Episcopalian';
                  break;
              case '8':
                  $religious_affiliation_data[] = 'Other';
                  break;
              default:
                  $religious_affiliation_data[] = Html::escape($religious_affiliation_value);
          }	

          $total_enrollment_value1 = $node->get('field_total_enrollment')->getString();
          $total_enrollment_data1[] = Html::escape($total_enrollment_value1);

          $application_fee_value = $node->get('field_application_fee')->getString();
          $application_fee_data[] = Html::escape($application_fee_value);

          $application_deadline_value = $node->get('field_application_deadline_date')->getString();
          $application_deadline_data[] = Html::escape($application_deadline_value);

		//Public General Information		
		$field = $node->get('field_characteristics');  // Get the field

		if ($field instanceof FieldItemListInterface) {			
			$allowed_values = $field->getFieldDefinition()->getSettings()['allowed_values'];		
			$type_of_school_data = [];
		
			foreach ($field as $item) {
				$type_of_school_value = $item->value;			
				$type_of_school_label = isset($allowed_values[$type_of_school_value]) ? $allowed_values[$type_of_school_value] : '';
				$type_of_school_data[] = Html::escape($type_of_school_label);
			}
			$type_of_school_row[] = implode(', ', $type_of_school_data);
			$type_school_data = array_map(function($value) {
				return Html::escape($value);
			  }, $type_of_school_row);			
		}		

		$total_enrollment_value2 = $node->get('field_total_enrollment')->getString();
		$total_enrollment_data2[] = Html::escape($total_enrollment_value2); 

		// Iterate over the nodes and collect data for student demographics.
		$label_data = [];
		foreach ($node_ids_array as $nid) {
			if ($node = Node::load($nid)) {
				$student_demographics_data = json_decode($node->student_demographics ?: '[]', true);
				if (!empty($student_demographics_data)) {
					foreach ($student_demographics_data as $entry) {
						$sd_label = Html::escape($entry['label']);
						if (isset($entry['value']) && !empty(trim($entry['value']))) {
							$sd_label_value = Html::escape($entry['value']);
						} else {
							$sd_label_value = 'NA';
						}
						if (!isset($label_data[$sd_label])) {
							$label_data[$sd_label] = [];
						}                          
						$label_data[$sd_label][$nid] = $sd_label_value; // Store the value for each node ID
					}
				}
			}
		}

		foreach ($label_data as $sd_label => $values) {
			if (!isset($table_rows_student_demographics[$sd_label])) {
				$table_rows_student_demographics[$sd_label] = '<tr><td>' . $sd_label . '</td>';
			}
			foreach ($node_ids_array as $node_id) {
				$table_rows_student_demographics[$sd_label] .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
			}              
			$table_rows_student_demographics[$sd_label] .= '<td><a href="#" class="close-row">close</a></td></tr>'; // Add a close button to each row
		}
		
		// Iterate over the nodes and collect data for Student Enrollment.
		$student_enrollment_value = json_decode($node->student_enrollment ?: '[]', true);
		foreach ($student_enrollment_value as $grade_key => $value) {
			$student_enrollment_data[$nid][$grade_key] = Html::escape($value);
		}
		
		$grades_to_display = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', 'PK', 'K', 'UGE', 'UGS', 'TOTAL_ENROLLMENT'];

		$student_enrollment_row = '';
		foreach ($grades_to_display as $grade) {
			$student_enrollment_row .= '<tr><td>' . $grade . '</td>';
			foreach ($node_ids_array as $nid) {
				$value = isset($student_enrollment_data[$nid][$grade]) ? $student_enrollment_data[$nid][$grade] : 'NA';
				$student_enrollment_row .= '<td>' . $value . '</td>';
			}
            $config = \Drupal::config('school_comparison_field_setting.settings');
			$pub_data_student_enroll = $config->get('data_student_enrollment');
			if ($pub_data_student_enroll == 2) {
				$student_enrollment_row = '<tr class="data-display-none"><td>Student Enrollment</td><td>close</td></tr>';				
			}else {			
				$student_enrollment_row .= '<td><a href="#" class="close-row">close</a></td></tr>';					
			}			
		}

          // Iterate over the nodes and collect data for Average class size.
		  $subject_data = [];
          foreach ($node_ids_array as $nid) {
              if ($node = Node::load($nid)) {
                  $avg_class_size_data = json_decode($node->avg_class_size ?: '[]', true);
                  if (!empty($avg_class_size_data)) {
                      foreach ($avg_class_size_data as $entry) {
                          $subject = Html::escape($entry['SUBJECT']);
                          if (isset($entry['SCHOOL']) && !empty(trim($entry['SCHOOL']))) {
                              $school_value = Html::escape($entry['SCHOOL']);
                          } else {
                              $school_value = 'NA';
                          }
                          if (!isset($subject_data[$subject])) {
                              $subject_data[$subject] = [];
                          }                          
                          $subject_data[$subject][$nid] = $school_value;
                      }
                  }
              }
          }
		  
          foreach ($subject_data as $subject => $values) {
              if (!isset($table_rows_average_class_size[$subject])) {
                  $table_rows_average_class_size[$subject] = '<tr><td>' . $subject . '</td>';
              }
              foreach ($node_ids_array as $node_id) {
                  $table_rows_average_class_size[$subject] .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
              }              
              $table_rows_average_class_size[$subject] .= '<td><a href="#" class="close-row">close</a></td></tr>'; 
          }

			// Iterate over the nodes and collect data for Attendence Rate.
			$attendence_data = [];
			$a_s_data= [];
			$attendance_rows = [] ; 
			$suspension_rows = [] ; 
			foreach ($node_ids_array as $nid) {
				if ($node = Node::load($nid)) {
					// Decode JSON data
					$attendence_rate_data = json_decode($node->attendance_suspension ?: '[]', true);						
					// Check if decoding was successful and it's an array
					if (is_array($attendence_rate_data)) {
						
						$a_data = $attendence_rate_data['SCHOOL'];
					
						foreach ($a_data as $entry) {							
												         
							if (is_array($entry)) {
								if(!empty($entry['ATTENDANCE_RATE'])){									
									$a_s_data[$nid][ 'Attendance Rate']  = $entry['ATTENDANCE_RATE'];	
								} 
								else { 
									$a_s_data[$nid][ 'Attendance Rate']  = 'NA';	
								}

								if(!empty($entry['SUSPENSION_RATE'])){									
									$a_s_data[$nid]['Suspension Rate'] = $entry['SUSPENSION_RATE'] ; 
								}
								else { 
									$a_s_data[$nid]['Suspension Rate'] = '0' ; 
								}
							}
							else {
								$a_s_data[$nid] = [] ;
							}
						}						
			
					}  
	
				}

			$config = \Drupal::config('school_comparison_field_setting.settings');
			$pub_data_attendence_rate = $config->get('data_attendence_rate');			

			foreach($a_s_data as $key => $data){
				$attendance_rows['Attendance Rate'][$nid] = '<td>' . $a_s_data[$nid]['Attendance Rate'] . '</td>';
				$suspension_rows['Suspension Rate'][$nid] = '<td>' . $a_s_data[$nid]['Suspension Rate'] . '</td>';
				}
			}				
				$table_rows_attendence_rate['Attendance Rate'] = '<tr><td>Attendance Rate</td>' . implode('', $attendance_rows['Attendance Rate']) . 
					'<td><a href="#" class="close-row">close</a></td></tr>'; 

				$table_rows_attendence_rate['Suspension Rate'] = '<tr><td>Suspension Rate</td>' . implode('', $suspension_rows['Suspension Rate']) . 
				'<td><a href="#" class="close-row">close</a></td></tr>'; 
			// dpr($table_rows_attendence_rate) ; 

			// Iterate over the nodes and collect data for student characteristics.
			$subject_data1 = [];
			foreach ($node_ids_array as $nid) {
				if ($node = Node::load($nid)) {
					$student_characteristics_data = json_decode($node->student_characteristics ?: '[]', true);
					if (!empty($student_characteristics_data)) {
						foreach ($student_characteristics_data as $entry) {
							$sc_subject = Html::escape($entry['SUBJECT']);
							if (isset($entry['SCHOOL']) && !empty(trim($entry['SCHOOL']))) {
								$sc_subject_value = Html::escape($entry['SCHOOL']);
							} else {
								$sc_subject_value = 'NA';
							}
							if (!isset($subject_data1[$sc_subject])) {
								$subject_data1[$sc_subject] = [];
							}                          
							$subject_data1[$sc_subject][$nid] = $sc_subject_value; 
						}
					}
				}
			}
	
			foreach ($subject_data1 as $sc_subject => $values) {
				if (!isset($table_rows_student_characteristics[$sc_subject])) {
					$table_rows_student_characteristics[$sc_subject] = '<tr><td>' . $sc_subject . '</td>';
				}
				foreach ($node_ids_array as $node_id) {
					$table_rows_student_characteristics[$sc_subject] .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
				}              
				$table_rows_student_characteristics[$sc_subject] .= '<td><a href="#" class="close-row">close</a></td></tr>'; 
			}

			// Iterate over the nodes and collect data for teacher characteristics.
			$subject_data2 = [];
			foreach ($node_ids_array as $nid) {
				if ($node = Node::load($nid)) {
					$teacher_characteristics_data = json_decode($node->teacher_characteristics ?: '[]', true);
					if (!empty($teacher_characteristics_data)) {
						foreach ($teacher_characteristics_data as $entry) {
							$tc_subject = Html::escape($entry['SUBJECT']);
							if (isset($entry['SCHOOL']) && !empty(trim($entry['SCHOOL']))) {
								$tc_subject_value = Html::escape($entry['SCHOOL']);
							} else {
								$tc_subject_value = 'NA';
							}
							if (!isset($subject_data2[$tc_subject])) {
								$subject_data2[$tc_subject] = [];
							}                          
							$subject_data2[$tc_subject][$nid] = $tc_subject_value; 
						}
					}
				}
			}
	
			foreach ($subject_data2 as $tc_subject => $values) {
				if (!isset($table_rows_teacher_characteristics[$tc_subject])) {
					$table_rows_teacher_characteristics[$tc_subject] = '<tr><td>' . $tc_subject . '</td>';
				}
				foreach ($node_ids_array as $node_id) {
					$table_rows_teacher_characteristics[$tc_subject] .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
				}              
				$table_rows_teacher_characteristics[$tc_subject] .= '<td><a href="#" class="close-row">close</a></td></tr>'; 
			}

			// Iterate over the nodes and collect data for regents exams.
			$subject_data3 = [];
			foreach ($node_ids_array as $nid) {
				if ($node = Node::load($nid)) {
					// Check if the 'regents' field is not null
					if ($node->regents !== null) {
						$regents_data = json_decode($node->regents ?: '[]', true);
						if (!empty($regents_data)) {
							foreach ($regents_data as $entry) {							

								$reg_subject = Html::escape($entry['SUBJECT']);					

								if (isset($entry['MEETS_SCHOOL']) && !empty(trim($entry['MEETS_SCHOOL']))) {
									$reg_subject_value = Html::escape($entry['MEETS_SCHOOL']);
								} else {
									$reg_subject_value = 'NA';
								}
								if (!isset($subject_data3[$reg_subject])) {
									$subject_data3[$reg_subject] = [];
								}
								$subject_data3[$reg_subject][$nid] = $reg_subject_value;
							}
						}
					}
				}
			}
	
			foreach ($subject_data3 as $reg_subject => $values) {
				if (!isset($table_rows_regents[$reg_subject])) {
					$table_rows_regents[$reg_subject] = '<tr><td>' . $reg_subject . '</td>';
				}
				foreach ($node_ids_array as $node_id) {
					$table_rows_regents[$reg_subject] .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
				}              
				$table_rows_regents[$reg_subject] .= '<td><a href="#" class="close-row">close</a></td></tr>'; 
			}
			
			// Iterate over the nodes and collect data for high school graduation rate.
			$hs_grad_rate_data = [];
			foreach ($node_ids_array as $nid) {
				if ($node = Node::load($nid)) {
					if ($node->hs_completion_rates !== null) {
						$hs_completion_rates_data = json_decode($node->hs_completion_rates ?: '[]', true);
						if (!empty($hs_completion_rates_data)) {
							foreach ($hs_completion_rates_data['SCHOOL'][0] as $subject => $entry) {
								$hs_grad_rate_subject = Html::escape($subject);					

								if (isset($entry) && !empty(trim($entry))) {
									$hs_grad_rate_subject_value = Html::escape($entry);
								} else {
									$hs_grad_rate_subject_value = 'NA';
								}

								if (!isset($hs_grad_rate_data[$hs_grad_rate_subject])) {
									$hs_grad_rate_data[$hs_grad_rate_subject] = [];
								}
								$hs_grad_rate_data[$hs_grad_rate_subject][$nid] = $hs_grad_rate_subject_value;
							}
						}
					}
				}
			}
	
			foreach ($hs_grad_rate_data as $hs_grad_rate_subject => $values) {
				if (!isset($table_rows_hs_grad_rates[$hs_grad_rate_subject])) {
					$table_rows_hs_grad_rates[$hs_grad_rate_subject] = '<tr><td>' . $hs_grad_rate_subject . '</td>';
				}
				foreach ($node_ids_array as $node_id) {
					$table_rows_hs_grad_rates[$hs_grad_rate_subject] .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
				}              
				$table_rows_hs_grad_rates[$hs_grad_rate_subject] .= '<td><a href="#" class="close-row">close</a></td></tr>'; 
			}

			// Iterate over the nodes and collect data for students performance.
			
			$student_performance_data = [];
			foreach ($node_ids_array as $nid) {
				if ($node = Node::load($nid)) {
					if ($node->students_performance_group !== null) {
						$students_performance_group_data = json_decode($node->students_performance_group ?: '[]', true);
						if (!empty($students_performance_group_data)) {
							foreach ($students_performance_group_data as $entry) {
								$grade_with_suffix = '';
								if((int)$entry['GRADE'] == 1){
									$grade_with_suffix = '1st';
								}
								elseif((int)$entry['GRADE'] == 2){
									$grade_with_suffix = '2nd';
								}
								elseif((int)$entry['GRADE'] == 3){
									$grade_with_suffix = '3rd';
								}
								else{
									$grade_with_suffix = $entry['GRADE'] . 'th';
								}

								$students_performance_group_subject = Html::escape($entry['SUBJECT'] . ' ' . $grade_with_suffix . ' grade');

								if (isset($entry['SCHOOL']) && !empty(trim($entry['SCHOOL']))) {
									$students_performance_group_subject_value = Html::escape($entry['SCHOOL']);
								} else {
									$students_performance_group_subject_value = '0';
								}
								if (!isset($student_performance_data[$students_performance_group_subject])) {
									$student_performance_data[$students_performance_group_subject] = [];
								}                          
								$student_performance_data[$students_performance_group_subject][$nid] = $students_performance_group_subject_value; 
							}
						}
					}
				}
			}
	
			foreach ($student_performance_data as $students_performance_group_subject => $values) {
				if (!isset($s_p_data[$students_performance_group_subject])) {
					$s_p_data[$students_performance_group_subject] = '<tr><td>' . $students_performance_group_subject . '</td>';
				}
				foreach ($node_ids_array as $node_id) {
					$s_p_data[$students_performance_group_subject] .= '<td>' . ($values[$node_id] ?? '0') . '</td>';
				}              
				$s_p_data[$students_performance_group_subject] .= '<td><a href="#" class="close-row">close</a></td></tr>'; 
			}

      }
    }
	    // Create the row markup for public.
		//Prepare table rows based on the collected data for Type of School
		$type_of_school_data = '<tr><td>Type of School</td>';
		foreach ($type_of_school_row as $type_of_school_value) {
			$type_of_school_data .= '<td>' . $type_of_school_value . '</td>';
		}
		$config = \Drupal::config('school_comparison_field_setting.settings');
		$pub_data_type_of_school = $config->get('data_type_of_school');
		if ($pub_data_type_of_school == 2) {
			$type_of_school_data = '<tr class="data-display-none"><td>Type of School</td><td>close</td></tr>';
		}else {			
			$type_of_school_data .= '<td><a href="#" class="close-row">close</a></td></tr>';
		}		

		//Prepare table rows based on the collected data for Total Enrollment
		$total_enrollment_row2 = '<tr><td>Total Enrollment</td>';
		foreach ($total_enrollment_data2 as $total_enrollment_value2) {
			$total_enrollment_row2 .= '<td>' . $total_enrollment_value2 . '</td>';
		}
		$config = \Drupal::config('school_comparison_field_setting.settings');
		$pub_data_total_enroll = $config->get('data_total_enroll');
		if ($pub_data_total_enroll == 2) {
			$total_enrollment_row2 = '<tr class="data-display-none"><td>Total Enrollment</td><td>close</td></tr>';
		}else {			
			$total_enrollment_row2 .= '<td><a href="#" class="close-row">close</a></td></tr>';
		}

		// Prepare table rows based on the collected data for student demography.
		$table_rows_student_demographics = [];
		foreach ($label_data as $sd_label => $values) {
			$row = '<tr><td>' . $sd_label . '</td>';

			foreach ($node_ids_array as $node_id) {
				$row .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
			} 
			$config = \Drupal::config('school_comparison_field_setting.settings');
			$pub_data_student_demo = $config->get('data_student_demographics');
			if ($pub_data_student_demo == 2) {
				$row = '<tr class="data-display-none"><td>Student Demographics</td><td>close</td></tr>';
				$table_rows_student_demographics[] = $row;
			}else {			
				$row .= '<td><a href="#" class="close-row">close</a></td></tr>'; // Add a close button to each row
				$table_rows_student_demographics[] = $row;
			} 	
			
		}

		// Prepare table rows based on the collected data for Average class size.
		$table_rows_average_class_size = [];
		foreach ($subject_data as $subject => $values) {
			$row = '<tr><td>' . $subject . '</td>';

			foreach ($node_ids_array as $node_id) {
				$row .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
			}      
			
			$config = \Drupal::config('school_comparison_field_setting.settings');
			$pub_data_avg_class_size = $config->get('data_average_class_size');
			if ($pub_data_avg_class_size == 2) {
				$row = '<tr class="data-display-none"><td>Average Class Size</td><td>close</td></tr>';
				$table_rows_average_class_size[] = $row;
			}else {			
				$row .= '<td><a href="#" class="close-row">close</a></td></tr>'; // Add a close button to each row
				$table_rows_average_class_size[] = $row;
			} 
		}

		// Prepare table rows based on the collected data for student characteristics.
		$table_rows_student_characteristics = [];
		foreach ($subject_data1 as $sc_subject => $values) {
			$row = '<tr><td>' . $sc_subject . '</td>';

			foreach ($node_ids_array as $node_id) {
				$row .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
			}            
			
			$config = \Drupal::config('school_comparison_field_setting.settings');
			$pub_data_student_char = $config->get('data_student_char');
			if ($pub_data_student_char == 2) {
				$row = '<tr class="data-display-none"><td>Student Characteristics</td><td>close</td></tr>';
				$table_rows_student_characteristics[] = $row;
			}else {			
				$row .= '<td><a href="#" class="close-row">close</a></td></tr>'; // Add a close button to each row
				$table_rows_student_characteristics[] = $row;
			} 
		}

		// Prepare table rows based on the collected data for Teacher characteristics.
		$table_rows_teacher_characteristics = [];
		foreach ($subject_data2 as $tc_subject => $values) {
			$row = '<tr><td>' . $tc_subject . '</td>';

			foreach ($node_ids_array as $node_id) {
				$row .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
			}

			$config = \Drupal::config('school_comparison_field_setting.settings');
			$pub_data_teacher_char = $config->get('data_teacher_char');
			if ($pub_data_teacher_char == 2) {
				$row = '<tr class="data-display-none"><td>Teacher Characteristics</td><td>close</td></tr>';
				$table_rows_teacher_characteristics[] = $row;
			}else {			
				$row .= '<td><a href="#" class="close-row">close</a></td></tr>'; // Add a close button to each row
				$table_rows_teacher_characteristics[] = $row;
			}            
			
		}

		// Prepare table rows based on the collected data for regents exams.
		$table_rows_regents = [];
		foreach ($subject_data3 as $reg_subject => $values) {
			$row = '<tr><td>' . $reg_subject . '</td>';

			foreach ($node_ids_array as $node_id) {
				$row .= '<td>' . ($values[$node_id] ?? 'NA') . '</td>';
			}

			$config = \Drupal::config('school_comparison_field_setting.settings');
			$pub_data_regents_exam = $config->get('data_regents_exams');
			if ($pub_data_regents_exam == 2) {
				$row = '<tr class="data-display-none"><td>Regents Exams</td><td>close</td></tr>';
				$table_rows_regents[] = $row;
			}else {			
				$row .= '<td><a href="#" class="close-row">close</a></td></tr>'; // Add a close button to each row
				$table_rows_regents[] = $row;
			}             
			
		}

		// Create the table rows for high school completion rates
		$table_rows_hs_grad_rates = [];
		foreach ($hs_grad_rate_data as $hs_grad_subject => $values) {
			$row = '<tr><td>' . $hs_grad_subject . '</td>';

			foreach ($node_ids_array as $node_id) {
				$row .= '<td>' . (str_replace('%', '', $values[$node_id]) ?? 'NA') . '</td>';
			}

			$config = \Drupal::config('school_comparison_field_setting.settings');
			$pub_data_hs_grad_rate = $config->get('data_hscr');
			if ($pub_data_hs_grad_rate == 2) {
				$row = '<tr class="data-display-none"><td>Graduation Rates</td><td>close</td></tr>';
				$table_rows_hs_grad_rates[] = $row;
			}else {			
				$row .= '<td><a href="#" class="close-row">close</a></td></tr>'; // Add a close button to each row
				$table_rows_hs_grad_rates[] = $row;
			}             
			
		}

		// Create the table rows for student performance
		$s_p_data = [];
		foreach ($student_performance_data as $sp_subject => $values) {
			$row = '<tr><td>' . $sp_subject . '</td>';

			foreach ($node_ids_array as $node_id) {
				$row .= '<td>' . ($values[$node_id] ?? '0') . '</td>';
			}

			$config = \Drupal::config('school_comparison_field_setting.settings');
			$pub_data_students_performance = $config->get('data_posics');
			if ($pub_data_students_performance == 2) {
				$row = '<tr class="data-display-none"><td>Student Performance</td><td>close</td></tr>';
				$s_p_data[] = $row;
			}else {			
				$row .= '<td><a href="#" class="close-row">close</a></td></tr>'; // Add a close button to each row
				$s_p_data[] = $row;
			}             
			
		}

		// Convert arrays to strings for rendering
		$node_titles_string = implode('', $node_title_grade_address);
		$table_headers[] = '<th></th>';
		$table_headers_string = implode('', $table_headers);
		$average_class_size = implode('', $table_rows_average_class_size);
		$attendence_rates = implode('', $table_rows_attendence_rate);
		$student_demographics = implode('', $table_rows_student_demographics);
		$student_characteristics = implode('', $table_rows_student_characteristics);
		$teacher_characteristics = implode('', $table_rows_teacher_characteristics);
		$regents = implode('', $table_rows_regents);
		$hs_grad_rates = implode('', $table_rows_hs_grad_rates) ;
		$student_per_data = implode('', $s_p_data) ; 


		// Create the markup for the sections
		$description_markup = '<h2>School Comparison</h2><p>This document presents a comparison of the following schools. Click the "X" icon to the right of each row to remove that criterion. At any point, you can click the "Reset" link above the table to show all of the criteria again. Click on the name of a school to view the school profile.</p>';
		$title_grade_address_markup = '<div class="title-grade-address-wrapper">'. $node_titles_string . '</div>';
		$reset_row = '<div id="reset-rows"><a href="#">Click here to restore all rows</a></div>';


		$table_markup_private = '';
		$table_markup_public = '';

        // Create the row markup for private.
		$coeducational_row = '<tr><td>Coeducational</td>';
		foreach ($coeducational_data as $coeducational_value) {
			$coeducational_row .= '<td>' . $coeducational_value . '</td>';
		}
		$config = \Drupal::config('school_comparison_field_setting.settings');
		$pvt_data_coeducational = $config->get('data_coeducational');
		if ($pvt_data_coeducational == 2) {
			$coeducational_row = '<tr class="data-display-none"><td>Coeducational</td><td>close</td></tr>';
		}else {			
			$coeducational_row .= '<td><a href="#" class="close-row">close</a></td></tr>';
		}

		$birthday_cutoff_row = '<tr><td>Birthday Cut-off</td>';
		foreach ($birthday_cutoff_data as $birthday_cutoff_value) {
			$birthday_cutoff_row .= '<td>' . $birthday_cutoff_value . '</td>';
		}
		$config = \Drupal::config('school_comparison_field_setting.settings');
		$pvt_data_birthday_cutoff = $config->get('data_birthday_cutoff');
		if ($pvt_data_birthday_cutoff == 2) {
			$birthday_cutoff_row = '<tr class="data-display-none"><td>Birthday Cut-off</td><td>close</td></tr>';
		}else {			
			$birthday_cutoff_row .= '<td><a href="#" class="close-row">close</a></td></tr>';
		}

		$religious_affiliation_row = '<tr><td>Religious Affiliation</td>';
		foreach ($religious_affiliation_data as $religious_affiliation_value) {
			$religious_affiliation_row .= '<td>' . $religious_affiliation_value . '</td>';
		}
		$config = \Drupal::config('school_comparison_field_setting.settings');
		$pvt_data_religious_affiliation = $config->get('data_religious_affiliation');
		if ($pvt_data_religious_affiliation == 2) {
			$religious_affiliation_row = '<tr class="data-display-none"><td>Religious Affiliation</td><td>close</td></tr>';
		}else {			
			$religious_affiliation_row .= '<td><a href="#" class="close-row">close</a></td></tr>';
		}

		$total_enrollment_row1 = '<tr><td>Total Enrollment</td>';
		foreach ($total_enrollment_data1 as $total_enrollment_value1) {
			$total_enrollment_row1 .= '<td>' . $total_enrollment_value1 . '</td>';
		}
		$config = \Drupal::config('school_comparison_field_setting.settings');
		$pvt_data_total_enrollment1 = $config->get('data_total_enrollment1');
		if ($pvt_data_total_enrollment1 == 2) {
			$total_enrollment_row1 = '<tr class="data-display-none"><td>Total Enrollment</td><td>close</td></tr>';
		}else {			
			$total_enrollment_row1 .= '<td><a href="#" class="close-row">close</a></td></tr>';
		}

		$application_fee_row = '<tr><td>Application Fee	</td>';
		foreach ($application_fee_data as $application_fee_value) {
			$application_fee_row .= '<td>' . $application_fee_value . '</td>';
		}
		$config = \Drupal::config('school_comparison_field_setting.settings');
		$pvt_data_application_fee = $config->get('data_application_fee');
		if ($pvt_data_application_fee == 2) {
			$application_fee_row = '<tr class="data-display-none"><td>Application Fee</td><td>close</td></tr>';
		}else {			
			$application_fee_row .= '<td><a href="#" class="close-row">close</a></td></tr>';
		}

		$application_deadline_row = '<tr><td>Application Deadline</td>';
		foreach ($application_deadline_data as $application_deadline_value) {
			$application_deadline_row .= '<td>' . $application_deadline_value . '</td>';
		}
		$config = \Drupal::config('school_comparison_field_setting.settings');
		$pvt_data_application_deadline = $config->get('data_application_deadline');
		if ($pvt_data_application_deadline == 2) {
			$application_deadline_row = '<tr class="data-display-none"><td>Application Deadline</td><td>close</td></tr>';
		}else {			
			$application_deadline_row .= '<td><a href="#" class="close-row">close</a></td></tr>';
		}

		// Display the table based on Private & Public condition.
		if ($school_type == 'private') {
			$table_markup_private = '
			<h2>Private schools comparison table</h2>
			<table class="comparison-table">
				<thead>
					<tr><th>Criteria</th>' . $table_headers_string . '</tr>
				</thead>
				<tbody>
					' . $coeducational_row . '
					' . $birthday_cutoff_row . '
					' . $religious_affiliation_row . '
					' . $total_enrollment_row1 . '
					<tr><td>After School Program</td>' . str_repeat('<td></td>', count($table_headers)) . '</tr>
					<tr><td>Summer Program</td>' . str_repeat('<td></td>', count($table_headers)) . '</tr>
					<tr><td>Tuition</td>' . str_repeat('<td></td>', count($table_headers)) . '</tr>
					' . $application_fee_row . '					
					' . $application_deadline_row . '
				</tbody>
			</table>';
		} else {			
			$table_markup_public = '
			<h2>General Information</h2>
			<table class="comparison-table value-2-name">
				<thead>
					<tr><th>Criteria</th>' . $table_headers_string . '</tr>
				</thead>
				<tbody>
					' . $type_of_school_data . '
					' . $total_enrollment_row2 . '	
				</tbody>
			</table> ' ; 


			if ($pub_data_student_enroll == 1){ 
				$table_markup_public .= '<h2>Student Enrollment</h2>
				<table class="comparison-table">
					<thead>
						<tr><th>Grades</th>' . $table_headers_string . '</tr>
					</thead>
					<tbody>
						' . $student_enrollment_row . '
					</tbody>
				</table>' ; 
			}

			if ($pub_data_avg_class_size == 1) {
				$table_markup_public .= '

				<h2>Average Class Size</h2>
				<table class="comparison-table">
					<thead>
						<tr><th>Subjects</th>' . $table_headers_string . '</tr>
					</thead>
					<tbody>
						' . $average_class_size . '
					</tbody>
				</table> ' ; 
			}
			
			if ($pub_data_attendence_rate == 1) {
				$table_markup_public .= '<h2>Attendance Rates</h2>				
				<table class="comparison-table">
					<thead>
						<tr><th>Attendence Rates</th>' . $table_headers_string . '</tr>
					</thead>
					<tbody>
						' . $attendence_rates . '
					</tbody>
				</table> ' ; 
			} 			

			if ($pub_data_student_demo == 1) {

				$table_markup_public .= '<h2>Student Demographics</h2>
				<table class="comparison-table">
					<thead>
						<tr><th>Colors</th>' . $table_headers_string . '</tr>
					</thead>
					<tbody>
						' . $student_demographics . '
					</tbody>
				</table>' ; 
			}

			if ($pub_data_student_char == 1) {

				$table_markup_public .= '
				
				<h2>Student Characteristics</h2>
				<table class="comparison-table">
					<thead>
						<tr><th>Category</th>' . $table_headers_string . '</tr>
					</thead>
					<tbody>
						' . $student_characteristics . '
					</tbody>
				</table> ' ; 
			}
			if ($pub_data_teacher_char == 1){
				$table_markup_public .= '

				<h2>Teacher Characteristics</h2>
				<table class="comparison-table">
					<thead>
						<tr><th>Topic</th>' . $table_headers_string . '</tr>
					</thead>
					<tbody>
						' . $teacher_characteristics . '
					</tbody>
				</table>' ; 
			}	

			if ($pub_data_regents_exam == 1){
				$table_markup_public .= '
				<h2>Percent of students passing regents exams</h2>
				<table class="comparison-table">
					<thead>
						<tr><th>Subject</th>' . $table_headers_string . '</tr>
					</thead>
					<tbody>
						' . $regents . '
					</tbody>
				</table>' ; 
			}
			if ($pub_data_hs_grad_rate  == 1){
				$table_markup_public .= '
				<h2>Graduation Rates</h2>
				<table class="comparison-table">
					<thead>
						<tr><th>School</th>' . $table_headers_string . '</tr>
					</thead>
					<tbody>
						'. $hs_grad_rates .'
					</tbody>
				</table>';
			}

			if ($pub_data_students_performance  == 1){
				$table_markup_public .= '
				<h2>The percent of students who met (level 3 or higher) or exceeded (level 4) state learning standards on the assessments in the following core subject areas</h2>
				<table class="comparison-table students-performance">
					<thead>
						<tr><th>Subject</th>' . $table_headers_string . '</tr>
					</thead>
					<tbody>
						'. $student_per_data .'
					</tbody>
				</table>';
			}
			
		}

		// Return the final render array
		return [
			'#markup' => $description_markup . $title_grade_address_markup . $reset_row . $table_markup_private . $table_markup_public,
			'#cache' => [
				'contexts' => ['url', 'user'],
				'max-age' => 0,
			],
		];
	}
}
