<?php
require 'vendor/autoload.php'; // Composer dependencies for   HTTP requests

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;



$GROQ_API_KEY = "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";


// Initialize HTTP client with default headers
$http = new Client([
    'headers' => [
        'Authorization' => 'Bearer ' . $GROQ_API_KEY,
        'Content-Type'  => 'application/json',
    ],
]);

/**
 * Transcribe audio file using Whisper via Groq OpenAI endpoint
 *
 * @param string $audioPath
 * @param string $language
 * @return array|null
 */
function transcribeAudio($audioPath, $language = 'fr') {
    global $http;

    if (!file_exists($audioPath)) {
        error_log("Audio file not found: $audioPath");
        return null;
    }

    try {
        $response = $http->request('POST', 'https://api.groq.com/openai/v1/audio/transcriptions', [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($audioPath, 'r'),
                    'filename' => basename($audioPath),
                ],
                ['name' => 'model',              'contents' => 'whisper-large-v3'],
                ['name' => 'language',           'contents' => $language],
                ['name' => 'temperature',        'contents' => '0'],
                ['name' => 'response_format',    'contents' => 'verbose_json'],
                ['name' => 'timestamp_granularities[]', 'contents' => 'segment'],
            ],
        ]);

        $result = json_decode($response->getBody(), true);

        if (isset($result['segments'])) {
            return [
                'segments' => array_map(function($segment) {
                    return [
                        'start' => $segment['start'],
                        'end'   => $segment['end'],
                        'text'  => $segment['text'],
                    ];
                }, $result['segments']),
            ];
        }

        return null;
    } catch (Exception $e) {
        error_log("Transcription failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Send transcript segments to Groq Chat for speaker diarization or analysis
 *
 * @param array $segments
 * @return array
 */
function diarizeWithGroq($segments) {
    global $http;

    $segmentsText = '';
    foreach ($segments as $i => $segment) {
        $segmentsText .= sprintf(
            "Segment %d: [%.2fs - %.2fs] %s\n",
            $i + 1,
            $segment['start'],
            $segment['end'],
            $segment['text']
        );
    }

    $prompt = <<<EOD
You are an expert in analyzing call center conversation transcripts. Your task is to identify the call center agent and the client, then assign appropriate speaker labels to each segment.

Analyze the transcript segments and identify who is the AGENT and who is the CLIENT based on:
- Professional language vs casual language
- Who asks for information vs who provides personal information
- Who follows scripts vs who has questions/concerns
- Who offers solutions vs who has problems
- Greeting patterns (agents often start with company name/greeting)

IMPORTANT: Return ONLY valid JSON with this exact structure. Do not include any extra commas, make sure all brackets are properly closed:

{
  "segments": [
    {
      "start": 0.0,
      "end": 10.5, 
      "text": "example text",
      "speaker": "AGENT"
    }
  ]
}

Ensure each segment object is properly formatted with no trailing commas. Return only the JSON object, no other text.
EOD;

    $payload = [
        'model'           => 'llama-3.3-70b-versatile',
        'messages'        => [
            ['role' => 'system',  'content' => $prompt],
            ['role' => 'user',    'content' => "Please analyze the following transcript segments and return the results in JSON format:\n\n$segmentsText"],
        ],
        'temperature'     => 0.2,
        'response_format' => ['type' => 'json_object'],
    ];

    try {
        $response = $http->post(
            'https://api.groq.com/openai/v1/chat/completions',
            ['json' => $payload]
        );
        $body = json_decode($response->getBody(), true);
        
        // Try to parse the JSON response and extract segments
        if (isset($body['choices'][0]['message']['content'])) {
            $content = $body['choices'][0]['message']['content'];
            
            // Clean up the JSON content before parsing
            $content = trim($content);
            
            // Try to parse the JSON
            $parsed = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['segments'])) {
                // Clean and validate each segment
                $processedSegments = [];
                foreach ($parsed['segments'] as $segment) {
                    // Only add valid segments
                    if (isset($segment['start']) && isset($segment['end']) && isset($segment['text'])) {
                        $processedSegments[] = [
                            'start' => (float)$segment['start'],
                            'end' => (float)$segment['end'],
                            'text' => trim($segment['text']),
                            'speaker' => $segment['speaker'] ?? 'UNKNOWN'
                        ];
                    }
                }
                
                if (!empty($processedSegments)) {
                    return $processedSegments;
                }
            }
            
            // If JSON parsing failed, log the error and try to fix common issues
            error_log("JSON parsing failed. Raw content: " . $content);
            error_log("JSON error: " . json_last_error_msg());
        }
        
        // Return original segments on failure
        return $segments;

    } catch (ClientException $e) {
        $status = $e->getCode();
        $responseBody = (string) $e->getResponse()->getBody();
        
        // Try to extract the failed_generation from the error response
        $errorData = json_decode($responseBody, true);
        if (isset($errorData['failed_generation'])) {
            error_log("Failed generation content: " . $errorData['failed_generation']);
            
            // Try to fix and parse the failed generation
            $failedJson = $errorData['failed_generation'];
            
            // Common JSON fixes
            $failedJson = preg_replace('/,\s*}/', '}', $failedJson); // Remove trailing commas before }
            $failedJson = preg_replace('/,\s*\]/', ']', $failedJson); // Remove trailing commas before ]
            $failedJson = preg_replace('/}\s*,\s*}/', '}', $failedJson); // Fix double closing braces
            
            $parsed = json_decode($failedJson, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['segments'])) {
                $processedSegments = [];
                foreach ($parsed['segments'] as $segment) {
                    if (isset($segment['start']) && isset($segment['end']) && isset($segment['text'])) {
                        $processedSegments[] = [
                            'start' => (float)$segment['start'],
                            'end' => (float)$segment['end'],
                            'text' => trim($segment['text']),
                            'speaker' => $segment['speaker'] ?? 'UNKNOWN'
                        ];
                    }
                }
                
                if (!empty($processedSegments)) {
                    error_log("Successfully recovered from failed generation");
                    return $processedSegments;
                }
            }
        }
        
        error_log("Diarization failed ({$status}): {$responseBody}");
        // Return original segments on failure
        return $segments;
    }
}

