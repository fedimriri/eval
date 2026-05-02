<?php
/**
 * Configuration file for the PHP project
 */

// Site configuration
$config = [
    'site_title' => 'QA Evaluation App',
    'site_description' => 'Call Center QA Evaluation System',
    'base_url' => '',  // Set this to your base URL in production
    'debug_mode' => true,
];

// GROQ API Configuration
$config['groq_api_key'] = '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';

// API Timeout Configuration
$config['groq_api_timeout'] = 1800; // 30 minutes timeout for API calls
$config['groq_api_connect_timeout'] = 120; // 2 minutes
$config['groq_api_retry_delay'] = 1000; // 1 second delay between retries
$config['groq_api_max_retries'] = 3;

// Audio Upload Configuration
$config['audio_upload_max_size'] = 20 * 1024 * 1024; // 20MB
$config['audio_allowed_types'] = ['audio/mp3', 'audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/x-m4a', 'audio/m4a'];

// Available Groq Models Configuration
$config['available_transcription_models'] = [
    'whisper-large-v3' => [
        'name' => 'Whisper Large v3',
        'description' => 'High-quality transcription model',
        'max_file_size' => '25 MB'
    ],
    'whisper-large-v3-turbo' => [
        'name' => 'Whisper Large v3 Turbo',
        'description' => 'Faster transcription with good quality',
        'max_file_size' => '25 MB'
    ],
    'distil-whisper-large-v3-en' => [
        'name' => 'Distil Whisper Large v3 (English)',
        'description' => 'Optimized for English transcription',
        'max_file_size' => '25 MB'
    ]
];

