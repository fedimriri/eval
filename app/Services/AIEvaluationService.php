<?php


// Config is already loaded in index.php, no need to require it again
class AIEvaluationService
{
    private $groqApiKey;
    private $transcriptionApiUrl = 'https://api.groq.com/openai/v1/audio/transcriptions';
    private $chatApiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private $httpClient;
    private $uploadDir = 'uploads/audio/';
    private $outputDir = 'uploads/transcripts/';

    /**
     * Constructor - Initialize the service
     */    public function __construct()
    {
        global $config;

        // Get API key from config
        $this->groqApiKey = $config['groq_api_key'] ?? '';

        if (empty($this->groqApiKey)) {
            throw new Exception('GROQ API key is not set. Please set the GROQ_API_KEY variable or add it to the config.');
        }

        // Update upload and output directories from config
        $this->uploadDir = 'uploads/audio/';
        $this->outputDir = 'uploads/transcripts/';
          // Initialize HTTP client with increased timeout settings
        $this->httpClient = new GuzzleHttp\Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->groqApiKey,
                'Content-Type' => 'application/json'
            ],
            'timeout' => $config['groq_api_timeout'] ?? 1800, // 30 minutes default
            'connect_timeout' => $config['groq_api_connect_timeout'] ?? 120, // 2 minutes default
            'verify' => false // Only if needed for local development
        ]);

        // Create upload and output directories if they don't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }

    /**
     * Process uploaded audio file
     *
     * @param array $file The uploaded file ($_FILES array element)
     * @param string $language The language of the audio (default: 'fr')
     * @return string The path to the saved audio file
     */
    public function processUploadedAudio($file)
    {

        // Validate file
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            error_log("No file uploaded - tmp_name missing or empty");
            throw new Exception('No file uploaded');
        }

        // Check if temporary file exists
        if (!file_exists($file['tmp_name'])) {
            error_log("Temporary file does not exist: " . $file['tmp_name']);
            throw new Exception('Temporary file not found');
        }

        // Check file type
        $allowedTypes = ['audio/mp3', 'audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/x-m4a', 'audio/m4a'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            error_log("Invalid file type: $fileType. Allowed: " . implode(', ', $allowedTypes));
            throw new Exception('Invalid file type. Allowed types: MP3, WAV, M4A');
        }

        // Generate unique filename
        $filename = time() . '_' . uniqid() . '_' . basename($file['name']);
        $filePath = $this->uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            error_log("Failed to move uploaded file from {$file['tmp_name']} to $filePath");
            throw new Exception('Failed to save uploaded file');
        }

        return $filePath;
    }

    /**
     * Process audio file and generate evaluation
     *
     * @param string $audioPath Path to the audio file
     * @param array $template The evaluation template
     * @param string $language The language of the audio (default: 'fr')
     * @param string $transcriptionModel The transcription model to use (optional)
     * @param string $evaluationModel The evaluation model to use (optional)
     * @return array The evaluation results
     */
    public function processAudioForEvaluation($audioPath, $template, $language = 'fr', $transcriptionModel = null, $evaluationModel = null)
    {
        $transcription = $this->transcribeAudio($audioPath, $language, $transcriptionModel);
        if (!$transcription || empty($transcription['segments'])) {
            error_log("Transcription failed or returned empty segments");
            throw new Exception('Failed to transcribe audio');
        }

        $segments = $this->diarizeWithGroq($transcription['segments']);

        $evaluator = new CallEvaluator();
        $transcriptData = [
            'status' => 'success',
            'audio_path' => $audioPath,
            'segments' => $segments
        ];
        try {
            $evaluationResults = $evaluator->evaluateCall($transcriptData, $template, null, $evaluationModel);
        } catch (Exception $e) {
            error_log("Evaluation failed: " . $e->getMessage());
            $evaluationResults = [];
        }

        return [
            'transcript' => $transcriptData,
            'evaluation' => $evaluationResults
        ];
    }

    /**
     * Transcribe audio file using Whisper via Groq OpenAI endpoint
     *
     * @param string $audioPath Path to the audio file
     * @param string $language The language of the audio (default: 'fr')
     * @param string $model The transcription model to use (optional)
     * @return array|null The transcription result
     */
    public function transcribeAudio($audioPath, $language = 'fr', $model = null)
    {
        global $config;

        if (!file_exists($audioPath)) {
            error_log("Audio file not found: $audioPath");
            return null;
        }

        // Use provided model or fall back to config default
        $transcriptionModel = $model ?? $config['transcription_model'] ?? 'whisper-large-v3';

        try {
            // Prepare request data for logging

            $response = $this->httpClient->request('POST', $this->transcriptionApiUrl, [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($audioPath, 'r'),
                        'filename' => basename($audioPath),
                    ],
                    ['name' => 'model', 'contents' => $transcriptionModel],
                    ['name' => 'language', 'contents' => $language],
                    ['name' => 'temperature', 'contents' => '0'],
                    ['name' => 'response_format', 'contents' => 'verbose_json'],
                    ['name' => 'timestamp_granularities[]', 'contents' => 'segment'],
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            $result = json_decode($responseBody, true);



            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error: " . json_last_error_msg());
                return null;
            }

            if (isset($result['error'])) {
                error_log("GROQ API Error: " . json_encode($result['error']));
                return null;
            }

            if (isset($result['segments'])) {
                return [
                    'segments' => array_map(function ($segment) {
                        return [
                            'start' => $segment['start'],
                            'end' => $segment['end'],
                            'text' => $segment['text'],
                        ];
                    }, $result['segments']),
                ];
            }

            error_log("No segments found in response");
            return null;
        } catch (Exception $e) {
            error_log("Transcription failed: " . $e->getMessage());
            error_log("Exception type: " . get_class($e));

            return null;
        }
    }

    /**
     * Send transcript segments to Groq Chat for speaker diarization
     *
     * @param array $segments The transcript segments
     * @return array The diarized segments
     */
    public function diarizeWithGroq($segments)
    {
        global $config;

        // If no segments, return empty array
        if (empty($segments)) {
            return [];
        }

        $segmentsText = '';
        foreach ($segments as $i => $segment) {
            $segmentsText .= sprintf(
                "Segment %d: [%.2fs - %.2fs] %s\n",
                $i + 1,
                $segment['start'],
                $segment['end'],
                $segment['text']
            );
        }        $prompt = <<<EOD
Process the call center transcript segments and identify speakers. Label each segment with:
- AGENT: For professional, script-following, service-providing speaker
- CLIENT: For the customer asking questions or explaining issues

Rules:
1. Return ONLY valid JSON
2. ONLY use "AGENT" or "CLIENT" as speaker labels
3. Label ALL segments - make best guess if unclear
4. Return ONLY timestamp and speaker identification, no text content

Required JSON format:
{
  "segments": [
    {
      "start": 0.0,
      "end": 4.14,
      "speaker": "AGENT"
    },
    {
      "start": 5.1,
      "end": 6.06,
      "speaker": "CLIENT"
    }
  ]
}
EOD;

        $payload = [            'model' =>   $evaluationModel ?? $config['evaluation_model'],
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => "Analyze these French call center transcript segments and return speaker identification in JSON format:\n\n$segmentsText"],
            ],
            'temperature' => 0.1, // Lower temperature for more consistent results
            'max_tokens' => 4000 // Ensure enough tokens for response
        ];

        try {            // Make the API call without streaming
            $response = $this->httpClient->post($this->chatApiUrl, [
                'json' => $payload
            ]);

            $responseBody = $response->getBody()->getContents();
            $body = json_decode($responseBody, true);
            $statusCode = $response->getStatusCode();

            // Check for API errors
            if ($statusCode !== 200) {
                throw new Exception("API returned status code: $statusCode");
            }

            if (!isset($body['choices'][0]['message']['content'])) {
                throw new Exception("Invalid API response structure");
            }

            $content = $body['choices'][0]['message']['content'];

            // Parse the JSON response with multiple strategies
            $parsed = $this->parseJsonResponse($content);

            if ($parsed && isset($parsed['segments']) && is_array($parsed['segments'])) {  
                $processedSegments = [];
                foreach ($parsed['segments'] as $segment) {
                    // Only add valid segments with proper speaker identification
                    if (isset($segment['start']) && isset($segment['end'])) {
                        // Find matching original segment to get the text
                        $originalSegment = current(array_filter($segments, function($orig) use ($segment) {
                            return abs($orig['start'] - $segment['start']) < 0.1 && 
                                   abs($orig['end'] - $segment['end']) < 0.1;
                        }));

                        if (!$originalSegment) {
                            continue; // Skip if no matching original segment found
                        }

                        // Force AI to provide speaker identification or use UNKNOWN
                        $speaker = isset($segment['speaker']) && in_array($segment['speaker'], ['AGENT', 'CLIENT']) 
                                 ? $segment['speaker'] 
                                 : 'UNKNOWN';

                        $processedSegments[] = [
                            'start' => (float) $segment['start'],
                            'end' => (float) $segment['end'],
                            'text' => $originalSegment['text'],
                            'speaker' => $speaker
                        ];
                    }
                }

                if (!empty($processedSegments)) {
                    return $processedSegments;
                }
            }

            // If AI parsing failed, throw exception to force retry
            throw new Exception("AI failed to parse segments properly");

        } catch (Exception $e) {   
            error_log('errror diarisatipon  '.$e->getMessage());
            // If API call failed, mark all segments as UNKNOWN
            return array_map(function ($segment) {
                $segment['speaker'] = 'UNKNOWN';
                return $segment;
            }, $segments);
        }
    }

    /**
     * Parse JSON response with multiple strategies
     *
     * @param string $content The JSON content to parse
     * @return array|null Parsed JSON or null on failure
     */
    private function parseJsonResponse($content)
    {
        // Strategy 1: Direct parsing
        $parsed = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $parsed;
        }

        // Strategy 2: Clean common JSON issues
        $cleanedContent = $content;

        // Remove any text before the first {
        $firstBrace = strpos($cleanedContent, '{');
        if ($firstBrace !== false) {
            $cleanedContent = substr($cleanedContent, $firstBrace);
        }

        // Remove any text after the last }
        $lastBrace = strrpos($cleanedContent, '}');
        if ($lastBrace !== false) {
            $cleanedContent = substr($cleanedContent, 0, $lastBrace + 1);
        }

        // Fix common JSON issues
        $cleanedContent = preg_replace('/,\s*}/', '}', $cleanedContent); // Remove trailing commas before }
        $cleanedContent = preg_replace('/,\s*\]/', ']', $cleanedContent); // Remove trailing commas before ]

        $parsed = json_decode($cleanedContent, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $parsed;
        }

        return null;
    }


}

