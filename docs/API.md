# API v1

All endpoints live under `/api/v1`, accept and return JSON (send `Accept: application/json`),
and are consumed by the Flutter app in [`mobile/`](../mobile). Image and media fields are
absolute URLs or `null`. Timestamps are ISO 8601 strings.

## Conventions

- **Single resources** are wrapped in `{"data": {...}}`.
- **Paginated collections** return `{"data": [...], "meta": {"current_page", "last_page", "per_page", "total"}}`
  and accept `?page=N`.
- **Validation errors** use HTTP 422 with Laravel's envelope:
  `{"message": "...", "errors": {"field": ["..."]}}`.
- **Other errors** return `{"message": "..."}` with 401 (missing/invalid token), 403 (forbidden,
  e.g. unverified email or closed ticket), 404, 429 (rate limited) or 503.
- **Authentication** uses Sanctum personal access tokens: `Authorization: Bearer <token>`.
  Tokens expire after `SANCTUM_TOKEN_EXPIRATION` minutes (default 30 days).
- Public content is only ever published/active content.

## Public content

| Method | Path | Description |
| --- | --- | --- |
| GET | `/health` | `{"status": "ok", "service": "..."}` |
| GET | `/site` | Brand, theme colours, contact details, social links, feature flags, maintenance state |
| GET | `/pages/home` | Every editable home-page section plus featured services/projects |
| GET | `/pages/{key}` | `key` is `services`, `approach`, `booking` or `contact` |
| GET | `/services` | All active services and active categories (not paginated) |
| GET | `/services/{slug}` | One active service |
| GET | `/projects` | Published projects, paginated (12 per page) |
| GET | `/projects/{slug}` | One published project |
| GET | `/articles` | Published articles, paginated (10). Filters: `q`, `category` (slug), `tag` (slug) |
| GET | `/articles/{slug}` | One published article |
| GET | `/article-categories` | Categories with at least one published article |

### `GET /site`

```json
{"data": {
  "brand_name": "IT Problem Solver",
  "tagline": "Engineering solutions and technical consulting",
  "logo_url": "https://example.test/site-assets/logo",
  "sticky_logo_url": null,                 // optional alternative logo for the scrolled/sticky header
  "favicon_url": null,
  "theme": {"primary": "#077784", "secondary": "#14263d"},
  "contact": {"email": "hello@example.test", "phone": null, "whatsapp": null, "business_hours": null, "address": null},
  "social_links": [{"platform": "linkedin", "url": "https://..."}],
  "currency": {"code": "UGX", "symbol": "UGX", "decimals": 0},
  "payments": {
    "iotec": false,                         // ioTec Pay mobile money is configured
    "bank": {"bank_name": "...", "account_name": "...", "account_number": "...", "branch": null, "swift": null},  // or null
    "mobile_money": {"provider": "MTN Mobile Money", "number": "...", "name": "..."},                         // or null
    "instructions": null
  },
  "features": {"projects": true, "articles": true, "chatbot": false},
  "maintenance": {"enabled": false, "message": null}
}}
```

### `GET /pages/home`

```json
{"data": {
  "hero": {
    "eyebrow": "...", "title": "...", "intro": "...",
    "primary_label": "Explore services", "secondary_label": "Book a consultation",
    "aside": "card",                       // "card" | "image" | "none"
    "image_url": null, "image_alt": "",
    "card": {"eyebrow": "...", "text": "...", "steps": ["...", "..."], "link_label": "Send an enquiry"}
  },
  "services": {"enabled": true, "eyebrow": "What we do", "heading": "Services", "link_label": "All services", "items": [ServiceSummary]},
  "why": {"enabled": true, "eyebrow": "...", "heading": "...", "items": [{"icon": "fa-comments", "heading": "...", "text": "...", "image_url": null}]},
  "projects": {"enabled": true, "eyebrow": "Portfolio", "heading": "Recent work", "link_label": "All projects", "items": [ProjectSummary]},
  "cta": {"enabled": true, "title": "...", "text": "..."}
}}
```

`why.items[].icon` is one of the Font Awesome names in `App\Support\PageContent::ICONS`
(`fa-comments`, `fa-scale-balanced`, `fa-circle-check`, `fa-shield-halved`, `fa-bolt`,
`fa-gears`, `fa-server`, `fa-network-wired`, `fa-laptop-code`, `fa-cloud`, `fa-lock`,
`fa-headset`, `fa-chart-line`, `fa-lightbulb`, `fa-clock`, `fa-users`).
`services.items` / `projects.items` are empty when the section is disabled or has no content.

### `GET /pages/{key}`

