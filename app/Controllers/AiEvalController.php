<?php

require_once dirname(__DIR__) . '/Services/AIEvaluationService.php';

class AiEvalController extends Controller {
    private $aiService;
    private $evaluationTemplateModel;

    public function __construct() {
        $this->aiService = new AIEvaluationService();
        $this->evaluationTemplateModel = $this->model('EvaluationTemplateModel');

        // Set JSON content type for all responses
        header('Content-Type: application/json');

        // Handle CORS for API requests
        $this->handleCORS();
    }

    /**
     * Handle CORS headers for API requests
     */
    private function handleCORS() {
        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }

            exit(0);
        }
    }

    /**
     * Get the HTTP request method
     */
    private function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get request data based on content type
     */
    private function getRequestData() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            return json_decode($input, true);
        }

        return $_POST;
    }

    /**
     * Send JSON response
     */
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Send error response
     */
    private function sendError($message, $statusCode = 400, $details = null) {
        $response = ['error' => $message];
        if ($details) {
            $response['details'] = $details;
        }
        $this->sendResponse($response, $statusCode);
    }

    /**
     * Validate required parameters
     */
    private function validateRequired($data, $required) {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $this->sendError('Missing required parameters: ' . implode(', ', $missing), 400);
        }
    }

    /**
     * Main router method - handles different HTTP methods
     */
    public function index() {
        $method = $this->getRequestMethod();

        switch ($method) {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle GET requests - return API documentation or status
     */
    private function handleGet() {
        $this->sendResponse([
            'status' => 'success',
            'message' => 'AI Evaluation API',
            'version' => '1.0',
            'endpoints' => [
                'POST /ai_eval/transcribe' => 'Transcribe audio file',
                'POST /ai_eval/evaluate' => 'Evaluate transcript with template',
                'POST /ai_eval/transcribe_and_evaluate' => 'Transcribe and evaluate audio file'
            ]
        ]);
    }

    /**
     * Handle POST requests - route to specific actions
     */
    private function handlePost() {
  // Get the URL to determine which action to take
        $url = $this->getUrlPath();

        if (strpos($url, 'evaluate') !== false && strpos($url, 'transcribe_and_evaluate') === false) {
            // Handle /ai_eval/evaluate
            $this->evaluate();
        } elseif (strpos($url, 'transcribe_and_evaluate') !== false || strpos($url, 'transcribe_and_evaluate') !== false) {
            // Handle /ai_eval/transcribe_and_evaluate
            $this->transcribe_and_evaluate();
        } elseif (strpos($url, 'transcribe') !== false) {
            // Handle /ai_eval/transcribe
            $this->transcribe();
        } else {
            // Default action is transcribe_and_evaluate for backward compatibility
            $this->transcribe_and_evaluate();
        }
    }

    /**
     * Get the current URL path
     */
    private function getUrlPath() {
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    /**
     * Transcribe an audio file
     * POST /ai_eval/transcribe
     */
    public function transcribe() {
        try {
            // Only allow POST method
            if ($this->getRequestMethod() !== 'POST') {
                $this->sendError('Method not allowed', 405);
            }

            // Check if file was uploaded
            if (!isset($_FILES['audio'])) {
                $this->sendError('No audio file uploaded', 400);
            }

            // Validate file upload
            $audioFile = $_FILES['audio'];
            if ($audioFile['error'] !== UPLOAD_ERR_OK) {
                $this->sendError('File upload error: ' . $this->getUploadErrorMessage($audioFile['error']), 400);
            }            // Get parameters
            $language = $_POST['language'] ?? 'fr';
            $transcriptionModel = $_POST['transcriptionModel'] ?? null;
            
            // Log model information
            error_log("Transcription Model received: " . ($transcriptionModel ?? 'not set'));

            // Validate language
            $allowedLanguages = ['fr', 'en', 'es', 'de', 'it'];
            if (!in_array($language, $allowedLanguages)) {
                $this->sendError('Invalid language. Allowed: ' . implode(', ', $allowedLanguages), 400);
            }

            // Process the uploaded audio file
            $audioPath = $this->aiService->processUploadedAudio($audioFile);

            // Transcribe the audio
            $transcription = $this->aiService->transcribeAudio($audioPath, $language, $transcriptionModel);
            $segments = $this->aiService->diarizeWithGroq($transcription['segments']);

            if (!$transcription) {
                throw new Exception('Transcription failed');
            }

            $this->sendResponse([
                'status' => 'success',
                'data' => [
                    'transcription' => ["segments"=>$segments],
                    'language' => $language,
                    'file_info' => [
                        'name' => $audioFile['name'],
                        'size' => $audioFile['size'],
                        'type' => $audioFile['type']
                    ]
    
                ]
            ]);

        } catch (Exception $e) {
            $this->sendError('Transcription failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Evaluate a transcription using a template
     * POST /ai_eval/evaluate
     */
    public function evaluate() {
        try {
            // Only allow POST method
            if ($this->getRequestMethod() !== 'POST') {
                $this->sendError('Method not allowed', 405);
            }

            // Get request data
            $data = $this->getRequestData();

            // Validate required parameters
            $this->validateRequired($data, ['transcript', 'templateId']);

            // Validate template ID is numeric
            if (!is_numeric($data['templateId'])) {
                $this->sendError('Template ID must be a number', 400);
            }

            // Get evaluation template
            $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($data['templateId']);
            if (!$template) {
                $this->sendError('Evaluation template not found', 404);
            }

            // Check if template is active
            if (!$template['is_active']) {
                $this->sendError('Evaluation template is not active', 400);
            }

            // Get evaluation model parameter
            $evaluationModel = $data['evaluationModel'] ?? null;

            $evaluator = new CallEvaluator();

            // Convert plain text transcript to segments format expected by evaluateCall
            $transcriptData = [
                'segments' => [
                    [
                        'start' => 0.0,
                        'end' => 0.0,
                        'text' => $data['transcript'],
                        'speaker' => 'TRANSCRIPT'
                    ]
                ]
            ];

            // Generate evaluation with specified model
            $evaluationResults = $evaluator->evaluateCall($transcriptData, $template, null, $evaluationModel);

            $this->sendResponse([
                'status' => 'success',
                'data' => [
                    'evaluation' => $evaluationResults,
                    'template' => [
                        'id' => $template['id'],
                        'name' => $template['name'],
                    ],
                    'transcript' => $data['transcript']
                ]
            ]);

        } catch (Exception $e) {
            $this->sendError('Evaluation failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Process audio file and generate evaluation
     * POST /ai_eval/transcribe_and_evaluate
     */
public function transcribe_and_evaluate() {
    try {
        // Allow only POST
        if ($this->getRequestMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
            return;
        }

        // Ensure audio file is uploaded
        if (!isset($_FILES['audio'])) {
            $this->sendError('No audio file uploaded', 400);
            return;
        }

        // Validate upload success
        $audioFile = $_FILES['audio'];
        if ($audioFile['error'] !== UPLOAD_ERR_OK) {
            $this->sendError('File upload error: ' . $this->getUploadErrorMessage($audioFile['error']), 400);
            return;
        }

        // Validate template ID
        if (!isset($_POST['templateId']) || !is_numeric($_POST['templateId'])) {
            $this->sendError('Valid Template ID is required', 400);
            return;
        }        $templateId = $_POST['templateId'];
        $language = $_POST['language'] ?? 'fr';
        $evaluationModel = $_POST['evaluationModel'] ?? null;
        $transcriptionModel = $_POST['transcriptionModel'] ?? null;

        // Log model information
        error_log("Evaluation Model received: " . ($evaluationModel ?? 'not set'));
        error_log("Transcription Model received: " . ($transcriptionModel ?? 'not set'));

        // Validate allowed language
        $allowedLanguages = ['fr', 'en', 'es', 'de', 'it'];
        if (!in_array($language, $allowedLanguages)) {
            $this->sendError('Invalid language. Allowed: ' . implode(', ', $allowedLanguages), 400);
            return;
        }

        // Retrieve evaluation template
        $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($templateId);
        if (!$template) {
            $this->sendError('Evaluation template not found', 404);
            return;
        }

        if (!$template['is_active']) {
            $this->sendError('Evaluation template is not active', 400);
            return;
        }

     

        // Process audio and evaluate
        $processedAudio = $this->aiService->processUploadedAudio($audioFile);
        $results = $this->aiService->processAudioForEvaluation(
            $processedAudio,
            $template,
            $language,
            $transcriptionModel,
            $evaluationModel
        );

        // Return structured response
        $this->sendResponse([
            'status' => 'success',
            'data' => [
                'results' => $results,
                'template' => [
                    'id' => $template['id'],
                    'name' => $template['name'],
                ],
                'file_info' => [
                        'name' => $audioFile['name'],
                        'size' => $audioFile['size'],
                        'type' => $audioFile['type']
                ],
                'language' => $language
            ]
        ]);

    } catch (Throwable $e) {
        // Log internal error
        error_log('❌ Audio evaluation failed: ' . $e->getMessage());
        $this->sendError('Processing failed: ' . $e->getMessage(), 500);
    }
}

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }


}
