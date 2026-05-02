<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CallEvaluator2 {
    private $groqApiKey;
    private $groqApiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private $httpClient;

    public function __construct($apiKey) {
        $this->groqApiKey = $apiKey;
        $this->httpClient = new Client([
            'headers' => [
                'Authorization' => "Bearer {$this->groqApiKey}",
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * Convert transcript segments to readable text
     */
    private function formatTranscript($segments) {
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
    public function evaluateCall($transcriptData, $template, $evaluationId = null) {
        // Format transcript
        $transcript = $this->formatTranscript($transcriptData['segments']);

        // Build evaluation criteria for prompt
        $criteriaPrompt = $this->buildCriteriaPrompt($template);

        // Create the prompt
        $prompt = $this->createEvaluationPrompt($transcript, $criteriaPrompt);

        // Call Groq API
        $groqResponse = $this->callGroqApi($prompt);

        if (!$groqResponse) {
            throw new Exception("Failed to get response from Groq API");
        }

        // Parse response and format as required array
        return $this->formatEvaluationResults($groqResponse, $template, $evaluationId);
    }

    /**
     * Build criteria description for the prompt
     */
    private function buildCriteriaPrompt($template) {
        $prompt = "EVALUATION CRITERIA:\n\n";

        foreach ($template['criteria'] as $criteria) {
            $prompt .= "CRITERIA: {$criteria['name']} (Weight: {$criteria['weight']}%)\n";
            $prompt .= "Description: {$criteria['description']}\n\n";

            foreach ($criteria['subcriteria'] as $subcriteria) {
                $prompt .= "  SUBCRITERIA: {$subcriteria['name']} (Weight: {$subcriteria['weight']}%)\n";
                $prompt .= "  Description: {$subcriteria['description']}\n\n";
            }
        }

        return $prompt;
    }

    /**
     * Create the complete evaluation prompt
     */
    private function createEvaluationPrompt($transcript, $criteriaPrompt) {
        return "You are a call center quality assurance evaluator. Please evaluate the following call transcript based on the provided criteria.

{$criteriaPrompt}

TRANSCRIPT:
{$transcript}

INSTRUCTIONS:
- Evaluate each subcriteria based on the transcript
- Use the notation system: C (Compliant), PC (Partially Compliant), NC (Non-Compliant), SI (Serious Issue), NE (Not Evaluated)
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
      \"notation\": \"PC\",
      \"comments\": \"Call was closed but without proper recap\"
    }
  ]
}";
    }

    /**
     * Call Groq API
     */
    private function callGroqApi($prompt) {
        $data = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3,
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
        } catch (RequestException $e) {
            error_log("Groq API Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate score based on notation
     */
    private function calculateScore($notation, $weight) {
        $scoreMultipliers = [
            'C' => 1.0,    // Compliant - 100%
            'PC' => 0.7,   // Partially Compliant - 70%
            'NC' => 0.0,   // Non-Compliant - 0%
            'SI' => 0.0,   // Serious Issue - 0%
            'NE' => 0.0    // Not Evaluated - 0%
        ];

        $multiplier = $scoreMultipliers[$notation] ?? 0.0;
        return $weight * $multiplier;
    }

    /**
     * Format evaluation results to match required array structure
     */
    private function formatEvaluationResults($groqResponse, $template, $evaluationId) {
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
        $id = 1; // Starting ID, you might want to get the actual next ID from database

        foreach ($evaluationData['evaluations'] as $evaluation) {
            $subcriteriaId = $evaluation['subcriteria_id'];
            $notation = $evaluation['notation'];
            $comments = $evaluation['comments'] ?? '';

            // Find the weight for this subcriteria
            $weight = $this->findSubcriteriaWeight($template, $subcriteriaId);
            $score = $this->calculateScore($notation, $weight);

            $results[] = [
                'id' => $id++,
                'evaluation_id' => $evaluationId ?? 0,
                'subcriteria_id' => $subcriteriaId,
                'notation' => $notation,
                'score' => number_format($score, 2),
                'comments' => $comments,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'subcriteria_name' => $evaluation['subcriteria_name'],
                'criteria_name' => $evaluation['criteria_name'],
                'criteria_id' => $evaluation['criteria_id']
            ];
        }

        return $results;
    }

    /**
     * Find subcriteria weight from template
     */
    private function findSubcriteriaWeight($template, $subcriteriaId) {
        foreach ($template['criteria'] as $criteria) {
            foreach ($criteria['subcriteria'] as $subcriteria) {
                if ($subcriteria['id'] == $subcriteriaId) {
                    return $subcriteria['weight'];
                }
            }
        }
        return 0.0;
    }
}

// Usage example
try {
    // Your Groq API key
    $groqApiKey = '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';

    // Initialize evaluator
    $evaluator = new CallEvaluator($groqApiKey);

    // Your transcript data (from the JSON file)
    $transcriptData = json_decode(file_get_contents('output/1748017779.json'), true);

    // Your QA template
$template = [
    "id" => 1000,
    "name" => "Inbound Sales Template",
    "activity_id" => 1,
    "is_active" => 1,
    "created_at" => "2025-05-23 14:15:12",
    "criteria" => [
        [
            "id" => 1002,
            "template_id" => 1000,
            "name" => "Call Handling",
            "description" => "Greeting, tone, closure of call",
            "weight" => 60.00,
            "order" => 1,
            "subcriteria" => [
                [
                    "id" => 1004,
                    "criteria_id" => 1002,
                    "name" => "Greeting and Introduction",
                    "description" => "Agent greets within 5 seconds, gives name and department. \nUse:\n- C: On-time, clear intro\n- PC: Greeted but intro was unclear\n- NC: No or late greeting\n- SI: No greeting, rude\n- NE: Call disconnected too quickly",
                    "weight" => 30.00,
                    "order" => 1,
                ],
                [
                    "id" => 1005,
                    "criteria_id" => 1002,
                    "name" => "Call Closure",
                    "description" => "Agent confirms resolution, offers help, ends call professionally.\nUse:\n- C: Proper closing with recap\n- PC: Closure present but missing recap\n- NC: Abrupt end\n- SI: Rude or confusing end\n- NE: Call cut unexpectedly",
                    "weight" => 30.00,
                    "order" => 2,
                ],
            ],
        ],
        [
            "id" => 1003,
            "template_id" => 1000,
            "name" => "Product Knowledge and Compliance",
            "description" => "Accuracy and adherence to process",
            "weight" => 39.00,
            "order" => 2,
            "subcriteria" => [
                [
                    "id" => 1006,
                    "criteria_id" => 1003,
                    "name" => "Accuracy of Information",
                    "description" => "All shared info is accurate and compliant.\nUse:\n- C: Fully accurate\n- PC: One minor inaccuracy\n- NC: Wrong info given\n- SI: Major error or risk\n- NE: Info not shared",
                    "weight" => 20.00,
                    "order" => 1,
                ],
                [
                    "id" => 1007,
                    "criteria_id" => 1003,
                    "name" => "Process Compliance",
                    "description" => "Agent follows standard call process.\nUse:\n- C: Fully compliant\n- PC: One small step missed\n- NC: Major step skipped\n- SI: Ignored process\n- NE: Not applicable",
                    "weight" => 19.00,
                    "order" => 2,
                ],
            ],
        ],
        [
            "id" => 1004,
            "template_id" => 1000,
            "name" => "identifiing",
            "description" => "itentification de la societe umanlink",
            "weight" => 1.00,
            "order" => 3,
            "subcriteria" => [
                [
                    "id" => 1008,
                    "criteria_id" => 1004,
                    "name" => "itentification socite",
                    "description" => "c:itentification de la societe umanlink\nnc pas d'identification",
                    "weight" => 1.00,
                    "order" => 1,
                ],
            ],
        ],
    ],
];


    // Generate evaluation
    $evaluationResults = $evaluator->evaluateCall($transcriptData, $template, 23);

    // Display results
    echo "Evaluation Results:\n";
    // Save results to file
    $outputFile = 'output/evaluation_results.json';
    file_put_contents($outputFile, json_encode($evaluationResults, JSON_PRETTY_PRINT));
    echo "Results saved to {$outputFile}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