/**
 * Call Evaluator Class
 *
 * Handles evaluation of call transcripts using Groq API
 */
class CallEvaluator
{
    private $groqApiKey;
    private $groqApiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private $httpClient;


    public function __construct()
    {
        global $config;
        $this->groqApiKey = $config['groq_api_key'] ?? '';

        // Initialize AI logging service

         $this->httpClient = new GuzzleHttp\Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->groqApiKey,
                'Content-Type' => 'application/json',
            ],
            'timeout' => $config['groq_api_timeout'] ?? 1800, // 30 minutes default
            'connect_timeout' => $config['groq_api_connect_timeout'] ?? 120, // 2 minutes default
            'verify' => false // Only if needed for local development
        ]);
    }




    /**
     * Convert transcript segments to readable text
     */
    private function formatTranscript($segments)
    {
        $transcript = "";
        foreach ($segments as $segment) {
            $speaker = $segment['speaker'];
            $text = $segment['text'];
            $transcript .= "{$speaker}: {$text}\n";
        }
        return $transcript;
    }

    /**
     * Generate evaluation using Groq API
     */
    public function evaluateCall($transcriptData, $template, $evaluationId = null, $model = null)
    {
        // Format transcript
        $transcript = $this->formatTranscript($transcriptData['segments']);

        // Build evaluation criteria for prompt
        $criteriaPrompt = $this->buildCriteriaPrompt($template);

        // Create the prompt
        $prompt = $this->createEvaluationPrompt($transcript, $criteriaPrompt);

        // Call Groq API with specified model
        $groqResponse = $this->callGroqApi($prompt, $model);

        if (!$groqResponse) {
            throw new Exception("Failed to get response from Groq API");
        }

        // Parse response and format as required array
        return $this->formatEvaluationResults($groqResponse, $template, $evaluationId);
    }

    /**
     * Build criteria description for the prompt
     */
    private function buildCriteriaPrompt($template)
    {
        $prompt = "EVALUATION CRITERIA:\n\n";

        foreach ($template['criteria'] as $criteria) {
            $prompt .= "CRITERIA: {$criteria['name']}(criteria_id: {$criteria['id']})\n";
            $prompt .= "Description: {$criteria['description']}\n\n";

            foreach ($criteria['subcriteria'] as $subcriteria) {
                $prompt .= "  SUBCRITERIA: {$subcriteria['name']} (subcriteria_id: {$subcriteria['id']})\n";
                $prompt .= "  Description: {$subcriteria['description']}\n\n";
            }
        }

        return $prompt;
    }

    /**
     * Create the complete evaluation prompt
     */
    private function createEvaluationPrompt($transcript, $criteriaPrompt)
    {
        return "You are a call center quality assurance evaluator. Please evaluate the following call transcript based on the provided criteria.

{$criteriaPrompt}

TRANSCRIPT:
{$transcript}

INSTRUCTIONS:
- Evaluate each subcriteria based on the transcript
- Use the notation system: C (Conforme), NC (Non conforme), PC (Point critique), SI (Situation inacceptable)
- Provide brief comments explaining your evaluation
- Be objective and fair in your assessment

Please respond with a JSON object containing evaluations for each subcriteria in this exact format:
{
  \"evaluations\": [
    {
      \"subcriteria_id\": 100,
      \"subcriteria_name\": \"Greeting and Introduction\",
      \"criteria_name\": \"Call Handling\",
      \"criteria_id\": 100,
      \"notation\": \"C\",
      \"comments\": \"Agent provided clear greeting and introduction\"
    },
    {
      \"subcriteria_id\": 101,
      \"subcriteria_name\": \"Call Closure\",
      \"criteria_name\": \"Call Handling\",
      \"criteria_id\": 100,
      \"notation\": \"NC\",
      \"comments\": \"Call was closed but without proper recap\"
    }
  ]
    }";
    }

    /**
     * Call Groq API
     */
    private function callGroqApi($prompt, $model = null)
    {
        global $config;

        // Use provided model or fall back to config default
        $evaluationModel = $model ?? $config['evaluation_model'] ?? 'llama-3.1-8b-instant';

        $data = [
            'model' => $evaluationModel,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $config['evaluation_temperature'] ?? 0.3,
        ];


        try {


            $response = $this->httpClient->post($this->groqApiUrl, [
                'json' => $data
            ]);

            $responseData = json_decode($response->getBody(), true);


            if (!isset($responseData['choices'][0]['message']['content'])) {
                error_log("Invalid Groq API response structure");
                return false;
            }

            return $responseData['choices'][0]['message']['content'];
        } catch (Exception $e) {
            error_log("Groq API Error: " . $e->getMessage());

            return false;
        }
    }


    /**
     * Format evaluation results to match required array structure
     */
    private function formatEvaluationResults($groqResponse, $template, $evaluationId)
    {
        // Try to extract JSON from the response
        $jsonStart = strpos($groqResponse, '{');
        $jsonEnd = strrpos($groqResponse, '}');

        if ($jsonStart === false || $jsonEnd === false) {
            throw new Exception("No valid JSON found in Groq response");
        }

        $jsonString = substr($groqResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
        $evaluationData = json_decode($jsonString, true);

        if (!$evaluationData || !isset($evaluationData['evaluations'])) {
            throw new Exception("Invalid JSON structure in Groq response");
        }

        $results = [];

        foreach ($evaluationData['evaluations'] as $evaluation) {
            $subcriteriaId = $evaluation['subcriteria_id'];
            $notation = $evaluation['notation'];
            $comments = $evaluation['comments'] ?? '';


            $results[] = [
                'subcriteria_id' => $subcriteriaId,
                'notation' => $notation,
                'comments' => $comments,
                'criteria_id' => $evaluation['criteria_id'],
            ];
        }

        return $results;
    }

}