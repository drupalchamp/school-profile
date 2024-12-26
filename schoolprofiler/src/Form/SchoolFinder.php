<?php 

namespace Drupal\schoolprofiler\Form ; 
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Entity\Query\QueryInterface ; 
use \Drupal\Core\Url;
use \Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;

class SchoolFinder extends FormBase { 	

	
	public function getFormId() { 
		return 'school_finder_form';
	}


	const MAX_RESULT_DISPLAY = 50 ; 

	public function buildForm(array $form, FormStateInterface $form_state){ 
		$form['sf'] = [
			'#type' => 'container',
			'#attributes' => [ 
				'class' => [ 
					'schoolfinder-search', 
				],
			],

		]; 
		$form['sf-compare'] = [
			'#type' => 'container',
			'#attributes' => [ 
				'id' => 'schoolfinder-compare', 
				'class' => 'schoolfinder-compare-wrapper', 
			], 

		]; 
		$form['sf']['title'] = [ 
			'#type' => 'item', 
			'#markup' => $this->t('<h1>School Finder</h1>'), 
		]; 
		$form['sf']['instruction'] = [ 
			'#type' => 'item', 
			'#markup' => $this->t('<div id="intro1"> Select the school criteria in which you are interested and click the search button. </div> 
			<div id="intro2"> <span>*</span> denotes required fields </div>'), 
		]; 

		$form['sf-compare']['title'] = [ 
			'#type' => 'item', 
			'#markup' => $this->t('<h1>Compare Tool</h1> <div id="schoolfinder-results"> No results</div>'), 
			'#attributes' => [ 
				'id' => 'schoolfinder-results', 
				'class' => 'schoolfinder-results-wrapper', 
			], 
			'#allowed_tags' => array_merge(\Drupal\Component\Utility\Xss::getAdminTagList(), ['button','span','input', 'a', 'div', 'img', 'table', 'td', 'tr', 'label']), 
		]; 

		$form['sf']['search'] = [ 
			'#type' => 'submit', 
			'#value' => $this->t('Search'), 
			'#ajax' => [
				'callback' => '::submitSchoolSearchCallback',
				'wrapper' => 'schoolfinder-results', 	
			], 
			'#value' => $this->t('Search'), 

		];

		$form['sf']['bc'] = [
			'#title' => $this->t('Basic Criteria'), 
			'#type' => 'fieldset', 
		]; 	

		$form['sf']['bc']['school_name'] = [

			'#type' => 'textfield', 
			'#title' => $this->t('School name'),
		    '#size' => 60, 
			'#maxlength' => 128, 
		 	'#default_value' => $form_state->getValue('school_name'), 	
			'#required' => FALSE, 

		] ; 

		$form['sf']['bc']['grade'] = [
			'#type' => 'select', 
			'#title' => $this->t('Your child\'s grade'), 
			'#options' => [
				'PK' => $this->t('Preschool'), 
				'K' => $this->t('Kindergarten'), 
				'1' => $this->t('1st'), 
				'2' => $this->t('2nd'), 
				'3' => $this->t('3rd'), 
				'4' => $this->t('4th'), 
				'5' => $this->t('5th'), 
				'6' => $this->t('6th'), 
				'7' => $this->t('7th'), 
				'8' => $this->t('8th'), 
				'9' => $this->t('9th'), 
				'10' => $this->t('10th'), 
				'11' => $this->t('11th'), 
				'12' => $this->t('12th'), 
			], 
			'#required' => TRUE, 
			'#empty_option' => $this->t('Select your child\'s grade'), 
			'#default_value' => $form_state->getValue('grade'), 

		] ; 

		$form['sf']['bc']['school_type'] = [ 
			'#type' => 'radios', 
			'#title' => $this->t('School type'),
			'#required' => TRUE, 
			'#options' => [ 
				'1' => $this->t('Private'), 
				'2' => $this->t('Public'), 
		
			], 
			'#prefix' => '<div id="school-type-wrapper">',
			'#suffix' => '</div>',	

		]; 
		
		$form['sf']['bc']['cbo'] = [ 
			'#type' => 'checkbox', 
			'#title' => $this->t('CBO-UPK (universal prekindergarten)'),
			'#required' => FALSE, 
			'#states' => [ 
				'visible' => [ 
					':input[name="school_type"]' => ['value' => 1], 
					':input[name="grade"]' => ['value' => 'PK'], 
				], 
				'enabled' => [ 
					':input[name="school_type"]' => ['value' => 1], 
					':input[name="grade"]' => ['value' => 'PK'], 
				], 
			],
		]; 
		$form['sf']['bc']['nyc_school'] = [ 
			'#type' => 'checkbox', 
			'#title' => $this->t('New York city schools'),
			'#required' => FALSE, 
			'#states' => [ 
				'visible' => [ 
					':input[name="school_type"]' => ['value' => 2], 
				], 
			],
		]; 
		
		
		$form['sf']['bc']['location'] = [
			'#tree' => TRUE, 
			'#title' => $this->t('Location'), 
			'#type' => 'fieldset', 
		]; 	

		$form['sf']['bc']['location']['address1'] = [
			'#type' => 'textfield', 
			'#title' => $this->t('Address'),
		    '#size' => 60, 
			'#maxlength' => 128, 
		 	'#default_value' => $form_state->getValue('address1'), 	
			'#required' => FALSE, 
		] ; 

		$form['sf']['bc']['location']['city'] = [
			'#type' => 'textfield', 
			'#title' => $this->t('City'),
		        '#size' => 30, 
			'#maxlength' => 128, 
		 	'#default_value' => $form_state->getValue('city'), 	
			'#required' => FALSE, 
		] ; 

		$form['sf']['bc']['location']['zip'] = [
			'#type' => 'textfield', 
			'#title' => $this->t('Zip code'),
		        '#size' => 10, 
			'#maxlength' => 5, 
		 	'#default_value' => $form_state->getValue('zip'), 	
			'#required' => FALSE, 
		] ; 
		
		$form['sf']['bc']['location']['distance'] = [
			'#type' => 'select', 
			'#title' => $this->t('Maximum distance'), 
			'#options' => [
				'0.5' => $this->t('Within 0.5 miles'), 
				'1' => $this->t('Within 1 mile'), 
				'1.5' => $this->t('Within 1.5 miles'), 
				'2' => $this->t('Within 2 miles'), 
				'5' => $this->t('Within 5 miles'), 
			], 
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
			'#states' => [ 
				'visible' => [ 
					':input[name="zip"]' => ['filled' => TRUE], 
				], 
			],


		] ; 
		// private school advanced search criteria (private_sch_adv_search) 
		
		$form['sf']['private_sch_adv_search'] = [
			'#tree' => TRUE, 
			'#title' => $this->t('Private School Advanced Search'), 
			'#type' => 'fieldset', 
			'#states' => [ 
				'visible' => [ 
					':input[name="school_type"]' => ['value' => 1], 
					':input[name="grade"]' => ['!value' => 'PK'], 
				], 
			],
		]; 	

		$field_religious_affiliation = [
			'#type' => 'select', 
			'#title' => $this->t('Religious Affiliation'), 
			'#options' => [
				'1' => $this->t('Nonsectarian'),
				'2' => $this->t('Jewish'),
				'3' => $this->t('Presbyterian'),
				'4' => $this->t('Quaker'),
				'5' => $this->t('Catholic'),
				'6' => $this->t('Methodist'),
				'7' => $this->t('Episcopalian'),
				'8' => $this->t('Other'),
			], 
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
			'#default_value' => $form_state->getValue('religious_affiliation'), 

		] ; 

		$form['sf']['private_sch_adv_search']['religious_affiliation'] = $field_religious_affiliation ; 

		
		$field_enrollment =  [ 
			'#type' => 'select', 
			'#title' => $this->t('Enrollment'),
			'#options' => [
				'1' => $this->t('Less than 100'), 
				'2' => $this->t('Between 100 and 250'), 
				'3' => $this->t('Between 250 and 500'), 
				'4' => $this->t('Between 500 and 1000'), 
				'5' => $this->t('Greater than 1000'),

			], 	
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
		]; 
		
		$form['sf']['private_sch_adv_search']['enrollment'] = $field_enrollment ; 
		
		$field_grades_served = [ 
			'#type' => 'select', 
			'#title' => $this->t('Grades served'),
			'#options' => [
				'K-5' => $this->t('K - 5'), 
				'K-8' => $this->t('K - 8'), 
				'K-12' => $this->t('K - 12'), 
				'6-8' => $this->t('6 - 8'), 
				'6-12' => $this->t('6 - 12'), 
				'9-12' => $this->t('9 - 12'), 
			], 	
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
			'#states' => [ 
				'invisible' => [ 
					':input[name="grade"]' => ['value' => 'PK'], 
				], 
			],
		]; 
		
		$form['sf']['private_sch_adv_search']['grades_served'] = $field_grades_served  ;  

		$field_maximum_tuition = [ 
			'#type' => 'select', 
			'#title' => $this->t('Maximum tuition'),
			'#options' => [
				'10000' => $this->t('Less than $10,000'), 
				'15000' => $this->t('Less than $15,000'), 
				'20000' => $this->t('Less than $20,000'), 
				'25000' => $this->t('Less than $25,000'), 
				'30000' => $this->t('Less than $30,000'), 
				'> 35000' => $this->t('$30,000 ore more'), 

			], 	
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
			'#states' => [ 
				'invisible' => [ 
					':input[name="grade"]' => ['value' => 'PK'], 
				], 
			],
		]; 
		
		$form['sf']['private_sch_adv_search']['maximum_tuition'] = $field_maximum_tuition ; 

		$field_coed = [ 
			'#type' => 'select', 
			'#title' => $this->t('Coeducational'),
			'#options' => [
				1 => $this->t('Boys only'), 
				2 => $this->t('Girls only'), 
				3 => $this->t('Coeducational'), 

			], 	
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
			'#states' => [ 
				'invisible' => [ 
					':input[name="grade"]' => ['value' => 'PK'], 
				], 
			],
		]; 

		$form['sf']['private_sch_adv_search']['coed'] = $field_coed ; 


		// private preschool advanced search (private_prek_sch_adv_search)
		
		
		$form['sf']['private_prek_sch_adv_search'] = [
			'#tree' => TRUE, 
			'#title' => $this->t('Private Preschool School Advanced Search'), 
			'#type' => 'fieldset', 
			'#states' => [ 
				'visible' => [ 
					':input[name="grade"]' => ['value' => 'PK'], 
					':input[name="school_type"]' => ['value' => 1], 
				], 
			],
		]; 	
		$form['sf']['private_prek_sch_adv_search']['age'] = [
			'#title' => $this->t('What age will your child be when she or he enrolls in school?'), 
			'#type' => 'item', 
			'#states' => [ 
				'visible' => [ 
					':input[name="school_type"]' => ['value' => 1], 
					':input[name="grade"]' => ['value' => 'PK'], 
				], 
			],
		]; 	


		$form['sf']['private_prek_sch_adv_search']['age']['pk_age_year']  = [ 
			'#type' => 'select', 
			'#title' => $this->t('Year'), 
			'#options' => [
				0 => $this->t('0'),
				1 => $this->t('1'),
				2 => $this->t('2'),
				3 => $this->t('3'),
				4 => $this->t('4'),
				5 => $this->t('5'),
			], 
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
		]; 
		
		$form['sf']['private_prek_sch_adv_search']['age']['pk_age_month']  = [ 
			'#type' => 'select', 
			'#title' => $this->t('Months'), 
			'#options' => [
				0 => $this->t('0'),
				1 => $this->t('1'),
				2 => $this->t('2'),
				3 => $this->t('3'),
				4 => $this->t('4'),
				5 => $this->t('5'),
				6 => $this->t('6'),
				7 => $this->t('7'),
				8 => $this->t('8'),
				9 => $this->t('9'),
				10 => $this->t('10'),
				11 => $this->t('11'),
				12 => $this->t('12'),
			], 
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
		]; 

		// philosophy are term tags 
		//
		$field_philosophy  = [ 
			'#type' => 'select', 
			'#title' => $this->t('Educational Philosophy'), 
			'#options' => [
				1 => $this->t('Traditional'), 
				2 => $this->t('Developmental'), 
				3 => $this->t('Montessori'), 
				4 => $this->t('Eclectic'), 
				5 => $this->t('Play-based'), 
				6 => $this->t('Day Care'), 
				7 => $this->t('Reggio Emelia'), 
				8 => $this->t('Other'), 
				9 => $this->t('Special Education'), 
				10 => $this->t('Waldorf'), 

			], 
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 

		]; 
		
		$form['sf']['private_prek_sch_adv_search']['philosophy']  = $field_philosophy ; 


		$form['sf']['private_prek_sch_adv_search']['enrollment'] = $field_enrollment  ; 

		$form['sf']['private_prek_sch_adv_search']['religious_affiliation'] = $field_religious_affiliation ; 

		$field_min_days_offered  = [ 
			'#type' => 'select', 
			'#title' => $this->t('Minimum days offered'), 
			'#options' => [
				1 => $this->t('1'), 
				2 => $this->t('2'), 
				3 => $this->t('3'), 
				4 => $this->t('4'), 
				5 => $this->t('5'), 
			], 
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
			'#states' => [ 
				'visible' => [ 
					':input[name="grade"]' => ['value' => 'PK'], 
				], 
			],

		]; 
		
		$form['sf']['private_prek_sch_adv_search']['min_days_offered']  = $field_min_days_offered  ; 
		
		$form['sf']['private_prek_sch_adv_search']['schedule']  = [ 
			'#title' => $this->t('Schedule'), 
			'#type' => 'fieldset', 
		]; 


		$form['sf']['private_prek_sch_adv_search']['schedule']['open_by']  = [ 
			'#type' => 'select', 
			'#title' => $this->t('Open By'), 
			'#options' => [
				'04:00 AM' => $this->t('4:00 AM'),
				'05:00 AM' => $this->t('5:00 AM'),
				'06:00 AM' => $this->t('6:00 AM'),
				'07:00 AM' => $this->t('7:00 AM'),
				'08:00 AM' => $this->t('8:00 AM'),
				'09:00 AM' => $this->t('9:00 AM'),
				'10:00 AM' => $this->t('10:00 AM'),
				'11:00 AM' => $this->t('11:00 AM'),
			], 
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
		]; 
		
		$form['sf']['private_prek_sch_adv_search']['schedule']['open_until']  = [ 
			'#type' => 'select', 
			'#title' => $this->t('Open Until'), 
			'#options' => [
				'12:00 PM' => $this->t('12:00 PM'),
				'01:00 PM' => $this->t('1:00 PM'),
				'02:00 PM' => $this->t('2:00 PM'),
				'03:00 PM' => $this->t('3:00 PM'),
				'04:00 PM' => $this->t('4:00 PM'),
				'05:00 PM' => $this->t('5:00 PM'),
				'06:00 PM' => $this->t('6:00 PM'),
				'07:00 PM' => $this->t('7:00 PM'),
				'08:00 PM' => $this->t('8:00 PM'),
				'09:00 PM' => $this->t('9:00 PM'),
			], 
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
		]; 

		// public preschool advanced search
		
		$form['sf']['public_pk_as'] = [
			'#tree' => true, 
			'#title' => $this->t('Public Preschool Advanced Search'), 
			'#type' => 'fieldset', 
			'#states' => [ 
				'visible' => [ 
					':input[name="school_type"]' => ['value' => 2 ], 
					':input[name="grade"]' => ['value' => 'PK' ], 
		//			':input[name="cbo"]' => ['checked' => false ], 
				], 
			],
		]; 	
		
		$form['sf']['public_pk_as']['characteristics'] = [
		 	'#type' => 'checkboxes', 
		  	'#title' => $this->t('School characteristics'), 	
			'#options' => [
				1 => $this->t('Dual language'), 
				2 => $this->t('Arts Focus'), 
				3 => $this->t('Career and Technical Education'), 
				4 => $this->t('CBO-UPK (universal prekindergarten)'), 
				5 => $this->t('Charter School'), 
				6 => $this->t('Educational Options'), 
				7 => $this->t('Gifted and Talented Program'), 
				8 => $this->t('Zoned School'), 
				9 => $this->t('New Immigrant Program'), 
				10 => $this->t('Selective Program'), 
				11 => $this->t('Transfer High School'), 
				12 => $this->t('Unzoned School'), 
				13 => $this->t('Wheelchair Accessible'), 
				14 => $this->t('Summer Program'), 
				15 => $this->t('Before School Program'), 
				16 => $this->t('Boarding School'), 
				17 => $this->t('Dress code'), 
				18 => $this->t('Special Education'), 
				19 => $this->t('ASD/ACES Program-Autism Program'), 
				20 => $this->t('Diversity in admissions'), 
				21 => $this->t('IB/2- years of college credit program'), 
				22 => $this->t('Screened'), 
				23 => $this->t('UPK Center (universal prekindergarten)'), 
				24 => $this->t('3K FOR ALL'), 
			], 
		];

		// public school advanced search
		
		$form['sf']['public_sch_adv_search'] = [
			'#tree' => TRUE,
			'#title' => $this->t('Public School Advanced Search'), 
			'#type' => 'fieldset', 
			'#states' => [ 
				'visible' => [ 
					':input[name="school_type"]' => ['value' => 2 ], 
					':input[name="grade"]' => ['!value' => 'PK' ], 
				], 
			],
		]; 	


		$form['sf']['public_sch_adv_search']['characteristics'] = [
		 	'#type' => 'checkboxes', 
		  	'#title' => $this->t('School characteristics'), 	
			'#options' => [
				1 => $this->t('Dual language'), 
				2 => $this->t('Arts Focus'), 
				3 => $this->t('Career and Technical Education'), 
				4 => $this->t('CBO-UPK (universal prekindergarten)'), 
				5 => $this->t('Charter School'), 
				6 => $this->t('Educational Options'), 
				7 => $this->t('Gifted and Talented Program'), 
				8 => $this->t('Zoned School'), 
				9 => $this->t('New Immigrant Program'), 
				10 => $this->t('Selective Program'), 
				11 => $this->t('Transfer High School'), 
				12 => $this->t('Unzoned School'), 
				13 => $this->t('Wheelchair Accessible'), 
				14 => $this->t('Summer Program'), 
				15 => $this->t('Before School Program'), 
				16 => $this->t('Boarding School'), 
				17 => $this->t('Dress code'), 
				18 => $this->t('Special Education'), 
				19 => $this->t('ASD/ACES Program-Autism Program'), 
				20 => $this->t('Diversity in admissions'), 
				21 => $this->t('IB/2- years of college credit program'), 
				22 => $this->t('Screened'), 
				23 => $this->t('UPK Center (universal prekindergarten).'), 
			], 
		];

		$form['sf']['public_sch_adv_search']['grades_served'] = [
			'#type' => 'select', 
			'#title' => $this->t('Grades served'),
			'#options' => [
				'K-5' => $this->t('K - 5'), 
				'K-8' => $this->t('K - 8'), 
				'K-12' => $this->t('K - 12'), 
				'6-8' => $this->t('6 - 8'), 
				'6-12' => $this->t('6 - 12'), 
				'9-12' => $this->t('9 - 12'), 
			], 	
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
			'#states' => [ 
				'invisible' => [ 
					':input[name="grade"]' => ['value' => 'PK'], 
				], 
			],
		]; 


		$form['sf']['public_sch_adv_search']['enrollment'] = [
			'#type' => 'select', 
			'#title' => $this->t('Enrollment'),
			'#options' => [
				'1' => $this->t('Less than 100'), 
				'2' => $this->t('Between 100 and 250'), 
				'3' => $this->t('Between 250 and 500'), 
				'4' => $this->t('Between 500 and 1000'), 
				'5' => $this->t('Greater than 1000'),

			], 	
			'#required' => FALSE, 
			'#empty_option' => $this->t('--All--'), 
		]; 
		

		$form['sf']['search2'] = [ 
			'#type' => 'submit', 
			'#ajax' => [
				'callback' => '::submitSchoolSearchCallback',
				'wrapper' => 'schoolfinder-results', 	
			], 
			'#value' => $this->t('Search'), 
		];


		return $form ; 
	}


	public function validateForm(array &$form, FormStateInterface $form_state) { 
		if ($form_state->getValue('school_name') == 'Reuben') { 
			$form_state->setErrorByName('school_name', $this->t('Reuben is an invalid value.')); 
		} 
	}


	public function submitForm(array &$form, FormStateInterface $form_state) { 

	}

	protected function schoolProfileSearchQuery($params){

		// dpm($params) ; 
		$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
		////dpm($params);

		$query = $nodeStorage->getQuery()
		     ->condition('status', 1)
		     ->condition('type', 'school_profile')
		     ->sort('title', 'ASC') 
		     ->accessCheck(FALSE) ; 					
					

		if (!empty($params['title'])){
			$query->condition('title', '%' . $params['title'] . '%', 'LIKE') ; 
		}

		//  consider school type if cbo is not checked
		if (empty($params['cbo'])){
			$query->condition('field_school_type', $params['school_type']) ;
		}		

		if (!empty($params['nyc_school'])){

			$query->condition('field_school_code', '3%', 'LIKE') ;
			
		}

		if ($params['grade'] == 'K'){ 
			$query->condition('field_grades_served', ['K', 'KFULL', 'KHALF'], 'IN') ;
		}
		else { 
			$query->condition('field_grades_served', [$params['grade']], 'IN') ;

		}

		if (!empty($params['rel_affiliate'])){
			$query->condition('field_religious_affiliation', $params['rel_affiliate']) ; 
		}
		
		if (!empty($params['philosophy'])){
			$query->condition('field_educational_approach', $params['philosophy']) ; 
		}

		if (!empty($params['characteristics'])){
			$query->condition('field_characteristics', $params['characteristics'], 'IN') ; 
		}

		if (!empty($params['cbo'])){

			$query->condition('field_characteristics', 4) ; 

		}


		if (!empty($params['enrollment'])){

			switch ($params['enrollment']){
				case 1: 
					$query->condition('field_total_enrollment', 100, '<') ; 
					break ; 
				case 2: 
					$query->condition('field_total_enrollment', [100, 250], 'BETWEEN') ; 
					break ; 

				case 3: 
					$query->condition('field_total_enrollment', [250, 500], 'BETWEEN') ; 
					break ; 

				case 4: 
					$query->condition('field_total_enrollment', [500, 1000], 'BETWEEN') ; 
					break ; 
				case 5: 
					$query->condition('field_total_enrollment', 1000, '>') ; 
					break ; 
			}
		}

		if (!empty($params['grades_served'])){
			$query->condition('field_grade_served_group', $params['grades_served']) ; 

		}

		if (!empty($params['maximum_tuition'])){

			// query tuition
			$x = 1; 

		}

	// 	if (!empty($params['pk_age_year']) || !empty($params['pk_age_month'])){
	// 		$age = $params['pk_age_year'] . "." . $params['pk_age_month'] ; 

	// 		// //dpm($age) ; 
	// 		$query->condition('field_ages_served_from', $age, '>=') ; 
	// //		$query->condition('field_ages_served_to', $age, '<=') ; 
	// 	}

	//	$query->range(0, self::MAX_RESULT_DISPLAY) ; 

		$ids = $query->execute();  
		// \Drupal::logger('fgertgher')->warning('<pre><code>' . print_r(['IDS' => $ids], TRUE) . '</code></pre>');
	
		$nodes = $nodeStorage->loadMultiple($ids);		

		if(!empty($params['pk_age_year']) && !empty($params['pk_age_month'])){
			$duration = (float) $params['pk_age_year'] . '.' . $params['pk_age_month'];
			 }
			 if(!empty($params['pk_age_year']) && (empty($params['pk_age_month']) || $params['pk_age_month'] == '0')){
				$duration = (float) $params['pk_age_year'] . '.' . 0;
			}
				 if((empty($params['pk_age_year']) || $params['pk_age_year'] == '0') && !empty($params['pk_age_month'])){
					$duration =  (float) (0). '.' . $params['pk_age_month'];
					 }
					//  \Drupal::logger('DURATION')->warning('<pre><code>' . print_r($duration, TRUE) . '</code></pre>');
				if(isset($duration)){
					
					// \Drupal::logger('duration type')->warning('<pre><code>' . print_r(['duration' => gettype($duration)], TRUE) . '</code></pre>');
					$new_nodes = [];
					foreach ($nodes as $node){									
								 if ($node->hasField('field_schedule') && !$node->get('field_schedule')->isEmpty()) {
									// $paragraphs = $node->get('field_schedule')->referencedEntities();
									$in_range = false;
									foreach ($node->get('field_schedule')->referencedEntities() as $paragraph) {
										$from = $paragraph->field_age_range->getValue()[0]['from'];
										$to = $paragraph->field_age_range->getValue()[0]['to'];										
										// \Drupal::logger('From_to_values')->warning('<pre><code>' . print_r(['from' => gettype((float) $from), 'to' => gettype((float) $to)], TRUE) . '</code></pre>');
										if($duration >= (float) $from && $duration <= (float) $to){	
											$in_range = true;
											// \Drupal::logger('Id of node')->warning('<pre><code>' . print_r($node->id(), TRUE) . '</code></pre>');
											break;
										}
									}
									if($in_range == true){	
										$new_nodes[] = $node ;
									}
								}
								
					}
					return array($new_nodes,count($new_nodes));
				}
				else{
					return array($nodes,count($nodes));
				}
		// return array($nodes,count($nodes));
	}

	public function schoolprofiler_get_schools($params){
		$nodeStorage = \Drupal::entityTypeManager()->getStorage('node') ; 

		$query = $nodeStorage->getQuery() ; 

		$query->condition('type', 'school') ; 

		if (!empty($params['total_enrollment'])){
			switch ($params['total_enrollment']){
				case 1:
		}

	//	$count_query = clone $query ; 

	//	$count_query->range(null) ; 

	//	$total_count = $count_query->count()->execute();

		return array($nodes, count($nodes)) ; 
	}
	}
	public function schoolprofiler_add_criteria($query, $field_name, $value, $compare = '='){
		// //dpm('xxx') ; 
		if (!empty($value)){
			return $query->condition($field_name, $value, $compare) ; 
		}

		return $query ; 
	}
	public function schoolfinder_save_geo_cache($key, $data) { 
	  $geo_cache_service = \Drupal::service('schoolprofiler.geo.cache');
	  $geo_cache_service->saveGeoCache($key, $data);

	}


	public function schoolfinder_get_geo_cache($key) {
	  $geo_cache_service = \Drupal::service('schoolprofiler.geo.cache') ; 
	  $geo_data = $geo_cache_service->getGeoCache($key);

	  return $geo_data ; 
	}

	public function proximity_filter($max_distance, $distance){
		
		return  $distance <= $max_distance ? true : false ;

	}
	public function sort_temp($a, $b){

		if ($a[1] == $b[1]){ 
			return 0 ; 
		}

		return ($a[1] < $b[1]) ? -1 : 1 ; 

	} 

	public function get_schoolprofile_address($node){
		if ($node->hasField('field_address') && !$node->get('field_address')->isEmpty()) { 
			$address_field = $node->get('field_address')->first();
			$address = $address_field->address_line1;
	
			if (!empty(trim($address_field->address_line2))) { 
				$address .= ' ' . $address_field->address_line2;
			}
			$address .= ', ' . $address_field->locality . ', ' . $address_field->administrative_area . ' ' . $address_field->postal_code;
		}

		return $address  ; 

	}

	public function basic_schoolfinder_results($nodes){

		$total_schools = 0 ; 
		foreach ($nodes as $node) {

			$id = $node->id(); 
		
			$node_link = "<a href=\"{$node->toUrl()->toString()}\" target=\"_blank\">{$node->title->value}</a>"; 

			$address = $this->get_schoolprofile_address($node) ; 

			$html .= "<tr id=\"{$node->id()}\">";
			$html .= "<td><input class=\"sf-nid-checkbox\" type=\"checkbox\" name=\"nid\" value=\"{$node->id()}\" id=\"nid-{$node->id()}\"></td>";
			
			$html .= "<td><div class=\"sf-results-title\"><label for=\"{$node->id()}\"> {$node_link} " . 
				"</label></div><div class=\"school-address\"> {$address}</div></td></tr>";
				
			++$total_schools ; 

			if ($total_schools == self::MAX_RESULT_DISPLAY){
				break ; 
			}
		}

		return array($html, $total_schools) ; 


	}
	public function proximity_schoolfinder_results($nodes, $params){
		$geo_data = false ; 
		$proximity_address = [ 
			'street' => $params['address'], 
			'city' => $params['city'], 
			'state' => 'NY', 
			'zip' => $params['zip'], 
		] ; 

		$location = strtolower(str_replace(' ', '', implode('~', $proximity_address))); 

		$check_cache = true; 

		if ($check_cache){
			//dpm('checking to see if we have the ' . $location . ' in cache') ; 
			// this returns a database field as a json string or false.
			$geo_data = json_decode($this->schoolfinder_get_geo_cache($location), true); 
		}
			// address not in cache
		if (!$geo_data) {
			//dpm('geo data is not in the cache... attempting to geo code the address') ; 

			// return a json object. we need to check for success
			$geo_data = schoolprofiler_geocode_get_lat_long($proximity_address) ; 

			$geo_data = json_decode($geo_data->getContent(), true) ; 

			//dpm('result from geocoding') ; 
			//dpm($geo_data) ; 

			if ($geo_data['status'] == 'success' && $geo_data['data']) {
				$geo_data = $geo_data['data'] ; // contains lat long

				// add the address to cache for future use
				$this->schoolfinder_save_geo_cache($location, $geo_data) ; 
			}
		}

		$temp = [] ; 

		foreach ($nodes as $node) {

			$id = $node->id(); 
		
			$node_link = "<a href=\"{$node->toUrl()->toString()}\" target=\"_blank\">{$node->title->value}</a>"; 
			
			$address = $this->get_schoolprofile_address($node) ; 

			$get_geolocation = $node->get('field_geolocation')->getValue();

			$latitudeTo = $get_geolocation['0']['lat'];
			$longitudeTo = $get_geolocation['0']['lng'];

			$distance = null ;
		
			if (!empty($latitudeTo) && !empty($longitudeTo) && $geo_data) {
				$miles = schoolprofiler_geocode_get_distance($geo_data, array('lat' => $latitudeTo, 'lon' => $longitudeTo)) ; 
				$distance = $miles . ' miles' ; 
			} 
			else {
				$distance = 'unable to calculate distance possibly due to data issue';
			}

			if (!empty($params['max_distance'])){
				if (!$this->proximity_filter($params['max_distance'], $miles)){
					continue ; 
				}
			}

			$html .= "<tr id=\"node-{$node->id()}\">";
			$html .= "<td><input class=\"sf-nid-checkbox\" type=\"checkbox\" name=\"nid\" value=\"{$node->id()}\" id=\"nid-{$node->id()}\"></td>";
			
			$html .= "<td><div class=\"sf-results-title\"><label for=\"node-{$node->id()}\"> {$node_link} " . 
				"</label></div><div class=\"school-address\"> {$address}</div><div class=\"school-zip-code\">Approximate Distance: {$distance}</div></td></tr>";




			$temp[] = array(0 => $html, 1 => $miles) ;
			$html = '' ; 
		}	


		// sort by miles ascending 
		usort($temp, array( $this, "sort_temp")); 
		
		$nresult = 0 ; 
		foreach ($temp as $k => $v){
			++$nresult ; 

			$html .= $v[0] ; 

			if ($nresult == self::MAX_RESULT_DISPLAY){
				break ; 
			}
		}

		return array($html, count($temp)) ; 



	}

	public function submitSchoolSearchCallback(array &$form, FormStateInterface $form_state){

		////dpm($form_state) ; 
		$node_value = "<ul>" ; 

		$element = $form['sf-compare'] ; 
		$school_name = $form_state->getValue('school_name') ; 
		$grade = $form_state->getValue('grade') ;		
		$school_type = $form_state->getValue('school_type') ; 				
		$cbo = $form_state->getValue('cbo') ; 				
		$nyc_school = $form_state->getValue('nyc_school') ; 				
		$address = $form_state->getValue(['location', 'address1']) ; 		
		$city = $form_state->getValue(['location', 'city']) ; 
		$zip= $form_state->getValue(['location', 'zip']) ;
		////dpm($zip);
		$max_distance= $form_state->getValue(['location', 'distance']) ; 		

		$characteristics = array() ; 		
		$params = [] ; 

		if (!empty($max_distance)){
			$params['max_distance'] = $max_distance ; 
		}

		if (!empty($school_name)){
			$params['title'] = $school_name ; 
		}

		$params['grade'] = $grade ; 

		$params['school_type'] = $school_type ; 
		
		if (!empty($cbo)){
			$params['cbo'] = $cbo ; 
		}

		if (!empty($nyc_school)){
			$params['nyc_school'] = $nyc_school ; 
		}


		if (!empty($address)){
			$params['address'] = $address ; 
		}

		if (!empty($city)){
			$params['city'] = $city; 
		}
		
		if (!empty($zip)){
			$params['zip'] = $zip ; 
		}

		if ($grade == 'PK' && $school_type == 1){ 

			//TODO: cbo search
		
			//TODO: schedule age search
			$pk_age_year = $form_state->getValue(['private_prek_sch_adv_search', 'age', 'pk_age_year']) ; 

			if (!empty($pk_age_year)){
				$params['pk_age_year'] = $pk_age_year ; 
			}

			$pk_age_month = $form_state->getValue(['private_prek_sch_adv_search', 'age', 'pk_age_month']) ; 

			if (!empty($pk_age_month)){
				$params['pk_age_month'] = $pk_age_month ; 
			}

			// educational philosophy search
			
			$philosophy = $form_state->getValue(['private_prek_sch_adv_search', 'philosophy']) ; 

			if (!empty($philosophy)){
				$params['philosophy'] = $philosophy ; 
			}


			// Student enrollment search

			$enrollment = $form_state->getValue(['private_prek_sch_adv_search', 'enrollment']) ; 

			if (!empty($enrollment)){
				$params['enrollment'] = $enrollment ; 
			}

			// Religious affiliation

			$rel_affiliate = $form_state->getValue(['private_prek_sch_adv_search', 'religious_affiliation']) ; 

			if (!empty($rel_affiliate)){
				$params['rel_affiliate'] = $rel_affiliate ; 
			}

			// TODO: schedule search min days offered

 			$min_days_offered = $form_state->getValue(['private_prek_sch_adv_search', 'min_days_offered']) ; 

			if (!empty($min_days_offered)){
				$params['min_days_offered'] = $min_days_offered ; 
			}

			//TODO: schedule search open by and open until 
			$open_by = $form_state->getValue(['private_prek_sch_adv_search', 'schedule', 'open_by']) ; 

			if (!empty($open_by)){
				$params['open_by'] = $open_by ; 
			}
			$open_until = $form_state->getValue(['private_prek_sch_adv_search', 'schedule', 'open_until']) ; 
			if (!empty($open_until)){
				$params['open_until'] = $open_until ; 
			}
		}
		else if ($grade == 'PK' && $school_type == 2){
			$characteristics = array_filter($form_state->getValue('public_pk_as')['characteristics']) ; 
			//dpm($characteristics) ; 

			if (!empty($characteristics)){
				$params['characteristics'] = $characteristics ; 
			}


		}

		else if ($grade != 'PK' && $school_type == 2){

			$characteristics = array_filter($form_state->getValue('public_sch_adv_search')['characteristics']) ; 
			//dpm($characteristics) ; 
			if (!empty($characteristics)){
				$params['characteristics'] = $characteristics ; 
			}

			$enrollment = $form_state->getValue(['public_sch_adv_search', 'enrollment']) ; 

			if (!empty($enrollment)){
				$params['enrollment'] = $enrollment ; 
			}
			
			$grades_served = $form_state->getValue(['public_sch_adv_search', 'grades_served']) ; 
			if (!empty($grades_served)){
				$params['grades_served'] = $grades_served ; 
			}
		 

		}
		else if ($grade != 'PK' && $school_type == 1){
			// Religious affiliation

			$rel_affiliate = $form_state->getValue(['private_sch_adv_search', 'religious_affiliation']) ; 

			if (!empty($rel_affiliate)){
				$params['rel_affiliate'] = $rel_affiliate ; 
			}
			// Student enrollment search

			$enrollment = $form_state->getValue(['private_sch_adv_search', 'enrollment']) ; 

			if (!empty($enrollment)){
				$params['enrollment'] = $enrollment ; 
			}

			$grades_served = $form_state->getValue(['private_sch_adv_search', 'grades_served']) ; 
			if (!empty($grades_served)){
				$params['grades_served'] = $grades_served ; 
			}

			$maximum_tuition = $form_state->getValue(['private_sch_adv_search', 'maximum_tuition']) ; 

			if (!empty($maximum_tuition)){
				$params['maximum_tuition'] = $maximum_tuition ; 
			}
		 
		 
		}

 

		$node_value .= '<li> School Name: ' . $school_name . '</li>' ; 
		$node_value .= '<li> Grade: ' . $grade . '</li>' ; 
		$node_value .= '<li> School type: ' . $school_type . '</li>' ; 
		$node_value .= '<li> Address: ' . $address . '</li>' ; 
		$node_value .= '<li> City: ' . $city . '</li>' ; 
		$node_value .= '<li> Zip: ' . $zip . '</li>' ; 
		$node_value .= '<li> Distance: ' . $distance . '</li>' ; 
		$node_value .= '<li> Religious Affiliation: ' . $rel_affiliate . '</li>' ; 
		$node_value .= '<li> Enrollment: ' . $enrollment . '</li>' ; 
		$node_value .= '<li> Grades served: ' . $grades_served . '</li>' ; 
		$node_value .= '<li> Maximum tuition: ' . $max_tuition . '</li>' ; 
		$node_value .= '<li> Coeducational: ' . $coed . '</li>' ; 
		$node_value .= '<li> Preschool age - Year: ' . $pk_age_year . '</li>' ; 
		$node_value .= '<li> Preschool age - Month: ' . $pk_age_month . '</li>' ; 
		$node_value .= '<li> Philosophy: ' . $philosophy . '</li>' ; 
		$node_value .= '<li> Minimum days offered: ' . $min_days_offered . '</li>' ; 
		$node_value .= '<li> Open by: ' . $open_by . '</li>' ; 
		$node_value .= '<li> Open until: ' . $open_until . '</li>' ; 
		$node_value .= '<li> School characteristics: ' . implode(", ", $characteristics) . '</li>' ; 


		foreach ($form_state->getValues() as $key => $value) { 
			$node_value .= '<p>' . $key . ' => ' . $value . '</p>' ; 
		}
		$values =  $form_state->getValues() ; 
		$private_sch_adv_search = $form_state->getValue(['private_sch_adv_search', 'enrollment']) ; 
		$sqresults =  $this->schoolProfileSearchQuery($params) ; 		

		$nodes = $sqresults[0] ; 
		// \Drupal::logger('what_a_nice_logName')->warning('<pre><code>' . print_r(count($nodes), TRUE) . '</code></pre>');
		$total_rows = $sqresults[1] ; 


		$node_value .= 'pk_age_year => ' .  $private_sch_adv_search; 
		
		$html_header  = '' ; 
		$link_options = [
			'attributes' => ['class' => ['sf-nid-checkbox'], 'target' => ['_blank'] ], 
		] ; 

		$html = "" ; 

		// process the school nodes one at a time and calculat the distance
		$proximity_search = false ; 

		$sf_results_form = array() ; 



		$address_val = $params['address'];
		$city_val = $params['city'] ; 
		$zip_val = $params['zip'];

		if (!empty($address_val) && !empty($city_val) && !empty($zip_val)){
			$res = $this->proximity_schoolfinder_results($nodes, $params) ; 
		}
		else { 
			$res = $this->basic_schoolfinder_results($nodes) ; 
		}

		$html = $res[0] ; 
		$form['sf-compare']['title'] = array(
			'#markup' => "<input id=\"compare_schools\" type=\"hidden\" name=\"compare_schools\" value=\"\"> <div id=\"schoolfinder-results\"> <div id=\"compare-btn-top\" class=\"compare-btn\">" . 
			"<span class=\"clear-compare-list\"> </span><a class=\"compare-url\" target=\"_blank\" href=\"/compare/$school_type/$grade\"> " . 
			"<img src=\"/modules/custom/schoolprofiler/images/compare.png\"> </a></div>" . 
			"<table id=\"finder-results\"><thead><tr><th></th><th>School</th></tr></thead> {$html} </table><div id=\"compare-btn-bottom\" class=\"compare-btn\">" .
			"<span class=\"clear-compare-list\"> </span><a class=\"compare-url\" target=\"_blank\" href=\"/compare/$school_type/$grade\">" .
		       "<img src=\"/modules/custom/schoolprofiler/images/compare.png\"> </a></div>", 
					'#allowed_tags' => array_merge(\Drupal\Component\Utility\Xss::getAdminTagList(), ['button','span','input', 'a', 'div', 'img']), 
		); 


		if ($res[1] >= self::MAX_RESULT_DISPLAY ){
		   $messenger = \Drupal::messenger();
		   $messenger->addWarning(t('Your search returned ' . number_format($total_rows) . ' schools, but we’ve displayed only the first ' . self::MAX_RESULT_DISPLAY . ' results. To refine your search and see more specific matches, consider adding additional criteria'));

		}

	return $form['sf-compare']['title'] ; 
  }


}
