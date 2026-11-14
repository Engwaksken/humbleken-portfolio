# IT Problem Solver: Flutter companion app

Flutter client for the Laravel site in this repository. It uses the versioned JSON API
under `/api/v1`. [`docs/API.md`](../docs/API.md) is the contract it is built against.

Targets: Android, iOS and web. Tested with Flutter 3.44 (Dart 3.12).

## Running against a local Laravel server

1. Start the API from the repository root:

   ```bash
   php artisan serve            # http://127.0.0.1:8000
   ```

   To reach it from a physical device on your network, bind to all interfaces instead:
   `php artisan serve --host=0.0.0.0 --port=8000`.

2. Install packages and run the app from `mobile/`:

   ```bash
   cd mobile
   flutter pub get
   flutter run                  # pick a device when prompted
   ```

### API base URL

The app reads the server origin (without `/api/v1`) from a compile-time define:

```bash
flutter run --dart-define=API_BASE_URL=http://192.168.1.20:8000
flutter build web --dart-define=API_BASE_URL=https://staging.example.com
```

When `API_BASE_URL` is not set, **release builds** (`flutter build apk/appbundle/ipa/web`,
`flutter run --release`) use the live site, **https://www.humbleken.com**. Debug and profile
builds default to a local server:

| Platform (debug/profile) | Default | Why |
| --- | --- | --- |
| Android emulator | `http://10.0.2.2:8000` | `10.0.2.2` is the emulator's alias for the host machine's `127.0.0.1`. `127.0.0.1` inside the emulator is the emulator itself. |
| iOS simulator, web, desktop | `http://127.0.0.1:8000` | Same machine as `php artisan serve`. |

On a **physical Android/iOS device**, neither default works. Pass your computer's LAN IP with
`--dart-define` and serve Laravel on `0.0.0.0`.

Plain HTTP is allowed only where local development needs it:

- **Android:** `usesCleartextTraffic` is set in `android/app/src/debug/AndroidManifest.xml`, so
  only debug builds allow it. Release builds require HTTPS.
- **iOS:** `NSAllowsLocalNetworking` permits local addresses. Public hosts still require HTTPS.
- **Web:** the browser enforces CORS. The Laravel app must allow the Flutter dev-server origin
  (e.g. `http://localhost:<port>`) for `api/v1/*` in `config/cors.php`. To get a stable port,
  run `flutter run -d chrome --web-port 5000`.

## Play Store release

- **Application ID:** `com.humbleken.app`. It can never change after the first upload.
- **Server:** release builds use https://www.humbleken.com. Deploy the Laravel site first, so
  `/api/v1/privacy-policy` and `DELETE /api/v1/auth/account` exist on the live server.
- **Signing:** `android/key.properties` points at `android/upload-keystore.jks`. Both are
  git-ignored. Back them up somewhere safe (a password manager or encrypted drive). Every
  future update must be signed with this upload key. With Play App Signing, Google holds the
  app signing key, and a lost upload key can be reset through Play Console support.
- **Push notifications (Firebase):** `android/app/google-services.json` (Firebase project
  `humble-ken`, app `com.humbleken.app`) is git-ignored because the repository is public. Download
  it again from the Firebase console when building on another machine. The server needs the
  Firebase service account key, pasted in admin Settings, Mobile app. Customers get a phone
  notification when I reply to a ticket or change their booking status, and can turn each off
  on the Account screen. Push is Android only; iOS would need APNs setup.
- **Version:** bump `version:` in `pubspec.yaml` before each upload. The number after `+` is
  the Android version code and must go up every time (`1.0.0+1`, `1.0.1+2`, ...).

Build the bundle to upload:

```bash
flutter build appbundle --release
# build/app/outputs/bundle/release/app-release.aab
```

Play Console listing details the app already supports:

- **Privacy policy URL:** https://www.humbleken.com/privacy (edited in admin Settings, Privacy policy).
- **Account deletion URL:** https://www.humbleken.com/account/delete. In the app: Account, Delete my account.
- **Data safety:** the app collects name, email, phone (optional), messages, support ticket
  files and chat messages; all sent over HTTPS; users can request deletion. No ads or tracking.

## Verification

```bash
flutter analyze
flutter test
flutter build web
```

## Features

- **Configuration and API client** (`lib/core/api`, `lib/core/config`)
  - Dio client that sends `Accept: application/json` and attaches `Authorization: Bearer <token>`
    when signed in.
  - Failures become an `ApiException` that keeps Laravel's 422 field errors.
  - A 401 on an authenticated request clears the stored token, which returns the app to the
    signed-out state.
