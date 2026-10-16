# Architecture

Cybersecurity-Academy is a database-driven PHP application built on a small,
purpose-made MVC framework rather than a full-stack framework. The goal is a
codebase that is easy to read, audit and extend, with security and
server-side correctness as first principles.

---

## 1. Request lifecycle

```
public/index.php
   └─ App (kernel / container)
        ├─ loads .env, config, routes
        ├─ builds the Request
        ├─ Router matches method + path → [controller, action] + middleware
        ├─ middleware pipeline (session, CSRF, auth, can:permission, locale, …)
        ├─ controller action → Services (business logic) → Repositories → Database
        ├─ View renders a template → Response
        └─ sends the Response (with security headers + CSP)
```

A single front controller (`public/index.php`) handles every dynamic request;
the web server serves real files and routes everything else to it.

---

## 2. Layers

| Directory | Responsibility |
|---|---|
| `app/Core/` | Framework: kernel/container (`App`), `Router`, `Request`/`Response`, `Database` (PDO wrapper), `Session`, `Auth`, `Validator`, `Translator`, `View`, `Mailer`, `RateLimiter`, `Logger`, `Config`, `Env`. |
| `app/Controllers/` | Thin HTTP handlers: validate input, call services, return a view or redirect. No business logic. |
| `app/Services/` | All business logic (enrolment, scoring, certificates, payments, sharing, uploads, notifications). |
| `app/Repositories/` | Data access helpers over the `Database` wrapper. |
| `app/Models/` | Lightweight data holders / row mappers. |
| `app/Middleware/` | Cross-cutting request concerns (auth, CSRF, permissions, locale). |
| `app/Policies/` | Authorisation decisions for resources. |
| `app/Validators/` | Input validation rule sets. |
| `app/Helpers/` | Global helper functions (`db()`, `session()`, `auth()`, `__()`/translation, URL helpers). |

Controllers depend on services; services depend on repositories and the
database. Dependencies point inward — the framework core knows nothing about
specific features.

---

## 3. Database layer

`app/Core/Database.php` is a thin PDO wrapper. Every query is a prepared
statement; there is deliberately **no** method that interpolates values into
SQL. `insert`/`update`/`delete` build parameterised statements and validate
identifiers against `^[a-zA-Z_][a-zA-Z0-9_]*$`. A nesting counter lets services
compose transactions safely (`transaction()` nests), and test helpers
(`beginTestTransaction`/`rollBackTestTransaction`) wrap each test in a
rolled-back transaction.

### Schema and migrations

Schema lives in versioned SQL files under `database/migrations/`, applied in
order and tracked in a migrations table so re-running only applies new ones:

| Migration | Contents |
|---|---|
| `001_identity_and_access.sql` | users, roles, permissions, sessions, audit log |
| `002_learning_content.sql` | paths, courses, modules, lessons, translations |
| `003_labs.sql` | labs, lab tasks, submissions |
| `004_assessments.sql` | question banks, quizzes, attempts, frozen questions |
| `005_certification_and_sharing.sql` | certificates, events, share records |
| `006_commerce_and_support.sql` | orders/payments, coupons, support, settings |

`bin/console schema:dump` regenerates `database.sql` from the live schema for
reference. Tables are InnoDB, `utf8mb4`, with foreign keys.

---

## 4. Routing

`app/Core/Router.php` supports named routes, groups with shared prefixes and
middleware, and inline parameter constraints — including regex quantifiers such
as `{token:[a-f0-9]{32}}`. Routes are declared in `routes/web.php`. The
`url(name, params)` helper builds URLs from route names so links stay correct
if paths change.

---

## 5. Internationalisation

- Interface strings live in `lang/en/*.php` and `lang/fr/*.php`, glob-merged by
  domain (`common`, `public`, `learning`, `admin`).
- `bin/console translations:check` enumerates every interface key and fails if
  any is missing in French, so the UI can never ship partially translated.
- **Content** (lessons, labs, questions) carries a per-item
  `translation_status`. When a French translation is incomplete the platform
  says so — it never presents English text as if it were finished French.

---

## 6. Content pipeline

Authoritative content is versioned under `database/content/`:

```
curriculum.php        paths / courses / modules (bilingual)
lessons/*.md          lessons with front-matter + assignment/criteria/references
labs/*                lab definitions with tasks and answer specs
datasets/*.csv        synthetic datasets for labs
questions/*.php       question banks, module quizzes, final exams
```

Content is seeded into the database (`bin/console seed`) or authored through the
admin area. Lessons are Markdown, rendered by `ContentRenderer` with HTML input
escaped and custom `:::block:::` directives (solution, hint, note, tip,
warning, scenario, check, diagram).

---

## 7. Server-side correctness

Security-critical logic never trusts the browser:

- **Labs** (`LabService`): a task's `answer_spec` (JSON) stays on the server and
  is never sent to the client; submissions are scored server-side and the user
  row is locked during submission.
- **Exams** (`QuizService`): questions are drawn at random and **frozen** into
  the attempt; a server-side timer governs expiry; submission locks the attempt
  `FOR UPDATE` so a double submit cannot double-score.
- **Certificates** (`CertificateService`): issued only when
  `CompletionService` confirms eligibility; signed with HMAC-SHA256 over
  `APP_KEY`.
- **Payments** (`PaymentService` + `Payments/`): a provider abstraction with a
  labelled demo provider and an implemented Stripe provider; enrolment is
  granted only on a signature-verified webhook, idempotently, with amount and
  currency checked.

---

## 8. Front end

- `public/assets/css/app.css` — a design system with a dark default and a light
  theme, responsive and reduced-motion aware.
- `public/assets/js/app.js` — dependency-free JavaScript (theme toggle,
  navigation, exam timer, lab log viewer, network canvas, share logging). All
  scripts are external to satisfy the no-inline-script CSP.

---

## 9. Testing

- `tests/Unit/` — pure logic (answer checking, Stripe signature, discounts).
- `tests/Feature/` — full HTTP-kernel tests (auth, CSRF, access control, the
  lesson→lab→exam→certificate flow, verification/tampering/revocation, sharing
  privacy, server-side payment verification, upload restrictions).
- Each test runs inside a transaction rolled back on teardown, against a
  dedicated test database the suite refuses to confuse with production.
- `.github/workflows/ci.yml` runs lint, PHPUnit and the translation check on
  every push.

---

## 10. Design principles

1. **Server-side truth.** Scoring, authorisation, payment and certificate
   validity are decided on the server; the UI only reflects them.
2. **Prepared statements only.** No code path builds SQL from values.
3. **No inline scripts.** A strict CSP is a structural constraint, not an
   afterthought.
4. **Honest content state.** Incomplete modules and translations are labelled,
   never faked.
5. **Small, readable core.** Features are added as services and content files,
   not framework magic.
