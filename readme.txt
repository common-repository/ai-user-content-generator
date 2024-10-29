=== AI user content generator ===
Contributors: lidzen
Tags: content, generator, AI, OpenAI, GPT-3.5
Requires at least: 5.0
Tested up to: 6.5.4
Stable tag: 1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.6

Let your website users generate content with OpenAI.

== Description ==

AI Content Generator leverages the power of OpenAI to produce content based on prompts provided by your users. Simply embed the generator using the provided shortcode, and watch as AI produces content in real-time for your users.

Features:

- Integration with OpenAI API.
- Customizable button styles and colors.
- Optional storage of user inputs.

For the pro version with additional features, please visit [here](https://elysehbiagini.com/aipro).

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ucgaip-content-generator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the AI Generator->Settings screen to configure the plugin.
4. Place the shortcode `[ucgaip-generator]` in your posts or pages where you'd like the AI Content Generator to appear.


== Third-Party Services ==

OpenAI API

== Description ==

This plugin relies on the OpenAI API, a third-party service, to provide its core functionality. Specifically, the plugin sends user-generated input to OpenAI's servers, where it is processed using their models to generate responses. The endpoint used by this plugin is:

- API Endpoint: https://api.openai.com/v1/chat/completions

== When and Why the Plugin Uses OpenAI’s Service ==

The plugin interacts with the OpenAI API whenever it needs to generate text-based responses based on user input. This is a fundamental aspect of the plugin’s operation, and it relies on OpenAI’s models to provide intelligent, context-aware responses.

Scenarios Where the API is Utilized:
- Chatbots and Virtual Assistants: When a user types a message, the plugin sends that message to the OpenAI API to generate a suitable reply.
- Content Creation Tools: The plugin leverages OpenAI’s language models to produce text content, such as articles, summaries, or conversational dialogue.
- Language Processing Applications: The plugin uses the API to understand and process natural language inputs, enabling sophisticated text analysis and manipulation.

== Understanding OpenAI’s Role and Responsibilities ==

By using this plugin, you are indirectly interacting with OpenAI’s services. It’s important to recognize that the data sent to the API (including user input) is processed by OpenAI’s infrastructure. OpenAI handles this data according to their privacy policies and terms of service.

== Important Links ==

- OpenAI API Documentation: For detailed information on how the OpenAI API works, refer to their official documentation: https://platform.openai.com/docs/api-reference

- Terms of Use: Before using this plugin, please review OpenAI’s terms of use to understand your legal obligations and rights: https://openai.com/policies/terms-of-use

- Privacy Policy: It’s also essential to understand how OpenAI handles data, including any data you might send via this plugin. Their privacy policy can be found here: https://openai.com/policies/privacy-policy

== Your Responsibilities ==

By using this plugin, you agree to comply with OpenAI’s terms and policies. This includes ensuring that the data you send is appropriate and complies with their guidelines. If you have any concerns about data privacy or usage, it’s recommended that you review these policies carefully and assess whether this service aligns with your requirements.

== Usage ==
To use the OpenAI API within this plugin, follow these steps:

- Get an API Key

1- Sign up for an account at OpenAI.
2- Generate an API key from your OpenAI account dashboard.

- Enter the API Key

1- Go to your WordPress dashboard.
2- Navigate to AI Generator -> Settings.
3- Enter your OpenAI API key in the OpenAi API Key field and save the settings.

- Configuration

Customize the plugin settings as needed, including button styles, colors, and whether to store user inputs.


- Additional Resources

For more details on using the OpenAI API, refer to the official OpenAI API documentation (https://beta.openai.com/docs/).

- Licensing

Ensure that you comply with OpenAI's usage policies and licensing terms when integrating their services into your WordPress plugin.


== Frequently Asked Questions ==

= Does this plugin store the content generated? =

By default, the plugin doesn't store the generated content. However, you can enable this option in the settings if needed.

= Do I need an API key? =

Yes, you will need an API key from OpenAI to use this plugin.

== Screenshots ==

1. screenshot-1 Example of the AI Content Generator in action.
2. screenshot-2 Example of the AI Content Generator in action.
3. screenshot-3 User inputs page.
4. screenshot-4 Settings page for customization.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
* Initial release.


