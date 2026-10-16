# Cybersecurity-Academy

A professional, bilingual (English/French) cybersecurity training and
certification platform covering three learning paths — **SOC Analysis**,
**Penetration Testing** and **Governance, Risk & Compliance (GRC)**.

It is a database-driven e-learning application, not a static site: real
accounts, persisted progress, server-scored labs and examinations, and
verifiable digital certificates.

> **Honest status.** This repository is a working platform with a complete
> engine and a growing body of content. What is implemented, tested and
> infrastructure-dependent is stated plainly below — nothing here is claimed
> to be finished that is not.

---

## Highlights

- **Three learning paths**, 3 flagship courses and 30 bilingual modules.
- **Lessons** with objectives, worked examples, scenarios, exercises with
  solutions, assignments and references. Content is Markdown, rendered with
  HTML escaping and custom blocks (solutions, hints, notes, scenarios).
- **Practical labs** scored on the server — browser simulations with synthetic
  datasets, downloadable datasets, and documented self-hosted virtual labs.
- **Assessment engine**: randomised question selection, server-side timer,
  seven question types, server-only scoring, retake policies, detailed review.
- **Verifiable certificates**: unique numbers, HMAC signatures, QR-coded PDFs
  in English and French, a public verification page, and revocation with an
  audit trail.
- **Social sharing** via official LinkedIn/Facebook share URLs and a
  download-and-copy workflow for TikTok — nothing is ever posted automatically,
  and nothing is public unless the learner opts in.
- **Administration** area with role-based access control (Super Admin, Course
  Admin, Instructor, Student), content management, translation tracking,
  question banks, certificate management, commerce, support and audit logs.
- **Security-first**: PDO prepared statements, `password_hash`, session
  regeneration, CSRF protection, a strict Content-Security-Policy with no
  inline scripts, database-backed rate limiting, audit logging, validated and
  out-of-web-root uploads, and server-side authorisation on every sensitive
  action.
- **Fully bilingual**: a centralised translation system with a key-coverage
  check (`php bin/console translations:check`) that fails if any interface key
  is missing in French; content carries a per-item translation status and
  never shows English as if it were a complete French translation.

## Requirements

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `gd`, `intl`,
  `fileinfo`, `openssl`.
