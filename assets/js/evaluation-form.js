/**
 * Evaluation Form JavaScript
 * Handles the interactive behavior of the evaluation form
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    if (typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Get all notation radio buttons
    const notationRadios = document.querySelectorAll('.notation-radio');

    // Add event listeners to all notation radios
    notationRadios.forEach(radio => {
        // Add change event listener
        radio.addEventListener('change', handleNotationChange);

        // Add keyboard event listener for better accessibility
        radio.addEventListener('keydown', handleKeyDown);
    });

    // Handle form submission
    const forms = document.querySelectorAll('form.forms-sample');
    forms.forEach(form => {
        form.addEventListener('submit', validateForm);
    });

    // Add keyboard shortcuts for the entire document
    document.addEventListener('keydown', handleGlobalKeyDown);

    // Initialize the form state
    initializeFormState();

    // Initialize AI functionality
    initializeAIFunctionality();
});

/**
 * Initialize the form state
 * Checks for any pre-selected notations and applies the appropriate styles
 */
function initializeFormState() {
    // Get all checked notation radios
    const checkedRadios = document.querySelectorAll('.notation-radio:checked');

    // Apply styles to any pre-selected notations
    checkedRadios.forEach(radio => {
        // Trigger the change event to apply styles
        const event = new Event('change');
        radio.dispatchEvent(event);
    });
}

/**
 * Handle notation radio change
 * @param {Event} event - The change event
 */
function handleNotationChange(event) {
    const radio = event.target;
    const notationValue = radio.value;

    // Remove any error styling from the parent container
    const container = radio.closest('.notation-badges');
    if (container) {
        container.parentElement.classList.remove('notation-error');
    }

    // For "Situation inacceptable" (SI) notation, just mark it as confirmed without showing a dialog
    if (notationValue === 'SI') {
        radio.setAttribute('data-confirmed', 'true');
    }

    // For "Point critique" (PC) notation, just mark it as confirmed without showing a dialog
    if (notationValue === 'PC') {
        radio.setAttribute('data-confirmed', 'true');
    }
}

/**
 * Handle keyboard navigation for radio buttons
 * @param {KeyboardEvent} event - The keydown event
 */
function handleKeyDown(event) {
    // Handle space or enter key to select the radio
    if (event.key === ' ' || event.key === 'Enter') {
        event.preventDefault();
        event.target.checked = true;

        // Trigger change event
        const changeEvent = new Event('change');
        event.target.dispatchEvent(changeEvent);
    }
}

/**
 * Handle global keyboard shortcuts
 * @param {KeyboardEvent} event - The keydown event
 */
function handleGlobalKeyDown(event) {
    // Only process if we're not in a text input or textarea
    if (event.target.tagName === 'INPUT' && event.target.type === 'text' ||
        event.target.tagName === 'TEXTAREA') {
        return;
    }

    // Get the currently focused element or its closest row
    let currentRow = null;

    if (document.activeElement) {
        // If the active element is a radio button, get its row
        if (document.activeElement.classList.contains('notation-radio')) {
            currentRow = document.activeElement.closest('tr');
        }
        // If the active element is a textarea in a row, get its row
        else if (document.activeElement.tagName === 'TEXTAREA' && document.activeElement.closest('tr')) {
            currentRow = document.activeElement.closest('tr');
        }
    }

    // If no row is found, get the first row
    if (!currentRow) {
        const rows = document.querySelectorAll('tr');
        for (const row of rows) {
            if (row.querySelector('.notation-radio')) {
                currentRow = row;
                break;
            }
        }
    }

    // If we have a row, process the keyboard shortcut
    if (currentRow) {
        let radioToSelect = null;

        // Number keys 1-4 for selecting notations
        switch (event.key) {
            case '1': // C - Conforme
                radioToSelect = currentRow.querySelector('.notation-radio[value="C"]');
                break;
            case '2': // NC - Non conforme
                radioToSelect = currentRow.querySelector('.notation-radio[value="NC"]');
                break;
            case '3': // PC - Point critique
                radioToSelect = currentRow.querySelector('.notation-radio[value="PC"]');
                break;
            case '4': // SI - Situation inacceptable
                radioToSelect = currentRow.querySelector('.notation-radio[value="SI"]');
                break;
            case 'ArrowDown':
                // Move to the next row
                navigateToRow(currentRow, 'next');
                return;
            case 'ArrowUp':
                // Move to the previous row
                navigateToRow(currentRow, 'prev');
                return;
        }

        // If we found a radio to select, select it
        if (radioToSelect) {
            event.preventDefault();
            radioToSelect.checked = true;
            radioToSelect.focus();

            // Trigger change event
            const changeEvent = new Event('change');
            radioToSelect.dispatchEvent(changeEvent);

            // Move focus to the comment textarea in the same row
            const commentTextarea = currentRow.querySelector('textarea');
            if (commentTextarea) {
                commentTextarea.focus();
            }
        }
    }
}

