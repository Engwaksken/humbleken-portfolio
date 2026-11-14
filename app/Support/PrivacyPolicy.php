<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * The privacy policy shown at /privacy and in the mobile app. Edited in admin
 * Settings, Privacy policy (Markdown). Until it is edited, a default describing
 * what this site and app actually collect is used.
 */
class PrivacyPolicy
{
    private const ALLOWED_HTML_TAGS = [
        'a', 'article', 'b', 'blockquote', 'br', 'code', 'dd', 'del', 'div', 'dl', 'dt', 'em',
        'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hr', 'i', 'li', 'ol', 'p',
        'pre', 'section', 'small', 'span', 'strong', 'sub', 'sup', 'u', 'ul',
    ];

    private const DROP_HTML_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math'];

    public static function markdown(): string
    {
        $custom = trim((string) Settings::get('privacy_policy'));

        return $custom !== '' ? $custom : static::default();
    }

    public static function html(): HtmlString
    {
        $content = static::markdown();

        // Full HTML fragments should be parsed as HTML, not fed through Markdown's
        // block parser (which can escape semantic tags such as <article>/<section>).
        if (! preg_match('/^\s*<(?:article|html|main|div|section|header|footer|p|h[1-6]|ul|ol|blockquote)\b/i', $content)) {
            $content = Str::markdown($content, [
                'html_input' => 'allow',
                'allow_unsafe_links' => false,
            ]);
        }

        return new HtmlString(static::sanitizeHtml($content));
    }

    private static function sanitizeHtml(string $html): string
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML(
            '<!doctype html><html><body><div id="privacy-policy-root">'.$html.'</div></body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('privacy-policy-root');
        if (! $root instanceof DOMElement) {
            return '';
        }

        static::sanitizeChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return $output;
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP_HTML_TAGS, true)) {
                $parent->removeChild($child);

                continue;
            }

            static::sanitizeChildren($child);

            if (! in_array($tag, self::ALLOWED_HTML_TAGS, true)) {
                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = trim($attribute->value);
                $allowed = $name === 'title'
                    || ($name === 'class' && preg_match('/^[\w\s-]+$/u', $value))
                    || ($tag === 'a' && $name === 'href' && preg_match('/^(?:https?:\/\/|mailto:|tel:|\/|#)/i', $value));

                if (! $allowed) {
                    $child->removeAttributeNode($attribute);
                }
            }
        }
    }

    public static function updatedAt(): ?string
    {
        return Settings::get('privacy_policy_updated_at') ?: null;
    }

    public static function default(): string
    {
        $brand = Settings::get('brand_name') ?: config('app.name');
        $owner = Settings::get('owner_name') ?: $brand;
        $email = Settings::get('contact_email');
        $contact = $email ? "email me at {$email}" : 'use the contact form on the website';
        $site = rtrim((string) config('app.url'), '/');

        return <<<MD
        This policy explains what personal information {$brand} collects through this website ({$site}) and the {$brand} mobile app, why it is collected and how you can control it. The site and app are run by {$owner}.

        ## Information I collect

        - **Enquiries and appointment requests:** your name, email address, optional phone number, the service you are interested in, your preferred time and your message.
        - **Customer accounts:** your name, email address and a securely hashed password. Signing in from the app stores a sign-in token on your device.
        - **Support tickets:** the subject, messages and any files you attach.
        - **Chat assistant:** the questions you type into the chat assistant, if it is switched on.
        - **Technical data:** your IP address and basic request details in server logs, used for security and to prevent abuse.

        I do not use advertising or tracking cookies, and I do not sell your information.

        ## How I use it

        - To reply to your enquiry, arrange appointments and provide support.
        - To send you emails you asked for, such as confirmations, replies to your tickets, email verification and password resets.
        - To keep the site and app secure, for example by limiting repeated requests.

        ## Who I share it with

        - **Email delivery:** emails are sent through my email provider.
        - **Chat assistant:** when the chat assistant is switched on, your chat messages are sent to an AI service provider to generate a reply. Please do not type sensitive information into the chat.
        - **Payments:** if you pay by mobile money through ioTec Pay, your phone number and the amount are shared with ioTec to process the payment. Bank transfers are handled by your bank.
        - **Hosting:** the site and its data are stored with my web hosting provider.

        I only share information when it is needed for these purposes or when the law requires it.

        ## How long I keep it

        Enquiries, appointment requests and support tickets are kept while they are useful for providing the service and for my records. Customer account data is kept until you delete your account.

        ## Your choices and rights

        - You can turn off ticket reply emails in your account settings on the website or in the app.
        - You can **delete your account** at any time from your account page on the website or the Account screen in the app. This permanently deletes your account, your support tickets and attachments, and the appointment requests and enquiries linked to your account. See {$site}/account/delete for step-by-step instructions.
        - You can ask for a copy of your information, or ask me to correct or delete it, including enquiries you sent without an account. To do this, {$contact}.

        ## Security

        Passwords are hashed, the site uses HTTPS, attachments are stored privately and stored secrets are encrypted. No system is perfectly secure, so please contact me straight away if you think your account has been misused.

        ## Children

        The site and app are not intended for children under 13, and I do not knowingly collect their information.

        ## Changes to this policy

        If I change this policy, I will update it on this page and in the app.

        ## Contact

        For any privacy question, {$contact}.
        MD;
    }
}
