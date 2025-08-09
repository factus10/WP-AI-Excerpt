# WP AI Excerpt

A WordPress plugin that automatically generates compelling excerpts for your posts and pages using OpenAI's powerful language models.

**Version:** 1.0  
**Author:** David ([@factus10](https://github.com/factus10))  
**License:** GPLv2 or later  

## Description

WP AI Excerpt seamlessly integrates AI-powered excerpt generation into your WordPress workflow. Save time and improve your content's discoverability with automatically generated summaries that capture the essence of your posts.

## Features

- **AI-Powered Generation**: Uses OpenAI's GPT models (GPT-3.5 Turbo, GPT-4, or GPT-4 Turbo) to create engaging excerpts
- **Native WordPress Integration**: Enhances the built-in excerpt functionality in both Classic and Block editors
- **Flexible Length Control**: Set a system-wide default (25-500 words) with per-post customization
- **Smart Integration**: Intercepts the "Add an excerpt..." link to provide an enhanced experience
- **Clean Output**: Automatically removes quotes from generated excerpts
- **Multiple Access Points**: 
  - Enhanced excerpt dialog in the sidebar
  - Dedicated meta box in the post editor
  - Direct integration with Classic Editor's excerpt box

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- OpenAI API key

## Installation

1. Download or clone this repository to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/factus10/WP-AI-Excerpt.git
   ```

2. Activate the plugin through the WordPress admin panel

3. Navigate to **Settings > AI Excerpt** to configure:
   - Your OpenAI API key
   - Default excerpt length
   - Preferred AI model

## Usage

### Block Editor (Gutenberg)

1. Click "Add an excerpt..." in the post sidebar
2. The enhanced dialog provides two options:
   - Write an excerpt manually
   - Generate one with AI
3. To use AI generation:
   - Set your desired word count
   - Click "Generate AI Excerpt"
   - Review and edit the generated text
   - Click "Save" to apply

### Classic Editor

1. Look for the "Generate AI Excerpt" button in the Excerpt meta box
2. Click to open the generation dialog
3. Set your desired word count and generate

### Alternative Method

A dedicated "Generate AI Excerpt" meta box is also available below the main editor for quick access.

## Configuration

### Plugin Settings

Access the settings at **Settings > AI Excerpt**:

- **OpenAI API Key**: Your API key from [OpenAI Platform](https://platform.openai.com/)
- **Default Excerpt Length**: Set between 25-500 words
- **AI Model**: Choose between GPT-3.5 Turbo, GPT-4, or GPT-4 Turbo

### Obtaining an OpenAI API Key

1. Sign up at [OpenAI Platform](https://platform.openai.com/)
2. Navigate to API keys in your account settings
3. Create a new API key
4. Copy and paste it into the plugin settings

## Screenshots

1. **Enhanced Excerpt Dialog** - The AI-powered excerpt interface that replaces the default WordPress dialog
2. **Settings Page** - Configure your API key and default preferences
3. **Meta Box Interface** - Alternative excerpt generation in the post editor

## Frequently Asked Questions

**Q: Does this work with custom post types?**  
A: Yes! The plugin works with any post type that supports excerpts.

**Q: Can I edit the AI-generated excerpts?**  
A: Absolutely. The generated text appears in an editable field before saving.

**Q: What happens to my existing excerpts?**  
A: Existing excerpts are preserved. The AI generation is optional and only replaces excerpts when you explicitly use it.

**Q: Is my content sent to OpenAI?**  
A: Yes, the post content is sent to OpenAI's API for processing. Only the first 3000 characters are sent to stay within token limits.

## Changelog

### Version 1.0
- Initial release
- AI-powered excerpt generation using OpenAI
- Integration with WordPress excerpt functionality
- Support for both Classic and Block editors
- Configurable excerpt length (25-500 words)
- Multiple AI model options
- Clean uninstall functionality

## Support

For issues, feature requests, or contributions, please visit the [GitHub repository](https://github.com/factus10/WP-AI-Excerpt).

## Privacy & Security

- API keys are stored securely in the WordPress database
- Content is transmitted securely to OpenAI via HTTPS
- No content is stored outside of your WordPress installation
- The plugin can be completely removed with no residual data

## Credits

Created by David ([@factus10](https://github.com/factus10))

This plugin uses the [OpenAI API](https://openai.com/) for excerpt generation.