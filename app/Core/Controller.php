<?php
/**
 * Controller base class
 */
class Controller {
    /**
     * Load a model
     *
     * @param string $model The model to load
     * @return object The model instance
     */
    protected function model($model) {
        // Include the model file
        require_once dirname(__DIR__) . '/Models/' . $model . '.php';

        // Return a new instance of the model
        return new $model();
    }

    /**
     * Load a view
     *
     * @param string $view The view to load
     * @param array $data Data to pass to the view
     * @param bool $useLayout Whether to use the layout
     * @return void
     */
    protected function view($view, $data = [], $useLayout = true) {
        // Make the global config available to all views
        global $config;
        $data['config'] = $config;

        // Extract data to make it available in the view
        extract($data);

        // Check if view exists
        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';

        // If the view doesn't exist, try converting snake_case to camelCase
        if (!file_exists($viewPath)) {
            // Convert snake_case to camelCase (e.g., business_unit/index to businessUnit/index)
            $parts = explode('/', $view);
            if (count($parts) > 1) {
                $directory = $parts[0];
                $file = $parts[1];

                // Convert directory from snake_case to camelCase
                if (strpos($directory, '_') !== false) {
                    $camelCaseDir = str_replace(' ', '', ucwords(str_replace('_', ' ', $directory)));
                    $camelCaseDir = lcfirst($camelCaseDir);
                    $camelCaseView = $camelCaseDir . '/' . $file;
                    $camelCaseViewPath = dirname(__DIR__) . '/Views/' . $camelCaseView . '.php';

                    if (file_exists($camelCaseViewPath)) {
                        $viewPath = $camelCaseViewPath;
                    }
                }
            }

            // If still not found, try the error page
            if (!file_exists($viewPath)) {
                $this->notFound();
            }
        }

        // If using layout, include it
        if ($useLayout) {
            // Include the header
            include_once dirname(__DIR__) . '/Views/layouts/header.php';

            // Include the view
            include_once $viewPath;

            // Include the footer
            include_once dirname(__DIR__) . '/Views/layouts/footer.php';
        } else {
            // Just include the view
            include_once $viewPath;
        }
    }

    /**
     * Handle not found
     *
     * @return void
     */
    public function notFound() {
        $this->view('errors/404', [], true);
    }

    /**
     * Handle server error
     *
     * @return void
     */
    public function serverError() {
        $this->view('errors/500', [], true);
    }
}