- **Site theming:** `GET /site` loads at startup.
  - The Material 3 theme is built from `theme.primary` and `theme.secondary`. If a brand colour
    falls below 4.5:1 contrast against the surface, the seeded tonal colour is used instead.
  - Fallback colours: `#077784` and `#14263d`.
  - The brand name and logo appear in the app bar and the drawer.
  - When `maintenance.enabled` is true, a maintenance screen shows the message and a retry button.
- **Navigation** (go_router, `StatefulShellRoute`)
  - Bottom bar: Home, Services, Work, Articles, Account. Work and Articles are hidden when
    `features.projects` or `features.articles` is false.
  - The drawer also links to Approach, Booking, Contact and the AI assistant (shown only when
    `features.chatbot` is true).
- **Home:** renders hero, services, why, projects and CTA in order, and skips disabled sections.
  - Hero aside: either an image (its `image_alt` is the screen-reader label) or a numbered-steps
    card that links to Contact.
  - Why items: all 16 Font Awesome names map to Material icons, with a fallback icon.
- **CMS pages** (`/pages/{key}`): eyebrow, title, intro, hero image, body, numbered steps, and a
  gallery that opens a full-screen, pinch-to-zoom viewer.
  - The Services tab adds the catalogue grouped by category.
  - Approach uses the generic page screen.
  - Booking and Contact add their forms.
- **Detail screens:** service (facts, "Request this service" opens Booking with the service
  preselected, video opens externally), project and article.
  - Project and article bodies render Markdown. Links open externally.
- **Lists:** projects, articles and tickets.
  - Infinite scroll driven by `meta`, pull-to-refresh, and loading, empty and error states with
    retry.
  - Articles also have debounced search (`q`) and category chips from `/article-categories`.
- **Forms:** contact enquiry and booking request.
  - Client validation mirrors the server rules, and server 422 errors show inline on the matching
    field.
  - Both have an optional service dropdown.
  - Booking has a date and time picker. It sends ISO 8601 with the device's UTC offset, e.g.
    `2026-11-20T14:30:00+03:00`.
  - Contact also lists the details from `/site`, each tappable: `mailto:`, `tel:`,
    `https://wa.me/<digits>` and social links.
- **Account**
  - Sign in, register and forgot password. The token is kept in `flutter_secure_storage`, and
    `device_name` is the platform name.
  - If the email isn't verified, a banner offers "Resend email" and "I've verified, refresh".
  - Signed-in users get a paginated ticket list with status chips and a ticket thread that styles
    staff and customer replies differently.
  - Replying is disabled when a ticket is closed. "New ticket" asks for a subject and body.
    Attachments are listed by name only.
  - Sign out calls `/auth/logout`, then clears the token.
- **AI assistant** (`POST /chatbot/messages`): sends the last 12 messages and is shown only when
  `features.chatbot` is true.

### Accessibility

- Headings carry `Semantics(header: true)`.
- Each tappable card is merged into a single link node for screen readers.
- Decorative icons and images are excluded from semantics. Content images use their alt text.
- Live regions announce form results and errors.
- Material components keep tap targets at 48dp or larger.
- Layouts use intrinsic heights (no fixed-height cards), so large text scales without clipping.
- Animations respect "reduce motion".
- A widget test checks the home screen against Flutter's tap-target, labelled-tap-target and
  text-contrast guidelines.
- A manual screen-reader pass (TalkBack/VoiceOver) is still needed.

## Project structure

```
lib/
  main.dart, app.dart          bootstrap (reads the saved token), MaterialApp + theme + startup gate
  core/
    api/                       ApiClient (Dio), ApiException, Paginated/PageMeta, PaginatedNotifier
    auth/                      TokenStore (secure storage) + AuthToken notifier
    config/                    AppConfig (API_BASE_URL) and device name
    router/                    routes, GoRouter config, bottom-navigation shell
    theme/                     theme from brand colours, contrast helpers
    utils/                     JSON helpers, validators, formatting, external links
    widgets/                   Loading/Error/Empty views, AppNetworkImage, image viewer,
                               paginated list, Markdown, form widgets, drawer, app bar
  features/<feature>/
    data/                      plain Dart models (fromJson) + repository + Riverpod providers
    presentation/              screens and widgets
test/
  models/                      JSON parsing (home page, ticket detail, pagination meta, site, services)
  utils_test.dart              icon mapping, links, config, theme contrast, ISO dates, 422 parsing
  widgets/                     home screen with a fake repository; app smoke test across all tabs
```

No code generation: models are hand-written, and providers are plain Riverpod 3 declarations.

## Known limitations

- Ticket attachments cannot be uploaded or downloaded from the app (names are shown).
- SVG logos are not rendered (the app shows the brand name only). Use a PNG/WebP logo for the
  app, or add `flutter_svg`.
- Service descriptions and page `body` render as plain text. Projects and articles use
  `body_markdown`.
- No offline cache beyond image caching. No push notifications.