/**
 * Analyze sentiment for call center conversations
 *
 * @param string $text
 * @param string $speaker
 * @param string $language
 * @return array
 */
function analyzeSentiment($text, $speaker = 'UNKNOWN', $language = 'en') {
    // More realistic sentiment analysis for call center contexts
    $text_lower = strtolower($text);
    
    // Define sentiment keywords for call center context
    $positive_keywords = ['thank', 'please', 'help', 'appreciate', 'great', 'good', 'excellent', 
                         'satisfied', 'resolved', 'solution', 'perfect', 'wonderful'];
    $negative_keywords = ['problem', 'issue', 'complaint', 'angry', 'frustrated', 'disappointed', 
                         'terrible', 'awful', 'horrible', 'unacceptable', 'cancel', 'refund'];
    $neutral_keywords = ['understand', 'information', 'account', 'policy', 'procedure', 'verify'];
    
    // Count keyword matches
    $positive_count = 0;
    $negative_count = 0;
    $neutral_count = 0;
    
    foreach ($positive_keywords as $keyword) {
        if (strpos($text_lower, $keyword) !== false) $positive_count++;
    }
    foreach ($negative_keywords as $keyword) {
        if (strpos($text_lower, $keyword) !== false) $negative_count++;
    }
    foreach ($neutral_keywords as $keyword) {
        if (strpos($text_lower, $keyword) !== false) $neutral_count++;
    }
    
    // Calculate sentiment score based on context
    if ($speaker === 'AGENT') {
        // Agents typically maintain professional tone, lean towards neutral/positive
        $base_score = 0.1;
    } else {
        // Clients can be more emotional
        $base_score = 0.0;
    }
    
    // Adjust score based on keyword counts
    $score = $base_score + ($positive_count * 0.3) - ($negative_count * 0.4) + ($neutral_count * 0.1);
    $score = max(-1, min(1, $score)); // Clamp between -1 and 1
    
    // Determine label
    if ($score > 0.2) {
        $label = $language === 'fr' ? 'positif' : 'positive';
    } elseif ($score < -0.2) {
        $label = $language === 'fr' ? 'négatif' : 'negative';
    } else {
        $label = $language === 'fr' ? 'neutre' : 'neutral';
    }
    
    return [
        'score' => round($score, 3),
        'label' => $label,
        'confidence' => round(min(1, abs($score) + 0.1), 3),
        'keywords' => [
            'positive' => $positive_count,
            'negative' => $negative_count,
            'neutral' => $neutral_count
        ],
        'context' => $speaker
    ];
}

/**
 * Orchestrate transcription, diarization, and sentiment analysis
 *
 * @param string $audioPath
 * @param string $language
 * @return array
 */
function processAudio($audioPath, $language = 'fr') {
    $transcription = transcribeAudio($audioPath, $language);
    if (!$transcription || empty($transcription['segments'])) {
        return ['status' => 'error', 'audio_path' => $audioPath, 'segments' => []];
    }

    // Perform diarization or other chat-based analysis
    $segments = diarizeWithGroq($transcription['segments']);

    // Analyze sentiment on each segment
    foreach ($segments as &$_segment) {
        $speaker = $_segment['speaker'] ?? 'UNKNOWN';
        $_segment['sentiment'] = analyzeSentiment($_segment['text'], $speaker, $language);
    }

    return [
        'status'     => 'success',
        'audio_path' => $audioPath,
        'segments'   => $segments,
    ];
}

// Entry point
$result = processAudio('test.mp3', 'fr');
$outputPath = 'output/' . time() . '.json';

// Create output directory if it doesn't exist
if (!file_exists('output')) {
    mkdir('output', 0755, true);
}

file_put_contents($outputPath, json_encode($result, JSON_PRETTY_PRINT));
echo "Results written to: " . $outputPath . "\n";
?>