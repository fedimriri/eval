<?php
/**
 * Table Helper
 *
 * Helper functions for generating reusable table components
 */

/**
 * Generate a reusable table component with DataTables support
 *
 * @param array $config Table configuration
 * @param array $data Data to display in the table
 * @param array $actions Action buttons configuration
 * @return string HTML for the table
 */
function generate_table($config, $data, $actions = []) {
    // Default configuration
    $defaultConfig = [
        'id' => 'data-table-' . uniqid(),
        'class' => 'table table-hover',
        'responsive' => true,
        'columns' => [],
        'empty_message' => 'No data found',
        'datatable' => true,
        'datatable_options' => [
            'paging' => true,
            'searching' => true,
            'ordering' => true,
            'info' => true,
            'responsive' => true,
            'autoWidth' => false,
            'lengthMenu' => [[10, 25, 50, -1], [10, 25, 50, "All"]]
        ]
    ];

    // Merge with provided configuration
    $config = array_merge($defaultConfig, $config);

    // Start building the table HTML
    $html = '';

    // Add responsive wrapper if needed
    if ($config['responsive']) {
        $html .= '<div class="table-responsive">';
    }

    // Add datatable class if enabled
    $tableClass = $config['class'];
    if ($config['datatable']) {
        $tableClass .= ' datatable';
    }

    // Start table
    $html .= '<table id="' . $config['id'] . '" class="' . $tableClass . '">';

    // Table header
    $html .= '<thead><tr>';
    foreach ($config['columns'] as $column) {
        $columnClass = isset($column['class']) ? ' class="' . $column['class'] . '"' : '';
        $columnWidth = isset($column['width']) ? ' width="' . $column['width'] . '"' : '';
        $sortable = isset($column['sortable']) && !$column['sortable'] ? ' data-orderable="false"' : '';

        $html .= '<th' . $columnClass . $columnWidth . $sortable . '>';
        $html .= isset($column['label']) ? $column['label'] : '';
        $html .= '</th>';
    }

    // Add actions column if provided
    if (!empty($actions)) {
        $html .= '<th class="text-center" data-orderable="false">Actions</th>';
    }

    $html .= '</tr></thead>';
    $html .= '<tbody>';

    // Check if data is empty
    if (empty($data)) {
        $colspan = count($config['columns']) + (empty($actions) ? 0 : 1);
        $html .= '<tr><td colspan="' . $colspan . '" class="text-center">' . $config['empty_message'] . '</td></tr>';
    } else {
        // Loop through data
        foreach ($data as $row) {
            $html .= '<tr>';
            
            // Add columns
            foreach ($config['columns'] as $column) {
                $field = $column['field'];
                $value = isset($row[$field]) ? $row[$field] : '';
                
                // Apply formatter if provided
                if (isset($column['formatter']) && is_callable($column['formatter'])) {
                    $value = $column['formatter']($value, $row);
                }
                
                $html .= '<td>' . $value . '</td>';
            }

            // Add actions if provided
            if (!empty($actions)) {
                $html .= '<td class="text-center">';
                foreach ($actions as $action) {
                    // Skip if condition is specified and not met
                    if (isset($action['condition']) && is_callable($action['condition']) && !$action['condition']($row)) {
                        continue;
                    }

                    // Generate URL
                    $url = $action['url'];
                    if (is_callable($action['url'])) {
                        $url = $action['url']($row);
                    }

                    // Generate button
                    $html .= '<a href="' . $url . '" class="btn btn-sm ' . $action['class'] . ' ' . (isset($action['margin']) ? $action['margin'] : 'mr-1') . '"';

                    // Add confirmation if specified
                    if (isset($action['confirm'])) {
                        $html .= ' onclick="return confirm(\'' . $action['confirm'] . '\');"';
                    }

                    // Add data attributes if specified
                    if (isset($action['data']) && is_array($action['data'])) {
                        foreach ($action['data'] as $key => $value) {
                            $dataValue = is_callable($value) ? $value($row) : $value;
                            $html .= ' data-' . $key . '="' . $dataValue . '"';
                        }
                    }

                    $html .= '>';

                    // Add icon if specified
                    if (isset($action['icon'])) {
                        $html .= '<i class="' . $action['icon'] . '"></i> ';
                    }

                    $html .= $action['label'] . '</a>';
                }
                $html .= '</td>';
            }

            $html .= '</tr>';
        }
    }

    $html .= '</tbody>';
    $html .= '</table>';

    // Close responsive wrapper if needed
    if ($config['responsive']) {
        $html .= '</div>';
    }

    return $html;
}
