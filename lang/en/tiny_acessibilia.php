<?php
// This file is part of Moodle - http://moodle.org/
// GNU GPL v3 or later.

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Acessibilia';

// UI (button/menubar/dialog)
$string['buttontitle'] = 'Acessibilia: generate image description';
$string['menulabel'] = 'Acessibilia';
$string['dialogtitle'] = 'Acessibilia — image description';
$string['selectfile'] = 'Select a file or drag it here';
$string['generate'] = 'Generate description';
$string['regenerate'] = 'Generate again';
$string['insert'] = 'Insert';
$string['cancel'] = 'Cancel';
$string['altlabel'] = 'Image alt text';
$string['decorative'] = 'Decorative image (empty alt)';
$string['error_nofile'] = 'Please select an image to continue.';
$string['error_unsupported'] = 'Unsupported file or operation unavailable.';
$string['error_upload'] = 'Failed to upload the image. Please try again.';
$string['error_ia'] = 'Could not automatically generate a description.';
$string['notice_privacy'] = 'The selected image will be sent to an external service to generate the description.';

// Settings (admin)
$string['setting:enabled'] = 'Enable Acessibilia';
$string['setting:enabled_desc'] = 'Enables the Acessibilia plugin in the TinyMCE editor.';
$string['setting:apiurl'] = 'API URL';
$string['setting:apiurl_desc'] = 'Endpoint of the AI service (Google AI Studio).';
$string['setting:model'] = 'Model';
$string['setting:model_desc'] = 'Name of the model to use (e.g., gemini-2.0-flash).';
$string['setting:apikey'] = 'API key';
$string['setting:apikey_desc'] = 'Private AI service key (stored server-side; never exposed to the browser).';
$string['setting:prompt'] = 'Default prompt';
$string['setting:prompt_desc'] = 'Base prompt in Portuguese to guide the description generation.';
$string['setting:timeout'] = 'Timeout (ms)';
$string['setting:timeout_desc'] = 'Maximum wait time for the AI response.';
$string['setting:forcealt'] = 'Require non-empty alt';
$string['setting:forcealt_desc'] = 'Requires the alt text to be filled unless the image is marked decorative.';
$string['setting:allowdecorative'] = 'Allow decorative image';
$string['setting:allowdecorative_desc'] = 'Allows inserting images with empty alt (decorative).';
$string['setting:logs'] = 'Enable server logs';
$string['setting:logs_desc'] = 'Sends usage metadata (no content) to the server for diagnostics.';

// Capability
$string['acessibilia:use'] = 'Use the Acessibilia plugin';

// Privacy (null provider)
$string['privacy:metadata'] = 'The Acessibilia plugin does not store any personal data.';
