<?php 

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<div id="text-generation-tool">
    <textarea 
        id="topic" 
        class="custom-textarea" 
        placeholder="<?php echo esc_attr(get_option('ucgaip_placeholder_text', 'Enter your topic here...')); ?>"
        style="border-radius: <?php echo esc_attr(get_option('ucgaip_border_radius', '5px')); ?> !important;"
        rows="4" 
        cols="50"></textarea>
    <button 
        id="generate-button" 
        class="custom-button"
        style="background-color: <?php echo esc_attr(get_option('ucgaip_button_color', '#3498db')); ?> !important; border-radius: <?php echo esc_attr(get_option('ucgaip_border_radius', '5px')); ?> !important;"
        >
        <?php echo esc_html(get_option('ucgaip_button_text', 'Generate!')); ?>
    </button>
    <div id="result-container" style="display: none;">
        <div class="result-wrapper">
            <div class="result-content">
                <textarea 
                    id="result" 
                    readonly 
                    class="custom-textarea" 
                    style="border-radius: <?php echo esc_attr(get_option('ucgaip_border_radius', '5px')); ?> !important;"
                    rows="6" 
                    cols="50"></textarea>
            </div>
            <div class="copy-button-container">
                <button 
                    id="copy-button" 
                    class="custom-button" 
                    onclick="copyToClipboard()"        
                    style="background-color: <?php echo esc_attr(get_option('ucgaip_button_color', '#3498db')); ?> !important; border-radius: <?php echo esc_attr(get_option('ucgaip_border_radius', '5px')); ?> !important;"
                >
                    Copy
                </button>
            </div>
        </div>
    </div>
    <div id="loading" class="loader" style="display: none;"></div>

    <input type="hidden" id="ucgaip-prompt-text" value="<?php echo esc_attr(get_option('ucgaip_prompt')); ?>">
</div>

<!-- <style>
#text-generation-tool .custom-button {
    background-color: <?php echo esc_attr(get_option('ucgaip_button_color', '#3498db')); ?> !important;
    border-radius: <?php echo esc_attr(get_option('ucgaip_border_radius', '5px')); ?> !important;
}
#text-generation-tool .custom-textarea {
    border-radius: <?php echo esc_attr(get_option('ucgaip_border_radius', '5px')); ?> !important;
}

    /* CSS for the loader */
    .loader {
        display: block;
        margin: 50px auto;
        border: 16px solid #f3f3f3; /* Light grey */
        border-top: 16px solid #b9b9b9; /* Blue */
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style> -->
