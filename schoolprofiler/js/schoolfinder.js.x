

(function ($, Drupal, once) {

  
  Drupal.behaviors.schoolprofiler = {
    attach: function(context, settings){ 

		$('.compare-btn').hide();

		$(once('checkboxListener', '.sf-nid-checkbox', context)).on('change', function() {
		  const max_compare = 5;
  
		//   console.log('Checkbox has been clicked');
		  const checkedCheckboxes = $('.sf-nid-checkbox').filter(':checked');
		  let checkedCount = checkedCheckboxes.length;
  
		//   console.log('Number of checked boxes ' + checkedCount);
  
		  // Check if the current count exceeds the limit
		  if (checkedCount > max_compare) {
			alert('Maximum number of schools to compare may not exceed ' + max_compare);			
			$(this).prop('checked', false); // Uncheck the last clicked checkbox			
			checkedCount = $('.sf-nid-checkbox').filter(':checked').length; // Recalculate checked count after unchecking the current checkbox
		  }
  
		  // Show or hide the compare button based on the number of checked checkboxes
		  if (checkedCount < 2) {
			$('.compare-btn').hide();
		  } else {
			$('.clear-compare-list').html('Ready to compare ' + checkedCount + ' schools. <span class="clear-list"><a href="#">Clear List</a></span>');
			$('.compare-btn').show();
		  }
  
		  // Clear List link event handler
		  $('.clear-compare-list', context).each(function() {
			$(this).find('a').off('click').on('click', function(e) {
			//   console.log('clear link clicked');
			  e.preventDefault();
			  $('.sf-nid-checkbox').prop('checked', false);
			  $('.compare-btn').hide();
			});
		  });
  
		}); 

		//Compare-Tool | Get the selected node for comparision and create new campare-url 
		let checkboxes = document.querySelectorAll('.sf-nid-checkbox');
		let totalNodeSelected = [];
		if(checkboxes){
			checkboxes.forEach((el)=>{
				el.addEventListener('change',(e)=>{
					if(e.target.checked == true){
						totalNodeSelected.push(e.target.value);
						let totalNodeSelected2 = totalNodeSelected.filter(function (el) {
							return el != null;
						});
						// console.log("Total selected node : "+totalNodeSelected2.join('|'));
						updateCompareLink(totalNodeSelected2);
					}
					else{
						let index = totalNodeSelected.indexOf(e.target.value);
						delete totalNodeSelected[index];
						let totalNodeSelected2 = totalNodeSelected.filter(function (el) {
							return el != null;
						});
						// console.log("Total selected node : "+totalNodeSelected2.join('|'));
						updateCompareLink(totalNodeSelected2);
					}

					function updateCompareLink(selectedNodes) {
						const compareLink = document.querySelector('.compare-url');
						let currentHref = compareLink.getAttribute('href');
						let baseHref = currentHref.split('/').slice(0, 4).join('/');
						const newHref = `${baseHref}/${selectedNodes.join('|')}`;
						compareLink.setAttribute('href', newHref);
					}
					
				});
			});
		}

		$('input[name="school_type"]').on('click', function() {
			$('input[name="school_type"]').not(this).prop('checked', false);			
			// console.log('Selected school type: ' + $(this).val());
		});
 // initialize datatables 
 JQuery('#schoolfinder-results').DataTable();
    } 
  } 		
})(jQuery, Drupal, once);


jQuery(document).ready(function(){
    let removedRows = [];	
	jQuery("#reset-rows").hide(); // Hide the restore link by default

    // Function to remove a row
    jQuery(".comparison-table tbody tr td:last-child").click(function(){
        let row = jQuery(this).closest("tr");
        let table = jQuery(this).closest(".comparison-table");
        let rowIndex = row.index(); // Get the index of the row within its table

        removedRows.push({row: row, table: table, index: rowIndex}); // Store the row, its table, and its index
        row.detach();
		jQuery("#reset-rows").show();
    });

    // Function to restore all rows
    jQuery("#reset-rows a").click(function(e){
        e.preventDefault();
        for(let i = 0; i < removedRows.length; i++) {
            let rowData = removedRows[i];
            let tableBody = rowData.table.find("tbody");
            
            if (rowData.index === 0) {
                tableBody.prepend(rowData.row); // Insert at the beginning if the index is 0
            } else {
                tableBody.find("tr").eq(rowData.index - 1).after(rowData.row); // Insert after the previous row
            }
        }
        removedRows = []; 
		jQuery("#reset-rows").hide(); // Hide the restore link after all rows are restored
    });


// initialize data tables 

});