- MySQL **8+** (or MariaDB 10.6+), InnoDB, `utf8mb4`.
- [Composer](https://getcomposer.org/).

## Quick start (local development)

```bash
git clone <your-fork-url> cybersecurity-academy
cd cybersecurity-academy
composer install

cp .env.example .env
php bin/console key:generate     # copy the APP_KEY line into .env
# edit .env: set DB_* credentials; keep PAYMENT_PROVIDER=demo for now

# create the database, then:
php bin/console migrate
php bin/console seed --demo      # reference data, curriculum, labs, exams, demo accounts

# serve the public/ directory
php -S 127.0.0.1:8000 -t public bin/dev-router.php
```

Open <http://127.0.0.1:8000>. The `--demo` seeder prints three demo accounts
and a one-time generated password. **Delete the demo accounts before
deploying** (or seed without `--demo`).

Create your own administrator instead of using a demo account:

```bash
php bin/console make:admin --email=admin@example.org --name="Platform Admin"
```

Full instructions, including Apache/Nginx and HTTPS, are in
[`docs/INSTALLATION.md`](docs/INSTALLATION.md) and
[`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

## Console commands

| Command | Purpose |
|---|---|
| `php bin/console migrate` | Apply pending migrations |
| `php bin/console migrate:fresh [--seed] [--demo]` | Drop all tables and re-migrate (dev only) |
| `php bin/console seed [--demo]` | Seed reference data and curriculum (and demo accounts) |
| `php bin/console make:admin --email=` | Create a super administrator |
| `php bin/console key:generate` | Print a new `APP_KEY` |
| `php bin/console maintenance:run` | Hourly housekeeping (run from cron) |
| `php bin/console translations:check` | Report missing French translations |
| `php bin/console schema:dump` | Regenerate `database.sql` from migrations |

## Testing

```bash
composer install            # installs PHPUnit (dev dependency)
# create an empty cyberacademy_test database and a .env.testing (see docs)
php vendor/bin/phpunit
```

The suite builds the schema in the **test** database and runs each test inside
a rolled-back transaction. It covers authentication, throttling, CSRF, access
control, the lesson/lab/exam flow, certificate issuance, verification,
tampering and revocation, sharing privacy, server-side payment verification and
file-upload restrictions. GitHub Actions runs lint + tests + the translation
check on every push (`.github/workflows/ci.yml`).

## Project layout

```
app/            Core framework, controllers, services, repositories, middleware
config/         Configuration (reads from environment)
database/       Migrations, seeders, and versioned content (curriculum, lessons,
                labs, questions, datasets)
lang/           Centralised en/fr interface translations
public/         Web root: front controller and built assets
resources/views Templates (public, dashboard, admin, emails, certificate PDF)
routes/         Route definitions
storage/        Logs, cache, sessions, uploads, certificates (outside web root)
tests/          Unit and feature tests
docs/           Installation, deployment, security and user guides
```

See [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) for the design rationale.

## Content status

The curriculum is published progressively and honestly. Every published lesson
is fully bilingual (English and French), with objectives, worked examples,
scenarios, a knowledge check, an exercise with a model answer, an assignment and
references. At the time of writing, **31 lessons** are published:

- **SOC Analysis path — complete.** All 10 modules are published: cybersecurity
  foundations, networking (incl. DNS/HTTP/proxy log analysis), operating-system
  logs (Windows and Linux), SIEM operations, KQL and detection engineering,
  incident response (lifecycle and alert triage), threat intelligence and
  hunting, identity and cloud monitoring, malware and endpoint investigation,
  and advanced SOC operations. Every module has a module quiz, and the final
  exam draws across every module.
- **GRC path — complete.** All 10 modules are published: GRC fundamentals, risk
  management, ISO/IEC 27001, the NIST Cybersecurity Framework 2.0, security
  controls and auditing, privacy and data protection, business continuity,
  third-party risk, GRC tools and documentation, and a capstone project. Each
  module has a module quiz, and the final exam draws across every module.
- **Penetration Testing path — in progress.** 6 of 10 modules published, in a
  conceptual, defensive framing: ethics/authorisation and rules of engagement,
  Linux and technical foundations, passive reconnaissance and OSINT,
  vulnerability assessment and CVSS, web application security, and reporting and
  remediation. The remaining modules (network enumeration, exploitation,
  Active Directory assessment, privilege escalation) are **in development**;
  hands-on offensive techniques are taught only for authorised, isolated use.
- **Labs**: five server-scored labs across SOC, GRC and Pentest.
- **Assessments**: ~165 bilingual questions feeding 24 module quizzes and a
  final exam for each of the three paths (random draw, server-side scoring).

Modules not yet written are shown as **in development** in the syllabus — the
platform never pretends a module is complete when it is not. New courses,
lessons, labs and questions are added through the admin area or as versioned
content files under `database/content/`, then seeded.

## What is *not* configured out of the box

- **Payments** run in a clearly labelled **demonstration mode** until a real
  provider is configured. A Stripe Checkout integration is implemented and its
  webhook-signature verification is unit-tested, but it has **not** been run
  against a live Stripe account in this repository; configure test keys and
  verify an end-to-end checkout before selling access.
- **Virtual labs** (intentionally vulnerable applications) are documented for
  you to deploy in your own isolated environment; the platform does not host
  them.
- **E-mail** writes to a log file by default; configure SMTP for production.

## Security and responsible use

Report vulnerabilities via the contact page (category "Security"). Offensive
techniques are taught only within explicit authorisation and isolated
environments — see the in-app Responsible Use and Lab Safety Policy and
[`docs/SECURITY.md`](docs/SECURITY.md).

## Certificates

Cybersecurity-Academy certificates document achievement **within this
platform**. They are not accredited, government-recognised or equivalent to
external professional certifications, and the platform never describes them as
such.

## License

Proprietary. All rights reserved by the operator of the instance.
