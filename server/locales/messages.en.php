<?php

return [
	'http.too_many_requests' => 'Too many requests made for the current IP address. See response headers for more information.',
	'http.invalid_origin' => 'Access to the service is denied because the request\'s origin domain is not allowed according to the security policy.',
	'link.unreachable_url' => 'The specified URL is unreachable.',
	'link.disabled' => 'The specified shortcut link has been disabled by its owner or by an administrator.',
	'link.reported' => 'The specified shortcut link has been reported by one or more users. This link must be verified by an administrator before it can be reached again.',
	'link.password.missing' => 'The specified shortcut link is protected by a password. Please provide it in the "Authorization" HTTP header.',
	'link.password.invalid' => 'The password you provided for the shortcut link is invalid. Please check it and try again.',
	'report.duplicated' => 'You have already reported this shortcut link, you cannot report it again.',
	'report.trusted_link' => 'You cannot report a trusted link. If you think this link is malicious, please contact an administrator.',
	'report.maximum_reached' => 'The maximum number of reports for this link has been reached (%max%). It cannot be reported again.',
	'slug.already_used' => 'The custom slug you chose is already used by another link. Please choose another one.',
	'api_key.missing' => 'The API key is missing. Please provide it in the "Authorization" HTTP header.',
	'api_key.invalid' => 'The provided API key is invalid. Please check it and try again.'
];