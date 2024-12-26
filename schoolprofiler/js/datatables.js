let called = false;
(function (Drupal) {
  Drupal.behaviors.initializeDataTables = {
    attach: function (context, settings) {
      // Declare variables
      let selectedUrlNodes = 0;
      let totalNodeSelected = [];
      const maxCompare = 5;

      // Locate the table
      const datatable = context.querySelector('#finder-results');

      // Disabling all td except the checkbox for all the paginations
      if(document.querySelectorAll('#finder-results tbody td') != null){
        jQuery('#finder-results tbody td').click(function(event) {
          // Check if the click is on the checkbox
          if (jQuery(event.target).is('input[type="checkbox"]')) {
            return; // Let the checkbox click through
          }
          event.stopPropagation(); // Prevent the click event from propagating to the td
        });
      }
      if (datatable && !datatable.dataset.initialized) {
        // Mark the table as initialized
        datatable.dataset.initialized = 'true';

        // Initialize DataTables
        const finderResults = new DataTable(datatable, {
          columnDefs: [
            {
              orderable: false,
              render: DataTable.render.select(),
              targets: 0,
              checkboxes: {
                selectRow: true,
              },
            },
          ],
          select: {
            style: 'multi',
            selector: 'td:first-child',
            headerCheckbox: false,
          },
          order: [[1, 'asc']],
          paging: true,
          searching: true,
          ordering: true,
          info: true,
          pageLength: 10,
          language: {
            emptyTable: 'No data available in table',
            search: 'Search:',
            searchPlaceholder: 'Search within results',
          },
        });

        // Clone and append the compare button
        const node = document.querySelector('#compare-btn-top');
        if (node) {
          const clone = node.cloneNode(true);
          clone.id = 'compare-btn-top2';
          document.querySelector('#schoolfinder-results').appendChild(clone);                    
        }

        // Checkbox click handling
        jQuery('#finder-results tbody td').click(function (event) {
          if (jQuery(event.target).is('input[type="checkbox"]')) {
            return; // Let the checkbox click through
          }
          event.stopPropagation(); // Prevent the click event from propagating to the td
        });

        // Event handler for checkbox changes
        finderResults.on('change', 'input[type="checkbox"]', function () {
          const selectedCount = finderResults.rows({ selected: true }).count();

          function updateCompareLink(selectedNodes) {
            const compareLinks = document.querySelectorAll('.compare-url');
            if (compareLinks) {
              compareLinks.forEach((compareLink) => {
                const currentHref = compareLink.getAttribute('href');
                const baseHref = currentHref.split('/').slice(0, 4).join('/');
                const newHref = `${baseHref}/${selectedNodes.join('|')}`;
                compareLink.setAttribute('href', newHref);
                selectedUrlNodes = newHref.split('|').length;
              });
            }
          }

          if (this.checked) {
            totalNodeSelected.push(this.closest('tr').id);
          } else {
            const index = totalNodeSelected.indexOf(this.closest('tr').id);
            if (index > -1) totalNodeSelected.splice(index, 1);
          }

          const filteredNodes = totalNodeSelected.filter((el) => el != null);
          updateCompareLink(filteredNodes);

          if (selectedCount > 4) {               

            finderResults.rows().every(function () {
              const rowNode = this.node();
              const checkbox = rowNode.querySelector('.dt-select-checkbox');
              if (checkbox && !checkbox.checked) checkbox.disabled = true;
            });
              // To display an alert when the maximum number of schools to compare is reached          
              setTimeout(function() {
                alert('You have reached the maximum limit for school selections. A maximum of ' + selectedCount + ' schools can be compared at a time.');
              }, 100);
          } else {
            finderResults.rows().every(function () {
              const rowNode = this.node();
              const checkbox = rowNode.querySelector('.dt-select-checkbox');
              if (checkbox && !checkbox.checked) checkbox.disabled = false;
            });
          }

          const compareBtn1 = document.getElementById('compare-btn-top');
          const compareBtn2 = document.getElementById('compare-btn-top2');

          // Remove the "entries" text
          const infoDiv = document.getElementById('finder-results_info');
          if (infoDiv) {
              infoDiv.innerHTML = infoDiv.innerHTML.replace(/entries/i, '').trim();
          }


          if (selectedCount < 2) {
            console.log(compareBtn1)
            if (compareBtn1)
               compareBtn1.style.display = 'none';
            if (compareBtn2) 
              compareBtn2.style.display = 'none';
          } else {
            document.querySelectorAll('.clear-compare-list').forEach((element) => {
              element.innerHTML = `Ready to compare ${selectedCount} schools. <span class="clear-list"><a href="#">Clear List</a></span>`;            
            });

            if (compareBtn1) compareBtn1.style.display = 'block';
            if (compareBtn2) compareBtn2.style.display = 'block';

            // Clear button functionality
            document.querySelectorAll('.clear-list a').forEach((btn) => {
              btn.addEventListener('click', function (e) {
                e.preventDefault();
                totalNodeSelected = [];
                if (compareBtn2) compareBtn2.style.display = 'none';

                finderResults.rows().every(function () {
                  const rowNode = this.node();
                  const checkbox = rowNode.querySelector('.dt-select-checkbox');
                  if (checkbox) {
                    checkbox.checked = false;
                    checkbox.disabled = false;
                  }
                });

                finderResults.rows().deselect();
                document.querySelectorAll('.clear-compare-list').forEach((el) => {
                  el.innerHTML = '';
                });

                if (compareBtn1) compareBtn1.style.display = 'none';
              });
            });
          }
        });
      }
      const compareBtnBottom = document.getElementById('compare-btn-bottom');
      const compareBtn1 = document.getElementById('compare-btn-top');
      const compareBtn2 = document.getElementById('compare-btn-top2');

        if (compareBtn1)
           compareBtn1.style.display = 'none';
        if (compareBtn2) 
          compareBtn2.style.display = 'none';
        if (compareBtnBottom)
          compareBtnBottom.style.display = 'none';

    },
  };
})(Drupal);

jQuery(document).ready(function () {
	let removedRows = [];
	jQuery("#reset-rows").hide(); // Hide the restore link by default
  
	// Function to remove a row
	jQuery(".comparison-table tbody tr td:last-child").click(function () {
	  let row = jQuery(this).closest("tr");
	  let table = jQuery(this).closest(".comparison-table");
	  let rowIndex = row.index(); // Get the index of the row within its table
  
	  removedRows.push({ row: row, table: table, index: rowIndex }); // Store the row, its table, and its index
	  row.detach();
	  jQuery("#reset-rows").show();
	});
  
	// Function to restore all rows
	jQuery("#reset-rows a").click(function (e) {
	  e.preventDefault();
	  for (let i = 0; i < removedRows.length; i++) {
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
  });