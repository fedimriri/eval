<?php
/**
 * Footer layout
 */
?>
                    <?php if (is_authenticated()): ?>
                        </div>
                        <!-- content-wrapper ends -->

                        <!-- footer -->
                        <footer class="footer">
                            <div class="footer-inner-wraper">
                                <div class="d-sm-flex justify-content-center justify-content-sm-between">
                                    <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © bootstrapdash.com 2020</span>
                                    <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center"> Free <a href="https://www.bootstrapdash.com/" target="_blank">Bootstrap dashboard templates</a> from Bootstrapdash.com</span>
                                </div>
                            </div>
                        </footer>
                        <!-- footer ends -->
                    </div>
                    <!-- main-panel ends -->
                </div>
                <!-- page-body-wrapper ends -->
            <?php endif; ?>
        </div>
        <!-- container-scroller -->

        <!-- plugins:js -->
        <script src="<?= asset_url('vendors/js/vendor.bundle.base.js'); ?>"></script>

        <!-- DataTables JS -->
        <script src="<?= asset_url('vendors/datatables/dataTables.min.js'); ?>"></script>
        <script src="<?= asset_url('vendors/datatables/dataTables.bootstrap4.min.js'); ?>"></script>

        <!-- Chart.js -->
        <script src="<?= asset_url('vendors/chart.js/Chart.min.js'); ?>"></script>

        <!-- Custom js -->
        <script src="<?= asset_url('js/off-canvas.js'); ?>"></script>
        <script src="<?= asset_url('js/hoverable-collapse.js'); ?>"></script>
        <script src="<?= asset_url('js/misc.js'); ?>"></script>
        <script src="<?= asset_url('js/chart.js'); ?>"></script>



        <!-- Initialize DataTables -->
        <script>
            $(document).ready(function() {
                $('.datatable').DataTable({
                    responsive: true,
                    order: [], // Remove default sorting
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        emptyTable: "" // Remove the "No data available in table" message
                    },
                    // Fix for "Requested unknown parameter" error
                    columnDefs: [
                        {
                            targets: '_all',
                            render: function(data, type, row, meta) {
                                return data !== undefined ? data : '';
                            }
                        }
                    ]
                });
            });
        </script>


