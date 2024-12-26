(function (Drupal) {
  Drupal.behaviors.schoolfinder = {
    attach: function (context, settings) { 
	// save the value of the selected grade
		    const grade = context.querySelector('#edit-grade') ; 
        let prevGrade = '' ; 
        let newGrade = '' ; 

        if (grade && !grade.hasAttribute('grade-processed')){ 
          grade.setAttribute('grade-processed', true) ; 

		      grade.addEventListener('focus', function () {
				    prevGrade = grade.value  ; 
            // console.log('The prev value is: ' + prevGrade) ; 
          }) ; 

          grade.addEventListener('change', function (){
            newGrade = grade.value ; 
            // console.log('new grade: ' + newGrade) ; 


            const form = context.querySelector('#school-finder-form');

            // save some fields to restore 
            const schoolName = context.querySelector('#edit-school-name') ; 
            const schoolNameValue = context.querySelector('#edit-school-name').value ;
            const schoolType = context.querySelector('input[name="school_type"]:checked'); 

            let schoolTypeValue = false ; 
            if (schoolType){
              schoolTypeValue = context.querySelector('input[name="school_type"]:checked').value ;
            }
            
            const nycSchoolValue = context.querySelector('#edit-nyc-school').checked; 
            const nycSchool = context.querySelector('#edit-nyc-school') ; 
            
            const address1 = context.querySelector('#edit-location-address1');
            const address1Value = context.querySelector('#edit-location-address1').value ;

            const city = context.querySelector('#edit-location-city') ; 
            const cityValue = context.querySelector('#edit-location-city').value ;

            const zip =  context.querySelector('#edit-location-zip') ; 
            const zipValue =  context.querySelector('#edit-location-zip').value ;

            const distance = context.querySelector('#edit-location-distance') ; 
            const distanceValue = context.querySelector('#edit-location-distance').value ; 

            // console.log('Resetting form')  ; 

            // reset the form 

            form.reset() ; 

            schoolName.value = schoolNameValue  ; 
            grade.value = newGrade ; 

            // console.log('nyc school: ' + nycSchool) ; 

            if (nycSchoolValue){
              nycSchool.checked = true ; 
            }

            if (schoolTypeValue){
              schoolType.value = schoolTypeValue ; 
              schoolType.checked = true ; 
              // console.log('school type value : ' + schoolTypeValue) ; 
            }

            address1.value = address1Value  ; 

            city.value = cityValue  ; 

            zip.value = zipValue  ; 

            distance.value = distanceValue ; 

          }) ; 

		    }
  }, 
 };
})(Drupal);