$config['available_evaluation_models'] = [
    // High-performance models
    'llama-3.3-70b-versatile' => [
        'name' => 'Llama 3.3 70B Versatile',
        'description' => 'Most capable model for complex evaluations',
        'context_window' => '128K tokens',
        'rpm' => 30,
        'rpd' => 1000,
        'tpm' => 12000,
        'tpd' => 100000
    ],
    'llama3-70b-8192' => [
        'name' => 'Llama 3 70B',
        'description' => 'High-quality evaluations with 8K context',
        'context_window' => '8K tokens',
        'rpm' => 30,
        'rpd' => 14400,
        'tpm' => 6000,
        'tpd' => 500000
    ],

    // Fast and efficient models
    'llama-3.1-8b-instant' => [
        'name' => 'Llama 3.1 8B Instant',
        'description' => 'Fast and efficient for standard evaluations',
        'context_window' => '128K tokens',
        'rpm' => 30,
        'rpd' => 14400,
        'tpm' => 6000,
        'tpd' => 500000
    ],
    'llama3-8b-8192' => [
        'name' => 'Llama 3 8B',
        'description' => 'Balanced speed and quality',
        'context_window' => '8K tokens',
        'rpm' => 30,
        'rpd' => 14400,
        'tpm' => 6000,
        'tpd' => 500000
    ],

    // Specialized models
    'gemma2-9b-it' => [
        'name' => 'Gemma 2 9B IT',
        'description' => 'Google\'s instruction-tuned model',
        'context_window' => '8K tokens',
        'rpm' => 30,
        'rpd' => 14400,
        'tpm' => 15000,
        'tpd' => 500000
    ],
    'mistral-saba-24b' => [
        'name' => 'Mistral Saba 24B',
        'description' => 'Mistral\'s advanced model for evaluations',
        'context_window' => '32K tokens',
        'rpm' => 30,
        'rpd' => 1000,
        'tpm' => 6000,
        'tpd' => 500000
    ],

    // Experimental and specialized models
    'deepseek-r1-distill-llama-70b' => [
        'name' => 'DeepSeek R1 Distill Llama 70B',
        'description' => 'DeepSeek\'s distilled reasoning model',
        'context_window' => '128K tokens',
        'rpm' => 30,
        'rpd' => 1000,
        'tpm' => 6000,
        'tpd' => 'unlimited'
    ],
    'qwen-qwq-32b' => [
        'name' => 'Qwen QwQ 32B',
        'description' => 'Qwen\'s question-answering model',
        'context_window' => '32K tokens',
        'rpm' => 30,
        'rpd' => 1000,
        'tpm' => 6000,
        'tpd' => 'unlimited'
    ],
    'allam-2-7b' => [
        'name' => 'Allam 2 7B',
        'description' => 'IBM\'s Allam model for evaluations',
        'context_window' => '8K tokens',
        'rpm' => 30,
        'rpd' => 7000,
        'tpm' => 6000,
        'tpd' => 'unlimited'
    ],

    // Llama 4 models (Beta)
    'meta-llama/llama-4-maverick-17b-128e-instruct' => [
        'name' => 'Llama 4 Maverick 17B',
        'description' => 'Meta\'s Llama 4 Maverick model (Beta)',
        'context_window' => '128K tokens',
        'rpm' => 30,
        'rpd' => 1000,
        'tpm' => 6000,
        'tpd' => 'unlimited'
    ],
    'meta-llama/llama-4-scout-17b-16e-instruct' => [
        'name' => 'Llama 4 Scout 17B',
        'description' => 'Meta\'s Llama 4 Scout model (Beta)',
        'context_window' => '16K tokens',
        'rpm' => 30,
        'rpd' => 1000,
        'tpm' => 30000,
        'tpd' => 'unlimited'
    ],

    // Guard models
    'llama-guard-3-8b' => [
        'name' => 'Llama Guard 3 8B',
        'description' => 'Safety and content moderation model',
        'context_window' => '8K tokens',
        'rpm' => 30,
        'rpd' => 14400,
        'tpm' => 15000,
        'tpd' => 500000
    ],
    'meta-llama/llama-guard-4-12b' => [
        'name' => 'Llama Guard 4 12B',
        'description' => 'Advanced safety and content moderation',
        'context_window' => '12K tokens',
        'rpm' => 30,
        'rpd' => 14400,
        'tpm' => 15000,
        'tpd' => 500000
    ],

    // Compound models (Beta)
    'compound-beta' => [
        'name' => 'Compound Beta',
        'description' => 'Experimental compound model',
        'context_window' => '128K tokens',
        'rpm' => 15,
        'rpd' => 200,
        'tpm' => 70000,
        'tpd' => 'unlimited'
    ],
    'compound-beta-mini' => [
        'name' => 'Compound Beta Mini',
        'description' => 'Lightweight compound model',
        'context_window' => '128K tokens',
        'rpm' => 15,
        'rpd' => 200,
        'tpm' => 70000,
        'tpd' => 'unlimited'
    ]
];

// Default Model Configuration
$config['transcription_model'] = 'whisper-large-v3';
$config['transcription_temperature'] = 0;

$config['evaluation_model'] = 'llama-3.1-8b-instant';
$config['evaluation_temperature'] = 0.3;

// File Storage Configuration
$config['audio_upload_dir'] = 'uploads/audio/';
$config['transcript_output_dir'] = 'uploads/transcripts/';


// Database configuration
$db_config = [
    'host' => 'mysql',  // Docker MySQL container name from docker-compose
    'username' => 'eval_user',
    'password' => 'eval_password',
    'database' => 'eval_db',
    'charset' => 'utf8mb4',
    'port' => 3306,
];

// Path configuration
$paths = [
    'root' => dirname(dirname(__DIR__)),
    'app' => dirname(__DIR__),
    'controllers' => dirname(__DIR__) . '/Controllers',
    'models' => dirname(__DIR__) . '/Models',
    'views' => dirname(__DIR__) . '/Views',
    'assets' => '/assets',
    'images' => '/assets/images',
    'css' => '/assets/css',
    'js' => '/assets/js',
    'vendors' => '/assets/vendors',
];

// Default controller and method
$routes = [
    'default_controller' => 'Home',
    'default_method' => 'index',
    'error_controller' => 'Error',
];

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting based on debug mode
if ($config['debug_mode']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', 'error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

