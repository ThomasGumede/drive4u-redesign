# Drive4U — Redesigned Website

A responsive multi-page HTML/CSS/JavaScript website with PHP contact and driver-interest forms. Branding uses the supplied Drive4U wordmark and icon with white backgrounds removed.

## Pages
- `index.html` — homepage
- `about.html` — company background and management (Zamokuhle Mdluli and Bheka Ndimande)
- `services.html` — fleet services and FAQs
- `team.html` — three drivers
- `team-details.html` — driver story directory
- `team-details-1.html`, `team-details-2.html`, `team-details-3.html` — individual crawlable driver stories
- `become-a-driver.html` — driver interest application form
- `contact.html` — vehicle owner and general contact form
- `privacy.html`, `terms.html`, `404.html`

## Before publishing
1. **Use HTTPS and PHP 8.1+** with cURL, mbstring, sessions and OpenSSL. Apache is recommended for the included `.htaccess` rules. The static HTML can be previewed locally, but forms require a PHP server.
2. Run `composer install --no-dev --optimize-autoloader` in the project root to install PHPMailer. This project does not bundle vendor dependencies.
3. Copy `config/mail.example.php` to `config/mail.php` and configure SMTP host, port, password and encryption. The username is already `noreply@driver4u.co.za`. The host is a **placeholder guess**; verify your actual mail provider's settings. Incoming enquiries default to `info@drive4u.co.za`.
4. Create Google **reCAPTCHA v3** keys for `driver4u.co.za` (include `www.driver4u.co.za` if needed). Put the **public site key** in `assets/js/site-config.js` and the **secret key** in `config/mail.php`. The PHP endpoint verifies success, hostname, action and score (default 0.5).
5. Confirm **which of the three supplied portraits belongs to each named driver**. Currently portraits are provisionally assigned in upload order: `driver-01.jpg` = Ntuthuko, `driver-02.jpg` = Siphamandla, `driver-03.jpg` = Lungelo. Replace files or update references after checking.
6. Add management photos in the About page: replace `.portrait-placeholder` elements with images and confirm titles. Do not invent titles.
7. Review the privacy policy and terms with your actual POPIA processes and contractual requirements. Confirm permission to publish photographs and testimonials.
8. Test both forms using valid and invalid submissions, verify the message is delivered, check SPF/DKIM/DMARC records for the sender domain, and verify reCAPTCHA scores in its admin console. Consider CAPTCHA threshold tuning.
9. Confirm address, contact details, and vehicle requirements; configure Google Search Console, submit `sitemap.xml`, verify canonical domain and add your Google Business Profile if eligible. Monitor indexing. Meta keywords are included at your request, but major search engines generally do not use them for rankings.
10. Replace/optimise photography as needed. Run Lighthouse and mobile accessibility tests on your actual host; SEO tags do not guarantee search rankings.

## Important notes
- **Never** place SMTP password or reCAPTCHA secret in HTML or JavaScript.
- Keep `config/mail.php` inaccessible from the web (Apache rule included); for stronger separation, move credentials **outside the public document root** and update the PHP config path.
- Do not publish a `vendor` directory that is web-accessible without blocking access to its internals. Apache rules are included; configure equivalent rules for Nginx.
- The website uses Google Fonts and Font Awesome CDN. You can self-host these assets for stricter privacy and availability.
- Static HTML canonical URLs currently include `.html`. Keep those URLs or update canonicals, sitemap, navigation and redirects consistently if switching to extensionless routes.
- Google reCAPTCHA requires external scripts and access to Google's verification endpoint. Test in production.
- If the domain is hosted behind a reverse proxy, verify HTTPS redirect logic with your host to avoid redirect loops.