<script>


    // Add CSS for multiSelect
    jQuery('head').append(`
      <style>
        .multiSelect_list {
          max-height: 200px;
          overflow-y: auto;
          overflow-x: hidden;
          scrollbar-width: thin;
        }
        .multiSelect_list::-webkit-scrollbar {
          width: 6px;
        }
        .multiSelect_list::-webkit-scrollbar-track {
          background: #f1f1f1;
        }
        .multiSelect_list::-webkit-scrollbar-thumb {
          background: #888;
          border-radius: 3px;
        }
        .multiSelect_list::-webkit-scrollbar-thumb:hover {
          background: #555;
        }
        .multiSelect_dropdown {
          max-height: 100px;
          overflow-y: auto;
          overflow-x: hidden;
          padding: 5px;
          display: flex;
          flex-wrap: wrap;
          align-items: center;
        }
        .multiSelect_choice {
          margin: 2px;
          padding: 2px 5px;
          display: inline-flex;
          align-items: center;
          background-color: #f0f0f0;
          border-radius: 3px;
          border: 1px solid #ddd;
        }
        .multiSelect_deselect {
          cursor: pointer;
          margin-left: 5px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          padding: 2px;
          border-radius: 50%;
          background-color: rgba(0,0,0,0.1);
        }
        .multiSelect_deselect:hover {
          background-color: rgba(0,0,0,0.2);
        }
      </style>
    `);

    // Initialize multiSelect components
    function initMultiSelect() {
      jQuery('.multiSelect').each(function() {
        var container = jQuery(this);

        // Skip if already initialized
        if (container.hasClass('initialized')) {
          return;
        }

        var selectField = container.find('select');
        var placeholder = selectField.attr('data-placeholder') || 'Select options';
        var uniqueId = 'multiSelect_' + Math.floor(Math.random() * 1000000);

        // Create the custom UI elements
        container.addClass('initialized');
        selectField.hide();

        var dropdownHtml = '<div class="multiSelect_dropdown"></div>';
        var placeholderHtml = '<span class="multiSelect_placeholder">' + placeholder + '</span>';
        var listHtml = '<ul class="multiSelect_list" id="' + uniqueId + '"></ul>';
        var arrowHtml = '<span class="multiSelect_arrow"></span>';

        container.append(dropdownHtml + placeholderHtml + listHtml + arrowHtml);

        var dropdown = container.find('.multiSelect_dropdown');
        var list = container.find('.multiSelect_list');

        // Add options to the list
        selectField.find('option').each(function() {
          var option = jQuery(this);
          var value = option.val();
          var text = option.text();
          var isSelected = option.prop('selected');

          var optionClass = isSelected ? 'multiSelect_option -selected' : 'multiSelect_option';
          var optionHtml = '<li class="' + optionClass + '" data-value="' + value + '">' +
                           '<a class="multiSelect_text">' + text + '</a></li>';

          list.append(optionHtml);

          // If option is selected, add it to the dropdown
          if (isSelected) {
            var choiceHtml = '<span class="multiSelect_choice" data-value="' + value + '">' +
                             text + '<span class="multiSelect_deselect"><svg class="-iconX" width="16" height="16" viewBox="0 0 24 24"><g stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></g></svg></span></span>';
            dropdown.append(choiceHtml);
            dropdown.addClass('-hasValue');
          }
        });

        // Set up dropdown attributes and position
        dropdown.attr('data-multiple', 'true');
        list.css('top', dropdown.height() + 5);

        // Handle option click
        container.on('click', '.multiSelect_option', function(e) {
          e.stopPropagation();

          var option = jQuery(this);
          var value = option.data('value');
          var text = option.find('.multiSelect_text').text();

          // Toggle selection
          if (option.hasClass('-selected')) {
            // Deselect
            option.removeClass('-selected');
            selectField.find('option[value="' + value + '"]').prop('selected', false);
            dropdown.find('.multiSelect_choice[data-value="' + value + '"]').remove();
          } else {
            // Select
            option.addClass('-selected');
            selectField.find('option[value="' + value + '"]').prop('selected', true);

            var choiceHtml = '<span class="multiSelect_choice" data-value="' + value + '">' +
                             text + '<span class="multiSelect_deselect"><svg class="-iconX" width="16" height="16" viewBox="0 0 24 24"><g stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></g></svg></span></span>';
            dropdown.append(choiceHtml);
          }

          // Update dropdown state
          if (dropdown.find('.multiSelect_choice').length > 0) {
            dropdown.addClass('-hasValue');
          } else {
            dropdown.removeClass('-hasValue');
          }

          // Update list position
          list.css('top', dropdown.height() + 5);
        });

        // Handle deselect button click - using direct binding for SVG elements
        jQuery(document).on('mousedown', '.multiSelect_deselect', function(e) {
          // Stop the event completely
          e.stopPropagation();
          e.preventDefault();

          var choice = jQuery(this).closest('.multiSelect_choice');
          var value = choice.data('value');
          var container = choice.closest('.multiSelect');
          var dropdown = container.find('.multiSelect_dropdown');
          var list = container.find('.multiSelect_list');
          var selectField = container.find('select');

          // Remove the choice
          choice.remove();

          // Deselect the option
          list.find('.multiSelect_option[data-value="' + value + '"]').removeClass('-selected');
          selectField.find('option[value="' + value + '"]').prop('selected', false);

          // Update dropdown state
          if (dropdown.find('.multiSelect_choice').length === 0) {
            dropdown.removeClass('-hasValue');
          }

          // Update list position
          list.css('top', dropdown.height() + 5);

          // Return false to prevent any further event handling
          return false;
        });

        // Toggle dropdown on click
        dropdown.on('click', function(e) {
          // Don't toggle if clicking on a deselect button
          if (jQuery(e.target).closest('.multiSelect_deselect').length) {
            return;
          }

          e.stopPropagation();
          e.preventDefault();

          dropdown.toggleClass('-open');
          list.toggleClass('-open').scrollTop(0).css('top', dropdown.height() + 5);
        });

        // Close dropdown when clicking outside
        jQuery(document).on('click', function() {
          dropdown.removeClass('-open');
          list.removeClass('-open');
        });
      });
    }

    // Initialize on document ready
    jQuery(document).ready(function() {
      initMultiSelect();
    });

    // Make the function globally available
    window.initMultiSelect = initMultiSelect;
</script>
    </body>
</html>