```json
{"data": {
  "key": "approach",
  "eyebrow": "Approach", "title": "...", "intro": "...",
  "image_url": null, "image_alt": "",
  "body": "Optional extra text, plain text with line breaks",
  "steps": [{"title": "Optional", "text": "...", "image_url": null}],
  "gallery": [{"image_url": "https://...", "caption": "..."}]
}}
```

`steps` holds the approach steps for `approach`, the "what happens next" list for `booking`,
and is empty for other pages.

### `GET /privacy-policy`

`{"data": {"content": "Markdown", "updated_at": "2026-11-14" | null, "url": "https://.../privacy"}}`

### Services, projects, articles

- `ServiceSummary`: `{id, slug, name, summary, category: {id, name, slug} | null, image_url}`
- `GET /services` → `{"data": [ServiceSummary], "categories": [{id, name, slug, description}]}`
- Service detail adds `description, delivery_method, estimated_duration, indicative_price, video_embed_url`.
- `ProjectSummary`: `{id, slug, title, summary, technologies: [string], image_url, published_at}`
- Project detail adds `body_markdown, body_html, video_embed_url`.
- `ArticleSummary`: `{id, slug, title, excerpt, category: {name, slug} | null, tags: [{name, slug}], published_at, reading_minutes}`
- Article detail adds `body_markdown, body_html`.
- `GET /article-categories` → `{"data": [{name, slug}]}`

## Public submissions (rate limited: 5/min per IP)

| Method | Path | Body | Success |
| --- | --- | --- | --- |
| POST | `/enquiries` | `name, email, phone?, service_id?, message (10-5000 chars)` | 201 `{"message": "..."}` |
| POST | `/booking-requests` | `name, email, phone?, service_id?, preferred_at (future datetime), message?` | 201 `{"message": "..."}` |
| POST | `/chatbot/messages` | `messages: [{role: "user"|"assistant", content}]` (1-12) | 200 `{"reply": "..."}`; 404 when the chatbot is disabled |

When automatic replies are on (admin Settings, Auto replies), the client is also emailed a
confirmation, and the `message` says so. Service details include `indicative_price` (number),
`indicative_price_formatted` (e.g. "UGX 250,000") and `currency`.

## Customer authentication (rate limited: 6/min per IP)

| Method | Path | Body | Success |
| --- | --- | --- | --- |
| POST | `/auth/register` | `name, email, password, password_confirmation, device_name` | 201 `{"token", "user": User}` and a verification email |
| POST | `/auth/login` | `email, password, device_name` | 200 `{"token", "user": User}`; 422 on bad credentials |
| POST | `/auth/forgot-password` | `email` | 202 `{"message"}` (always the same message) |
| GET | `/auth/user` | – (Bearer) | `{"data": User}` |
| PUT | `/auth/notification-preferences` | `notification_preferences: {ticket_reply: {mail: bool}}` (Bearer) | `{"data": User}` |
| POST | `/auth/email/verification-notification` | – (Bearer) | 202 `{"message"}` |
| POST | `/auth/logout` | – (Bearer) | 204, revokes the current token |
| DELETE | `/auth/account` | `password` (Bearer) | 204, permanently deletes the account, its tickets and attachments, and linked bookings and enquiries; 422 on a wrong password |

`User`: `{id, name, email, email_verified: bool, notification_preferences: {ticket_reply: {mail: bool}}}`. Administrators cannot sign in through the
API (their accounts are protected by two-factor authentication in the admin panel); the login
endpoint returns 422 for them.

## Support tickets (Bearer token and verified email)

Unverified accounts receive 403 `{"message": "Your email address is not verified."}`.
Tickets belonging to other customers return 404.

| Method | Path | Body | Success |
| --- | --- | --- | --- |
| GET | `/tickets` | – | Paginated `TicketSummary` (15 per page), most recent activity first |
| POST | `/tickets` | `subject, body (10-10000), attachments[]?` | 201 `{"data": TicketDetail}` |
| GET | `/tickets/{id}` | – | `{"data": TicketDetail}` |
| POST | `/tickets/{id}/replies` | `body (2-10000), attachments[]?` | 201 `{"data": TicketDetail}`; 403 when closed |
| GET | `/tickets/{id}/replies/{reply}/attachments/{index}` | – | File download |

Attachments are optional `multipart/form-data` files (max 5, 10 MB each:
pdf, png, jpg, jpeg, gif, txt, csv, doc, docx, xls, xlsx, zip). JSON bodies work when there are
no attachments. Ticket writes are rate limited to 10/min per user.

- `TicketSummary`: `{id, subject, status: "open"|"answered"|"closed", status_label, is_closed, created_at, last_activity_at}`
- `TicketDetail`: `TicketSummary` plus `replies: [{id, body, from_staff, author_name, created_at, attachments: [{index, name, download_url}]}]`.
  Internal staff notes are never returned.
