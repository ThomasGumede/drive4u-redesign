<?php
// Copy this file to mail.php and edit the values. Never commit mail.php to public repos.
return [
    'smtp_host' => 'smtp.driver4u.co.za', // Confirm with your email hosting provider
    'smtp_port' => 587,                   // Common STARTTLS port; confirm with provider
    'smtp_username' => 'noreply@driver4u.co.za',
    'smtp_password' => 'ADD_YOUR_SMTP_PASSWORD_HERE',
    'smtp_encryption' => 'tls',           // tls or ssl
    'from_email' => 'noreply@driver4u.co.za',
    'from_name' => 'Drive4U Website',
    'to_email' => 'info@drive4u.co.za',
    'to_name' => 'Drive4U Enquiries',
    'recaptcha_secret' => 'REPLACE_WITH_RECAPTCHA_V3_SECRET_KEY',
    'recaptcha_threshold' => 0.5,
    'recaptcha_hostname' => 'driver4u.co.za',
];
