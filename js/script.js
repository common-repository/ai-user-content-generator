document.addEventListener("DOMContentLoaded", function() {
    handleGenerateButton();
});

function handleGenerateButton() {
    var generateButton = document.getElementById('generate-button');
    if (!generateButton) return;

    generateButton.addEventListener('click', function(e) {
        e.preventDefault();

        if (generateButton.disabled) return;

        var topicElement = document.getElementById('topic');
        if (!topicElement) {
            console.error('Error: Topic element not found!');
            return;
        }

        var topic = topicElement.value.trim();

        // Check if topic is empty and alert user if it is
        if (topic === "") {
            alert("You must type something!");
            return; // Exit function early
        }

        generateButton.disabled = true;

        var promptTextElement = document.getElementById('ucgaip-prompt-text');
        
        if (!promptTextElement) {
            console.error('Error: Prompt text element not found!');
            return;
        }

        var prompt_text = promptTextElement.value;
        var prompt = prompt_text + ' ' + topic;
        var loading = document.getElementById('loading');
        var result = document.getElementById('result');
        var resultC = document.getElementById('result-container');

        loading.style.display = 'block';
        result.style.display = 'none';
        resultC.style.display = 'none';

        var formData = new FormData();
        formData.append('action', 'ucgaip_generate_text');
        formData.append('prompt', prompt);
        formData.append('nonce', AiGeneratorAjax.nonce);

        fetch(AiGeneratorAjax.ajax_url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            if (data.success && data.data.choices && data.data.choices.length > 0) {
                result.value = data.data.choices[0].message.content;
                result.style.display = 'block';
                resultC.style.display = 'block';
            } else {
                result.value = 'An error occurred: ' + (data.data || 'Unknown error');
                result.style.display = 'block';
                resultC.style.display = 'block';
            }
            generateButton.disabled = false;
        })
        .catch(error => {
            loading.style.display = 'none';
            result.value = 'An error occurred: ' + error.message;
            result.style.display = 'block';
            resultC.style.display = 'block';
            generateButton.disabled = false;
        });
    });
}

function copyToClipboard() {
    const textarea = document.getElementById('result');
    if (textarea) {
        textarea.select(); // Selects the content of the textarea
        document.execCommand('copy'); // Copies the selected content to clipboard

        // Optionally, show a message to user
        alert('Content copied to clipboard!');
    }
}
