# Security

This document describes the security controls built into Cybersecurity-Academy,
how to configure them, and how to report vulnerabilities. Offensive content in
the curriculum is taught only for authorised, isolated use — see
[Responsible use](#8-responsible-use-and-lab-safety).

---

## 1. Threat model

The platform holds user accounts, learning progress, payment records and
issued certificates. The controls below defend against account takeover,
injection, cross-site attacks, privilege escalation, forged certificates and
payment fraud.

---

## 2. Data access

- **All** database access uses PDO **prepared statements**. The database
  wrapper has no API that interpolates values into SQL, and table/column
  identifiers are validated against a strict allow-list pattern.
- Passwords are stored only as `password_hash()` bcrypt hashes and checked with
  `password_verify()`. Plaintext passwords are never stored or logged.
- Card data is never stored. Payment processing is delegated to the provider;
  the platform keeps only provider references and settlement status.

---

## 3. Sessions and authentication

- Session IDs are regenerated on login and privilege change to prevent
  fixation.
- Session cookies are `HttpOnly`, `SameSite`, and — when
  `SESSION_SECURE_COOKIE=true` — `Secure` (HTTPS only).
- Each account carries an `auth_version`; changing a password bumps it and
  invalidates all other active sessions.
- Login is rate-limited (`LOGIN_MAX_ATTEMPTS`, `LOGIN_DECAY_SECONDS`) using a
  database-backed fixed-window throttle keyed by account and IP.
- E-mail verification is required before sensitive actions.

Relevant `.env` settings:

```ini
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
LOGIN_MAX_ATTEMPTS=5
LOGIN_DECAY_SECONDS=900
```

---

## 4. Request protection

- **CSRF:** every state-changing request (POST/PUT/DELETE) must carry a valid
  per-session token; missing or wrong tokens are rejected.
- **XSS:** all output is escaped by default. Lesson content authored in
  Markdown is rendered with HTML input **escaped** (`html_input=escape`), so
  authored HTML cannot inject script.
- **Content-Security-Policy:** a strict CSP is sent with **no inline scripts**.
  All JavaScript is external and CSP-compatible. This is the single most
  important XSS mitigation and must not be relaxed to allow `unsafe-inline`.
- Additional headers: `X-Content-Type-Options`, `X-Frame-Options`/frame-ancestors,
  `Referrer-Policy`, and HSTS behind HTTPS.

---

## 5. Authorisation (RBAC)

- Four roles: **Super Admin**, **Course Admin**, **Instructor**, **Student**,
  plus granular permissions.
- Authorisation is enforced **server-side** on every sensitive route via
  middleware (`can:permission`) — never in the browser. Hiding a UI control is
  never the access control.
- Sensitive actions are written to an **audit log** (actor, action, target,
  IP, time).

---

## 6. Uploads

- Uploads are validated by MIME type and extension against an allow-list, size-
  capped (`UPLOAD_MAX_BYTES`), given random server-generated names, and stored
  **outside the web root** under `storage/`. They are streamed back through a
  controller that checks authorisation, never served directly.

---

## 7. Certificates and payments

- **Certificates** carry a CSPRNG-generated number and an **HMAC-SHA256
  signature** computed with `APP_KEY`. Public verification distinguishes
  *valid*, *revoked*, *invalid* and *tampered* states, and revocation is
  audited. Because the signature depends on `APP_KEY`, that key must stay
  constant once certificates are issued (see backups in
  [`DEPLOYMENT.md`](DEPLOYMENT.md)).
- Certificates are **not** accredited or government-recognised, and the
  platform never describes them as such.
- **Payments** are verified **server-side** only. Enrolment is granted when a
  signature-verified provider webhook confirms settlement — never on a browser
  redirect. Amount and currency are checked, and settlement is idempotent.
  The Stripe webhook signature check is unit-tested but has not been exercised
  against a live account in this repository; verify it in your own environment
  before selling access.

---

## 8. Responsible use and lab safety

The curriculum includes offensive security topics (e.g. penetration testing).
These are taught strictly for **authorised** testing in **isolated**
environments:

- Learners are shown an in-app Responsible Use and Lab Safety Policy.
- Intentionally vulnerable target applications are **documented for you to
  deploy in your own isolated lab** — the platform does not host them and does
  not perform attacks.
- Lessons emphasise written authorisation, scope and rules of engagement before
  any technique is practised.

Do not use techniques taught here against systems you do not own or lack
explicit written permission to test.

---

## 9. Secrets

- `.env` and everything under `storage/` must never be web-accessible or
  committed. Verify `curl https://host/.env` returns 403/404.
- In production, secrets may be injected as real environment variables instead
  of a file.
- Rotate `STRIPE_*` secrets through your provider; rotate `APP_KEY` only with a
  deliberate certificate re-signing plan, never casually.

---

## 10. Production hardening checklist

- [ ] `APP_DEBUG=false`
- [ ] HTTPS enforced; `SESSION_SECURE_COOKIE=true`; HSTS on
- [ ] CSP intact (no `unsafe-inline` added)
- [ ] Demo accounts removed; strong admin password
- [ ] `storage/` and `.env` not web-accessible
- [ ] Rate-limit values reviewed for your traffic
- [ ] Backups of DB + `storage/`; `APP_KEY` stored safely and separately
- [ ] Payment provider verified end-to-end before go-live

---

## 11. Reporting a vulnerability

Report suspected vulnerabilities through the in-app contact page using the
**Security** category, or to the security contact configured for your instance.
Please include steps to reproduce and avoid testing against other users' data.
Do not publicly disclose before the operator has had a reasonable chance to
respond.