/**
 * Navigate to the next or previous row
 * @param {HTMLElement} currentRow - The current row
 * @param {string} direction - The direction to navigate ('next' or 'prev')
 */
function navigateToRow(currentRow, direction) {
    // Get all rows with notation radios
    const rows = Array.from(document.querySelectorAll('tr')).filter(row =>
        row.querySelector('.notation-radio')
    );

    // Find the index of the current row
    const currentIndex = rows.indexOf(currentRow);

    if (currentIndex === -1) {
        return;
    }

    // Calculate the target index
    let targetIndex;
    if (direction === 'next') {
        targetIndex = (currentIndex + 1) % rows.length;
    } else {
        targetIndex = (currentIndex - 1 + rows.length) % rows.length;
    }

    // Get the target row
    const targetRow = rows[targetIndex];

    // Focus on the first radio in the target row
    const firstRadio = targetRow.querySelector('.notation-radio');
    if (firstRadio) {
        firstRadio.focus();

        // Scroll the row into view if needed
        targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

/**
 * Validate the form before submission
 * @param {Event} event - The submit event
 */



function validateForm(event) {
    // Get all subcriteria rows
    const rows = document.querySelectorAll('tr');
    let isValid = true;
    let firstInvalidElement = null;

    // Check each row for a selected notation
    rows.forEach(row => {
        // Find all notation radios in this row
        const radios = row.querySelectorAll('.notation-radio');

        // Skip if there are no notation radios in this row
        if (radios.length === 0) {
            return;
        }

        // Check if any radio is selected
        const isSelected = Array.from(radios).some(radio => radio.checked);

        if (!isSelected) {
            isValid = false;

            // Add error class to the container
            const container = row.querySelector('.notation-badges');
            if (container) {
                container.parentElement.classList.add('notation-error');

                // Store the first invalid element for focusing
                if (!firstInvalidElement) {
                    firstInvalidElement = radios[0];
                }
            }
        }
    });

    // If the form is not valid, prevent submission and focus the first invalid element
    if (!isValid) {
        event.preventDefault();

        // Focus the first invalid element immediately
        if (firstInvalidElement) {
            firstInvalidElement.focus();

            // Scroll to the first invalid element
            const invalidRow = firstInvalidElement.closest('tr');
            if (invalidRow) {
                invalidRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }
}

/**
 * Initialize AI functionality
 */
function initializeAIFunctionality() {
    const transcribeBtn = document.getElementById('transcribe-btn');
    const aiEvaluateBtn = document.getElementById('ai-evaluate-btn');
    const transcribeAndEvaluateBtn = document.getElementById('transcribe-and-evaluate-btn');
    const clearAiBtn = document.getElementById('clear-ai-btn');

    if (transcribeBtn) {
        transcribeBtn.addEventListener('click', handleTranscribe);
    }

    if (aiEvaluateBtn) {
        aiEvaluateBtn.addEventListener('click', handleAIEvaluate);
    }

    if (transcribeAndEvaluateBtn) {
        transcribeAndEvaluateBtn.addEventListener('click', handleTranscribeAndEvaluate);
    }

    if (clearAiBtn) {
        clearAiBtn.addEventListener('click', handleClearAI);
    }
}

/**
 * Handle transcribe audio functionality
 */
async function handleTranscribe() {
    const audioFile = document.getElementById('audio-file').files[0];
    const language = document.getElementById('language-select').value;    if (!audioFile) {
        showError('Please select an audio file first.');
        return;
    }

    try {
        showStatus('Transcribing audio...', 'info');

        const formData = new FormData();
        formData.append('audio', audioFile);
        formData.append('language', language);
        const transcriptionModel = document.getElementById('transcription-model-select')?.value;
        if (transcriptionModel) formData.append('transcriptionModel', transcriptionModel);

        const response = await fetch('/ai_eval/transcribe', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            // Store transcription data globally for audio playback
            window.transcriptionData = result.data;

            // Display audio player and enhanced segments
            displayAudioPlayer(result.data);
            displayTranscriptionSegments(result.data.transcription);

            // Also fill the textarea for backward compatibility
            if (result.data.transcription && result.data.transcription.segments) {
                const transcriptText = result.data.transcription.segments.map(segment =>
                    `${segment.speaker || 'SPEAKER'}: ${segment.text}`
                ).join('\n');
                document.getElementById('transcript-text').value = transcriptText;
            }

            showStatus('Audio transcribed successfully!', 'success');
            setTimeout(hideStatus, 3000);
        } else {
            throw new Error(result.error || 'Transcription failed');
        }
    } catch (error) {
        console.error('Transcription error:', error);
        showError('Transcription failed: ' + error.message);
    }
}

/**
 * Handle transcribe and evaluate functionality (combined)
 */
async function handleTranscribeAndEvaluate() {
    const audioFile = document.getElementById('audio-file').files[0];
    const language = document.getElementById('language-select').value;
    const templateId = document.getElementById('template-id').value;
    const evaluationModel = document.getElementById('evaluation-model-select')?.value;
    const transcriptionModel = document.getElementById('transcription-model-select')?.value;


    if (!audioFile) {
        showError('Please select an audio file first.');
        return;
    }

    try {
        showStatus('Transcribing audio and generating AI evaluation...', 'info');

        const formData = new FormData();
        formData.append('audio', audioFile);
        formData.append('language', language);
        formData.append('templateId', templateId);
        if (evaluationModel) formData.append('evaluationModel', evaluationModel);
        if (transcriptionModel) formData.append('transcriptionModel', transcriptionModel);

        const response = await fetch('/ai_eval/transcribe_and_evaluate', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            // Extract transcript and evaluation from the combined result
            const transcriptData = result.data.results.transcript;
            const evaluationData = result.data.results.evaluation;

            // Store transcription data globally for audio playback
            window.transcriptionData = result.data;

            // Display audio player and enhanced segments
            displayAudioPlayer(result.data);
            displayTranscriptionSegments(transcriptData);

            // Fill transcript text area for backward compatibility
            if (transcriptData && transcriptData.segments) {
                const transcriptText = transcriptData.segments.map(segment =>
                    `${segment.speaker}: ${segment.text}`
                ).join('\n');
                document.getElementById('transcript-text').value = transcriptText;
            }

            // Apply evaluation results to form
            if (evaluationData) {
                applyAIEvaluationToForm(evaluationData);
            }

            showStatus('Audio transcribed and evaluated successfully!', 'success');
            setTimeout(hideStatus, 3000);
        } else {
            throw new Error(result.error || 'Transcription and evaluation failed');
        }
    } catch (error) {
        console.error('Transcribe and evaluate error:', error);
        showError('Transcription and evaluation failed: ' + error.message);
    }
}

/**
 * Handle AI evaluation functionality
 */
async function handleAIEvaluate() {
    const transcript = document.getElementById('transcript-text').value.trim();
    const templateId = document.getElementById('template-id').value;
    const evaluationModel = document.getElementById('evaluation-model-select')?.value;

    if (!transcript) {
        showError('Please provide a transcript first (either upload audio or enter text manually).');
        return;
    }

    try {
        showStatus('AI is evaluating the transcript...', 'info');

        const response = await fetch('/ai_eval/evaluate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                transcript: transcript,
                templateId: templateId,
                evaluationModel:evaluationModel
            })
        });

        const result = await response.json();

        if (result.status === 'success') {
            applyAIEvaluationToForm(result.data.evaluation);
            showStatus('AI evaluation completed successfully!', 'success');
            setTimeout(hideStatus, 3000);
        } else {
            throw new Error(result.error || 'AI evaluation failed');
        }
    } catch (error) {
        console.error('AI evaluation error:', error);
        showError('AI evaluation failed: ' + error.message);
    }
}

/**
 * Apply AI evaluation results to the form
 */
function applyAIEvaluationToForm(evaluationResults) {
    evaluationResults.forEach(result => {
        const subcriteriaId = result.subcriteria_id;
        const notation = result.notation;
        const comments = result.comments;

        // Find and select the appropriate radio button using the correct ID format
        // The ID format is: notation_{notation_value}_{subcriteria_id}
        const radioButton = document.querySelector(`input[id="notation_${notation.toLowerCase()}_${subcriteriaId}"]`);

        if (radioButton) {
            radioButton.checked = true;

            // Trigger change event to apply any styling
            const changeEvent = new Event('change');
            radioButton.dispatchEvent(changeEvent);
        } else {
            console.warn(`Radio button not found for subcriteria ${subcriteriaId} with notation ${notation}`);
        }

        // Fill in the comment field
        const commentField = document.querySelector(`textarea[name="comment_${subcriteriaId}"]`);
        if (commentField && comments) {
            commentField.value = comments;
        }
    });
}

/**
 * Clear AI results
 */
function handleClearAI() {
    // Clear transcript
    document.getElementById('transcript-text').value = '';

    // Clear all radio buttons
    const radioButtons = document.querySelectorAll('.notation-radio');
    radioButtons.forEach(radio => {
        radio.checked = false;
    });

    // Clear all comment fields
    const commentFields = document.querySelectorAll('textarea[name^="comment_"]');
    commentFields.forEach(field => {
        field.value = '';
    });

    // Clear audio file input
    document.getElementById('audio-file').value = '';

    // Clear audio player and segments display
    clearAudioPlayer();
    clearTranscriptionSegments();

    // Clear stored transcription data
    window.transcriptionData = null;

    hideStatus();
}

/**
 * Show status message
 */
function showStatus(message, type = 'info') {
    const statusDiv = document.getElementById('ai-status');
    const statusText = document.getElementById('ai-status-text');
    const alertDiv = statusDiv.querySelector('.alert');

    statusText.textContent = message;

    // Remove existing alert classes
    alertDiv.className = 'alert';

    // Add appropriate class based on type
    switch (type) {
        case 'success':
            alertDiv.classList.add('alert-success');
            alertDiv.innerHTML = '<i class="mdi mdi-check-circle"></i> <span id="ai-status-text">' + message + '</span>';
            break;
        case 'error':
            alertDiv.classList.add('alert-danger');
            alertDiv.innerHTML = '<i class="mdi mdi-alert-circle"></i> <span id="ai-status-text">' + message + '</span>';
            break;
        default:
            alertDiv.classList.add('alert-info');
            alertDiv.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> <span id="ai-status-text">' + message + '</span>';
    }

    statusDiv.style.display = 'block';
}

/**
 * Show error message
 */
function showError(message) {
    showStatus(message, 'error');
    setTimeout(hideStatus, 5000);
}

/**
 * Hide status message
 */
function hideStatus() {
    const statusDiv = document.getElementById('ai-status');
    statusDiv.style.display = 'none';
}

/**
 * Display audio player for uploaded audio
 */
function displayAudioPlayer(data) {
    const audioPlayerContainer = document.getElementById('audio-player-container');
    if (!audioPlayerContainer) {
        console.error('Audio player container not found');
        return;
    }

    // Create audio URL from uploaded file
    const audioFile = document.getElementById('audio-file').files[0];
    if (!audioFile) {
        console.error('No audio file found');
        return;
    }

    const audioUrl = URL.createObjectURL(audioFile);

    audioPlayerContainer.innerHTML = `
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="mdi mdi-volume-high"></i> Audio Player
                </h6>
            </div>
            <div class="card-body">
                <audio id="audio-player" controls class="w-100" preload="metadata">
                    <source src="${audioUrl}" type="${audioFile.type}">
                    Your browser does not support the audio element.
                </audio>
                <div class="mt-2">
                    <small class="text-muted">
                        <strong>File:</strong> ${data.file_info?.name || audioFile.name}
                        <strong>Size:</strong> ${formatFileSize(data.file_info?.size || audioFile.size)}
                    </small>
                </div>
            </div>
        </div>
    `;

    audioPlayerContainer.style.display = 'block';

    // Add event listener for real-time segment highlighting
    setTimeout(() => {
        const audioPlayer = document.getElementById('audio-player');
        if (audioPlayer) {
            audioPlayer.addEventListener('timeupdate', function() {
                highlightCurrentSegment(this.currentTime);
            });
        }
    }, 100);
}

/**
 * Display transcription segments with clickable timestamps
 */
function displayTranscriptionSegments(transcriptionData) {
    const segmentsContainer = document.getElementById('transcription-segments-container');
    if (!segmentsContainer) {
        console.error('Transcription segments container not found');
        return;
    }
    if (!transcriptionData || !transcriptionData.segments) {
        console.error('No transcription data or segments found');
        return;
    }

    const segments = transcriptionData.segments;

    let segmentsHtml = `
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="mdi mdi-text-to-speech"></i> Transcription Segments
                    <small class="text-muted">(Click on timestamps to jump to audio position)</small>
                </h6>
            </div>
            <div class="card-body">
                <div class="segments-list">
    `;

    segments.forEach((segment) => {
        const startTime = formatTime(segment.start);
        const endTime = formatTime(segment.end);
        const speaker = segment.speaker || 'SPEAKER';
        const speakerClass = speaker === 'AGENT' ? 'badge-primary' : 'badge-secondary';

        segmentsHtml += `
            <div class="segment-item mb-3 p-3 border rounded" data-start="${segment.start}" data-end="${segment.end}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge ${speakerClass}">${speaker}</span>
                    <button class="btn btn-sm btn-outline-info timestamp-btn"
                            onclick="seekToTime(${segment.start})"
                            title="Click to jump to this time in audio">
                        <i class="mdi mdi-clock-outline"></i> ${startTime} - ${endTime}
                    </button>
                </div>
                <div class="segment-text">
                    ${segment.text}
                </div>
            </div>
        `;
    });

    segmentsHtml += `
                </div>
            </div>
        </div>
    `;

    segmentsContainer.innerHTML = segmentsHtml;
    segmentsContainer.style.display = 'block';
}

/**
 * Clear audio player
 */
function clearAudioPlayer() {
    const audioPlayerContainer = document.getElementById('audio-player-container');
    if (audioPlayerContainer) {
        audioPlayerContainer.innerHTML = '';
        audioPlayerContainer.style.display = 'none';
    }
}

/**
 * Clear transcription segments
 */
function clearTranscriptionSegments() {
    const segmentsContainer = document.getElementById('transcription-segments-container');
    if (segmentsContainer) {
        segmentsContainer.innerHTML = '';
        segmentsContainer.style.display = 'none';
    }
}

/**
 * Seek to specific time in audio player
 */
function seekToTime(timeInSeconds) {
    const audioPlayer = document.getElementById('audio-player');
    if (audioPlayer) {
        audioPlayer.currentTime = timeInSeconds;
        audioPlayer.play().catch(e => {
            console.log('Auto-play prevented by browser:', e);
        });

        // Highlight the current segment
        highlightCurrentSegment(timeInSeconds);
    }
}

/**
 * Highlight current segment based on audio time
 */
function highlightCurrentSegment(currentTime) {
    const segments = document.querySelectorAll('.segment-item');
    segments.forEach(segment => {
        const start = parseFloat(segment.dataset.start);
        const end = parseFloat(segment.dataset.end);

        if (currentTime >= start && currentTime <= end) {
            segment.classList.add('bg-light', 'border-primary');
        } else {
            segment.classList.remove('bg-light', 'border-primary');
        }
    });
}

/**
 * Format time in seconds to MM:SS format
 */
function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
}

/**
 * Format file size in human readable format
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}