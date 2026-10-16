---
title: Web Application Security Fundamentals
chapter: How web applications fail — and how to prevent it
summary: Understand the most common classes of web-application weakness using the OWASP Top 10 as a map — what each one is, how it arises in code, and how to prevent it — framed for assessment and defence rather than exploitation.
duration: 55
level: intermediate
preview: true
objectives:
- Explain how the client-server web model creates an attack surface
- Describe the main OWASP Top 10 risk categories conceptually
- Explain how injection and broken access control arise and how to prevent them
- Recognise authentication, misconfiguration and component risks
- Map each weakness to a concrete defensive control
prerequisites:
- Vulnerability Assessment and CVSS Scoring
---

## Introduction

Web applications are the largest attack surface most organisations have: reachable by anyone, running complex code, and connected to the data that matters. This lesson maps the most common classes of web-application weakness using the **OWASP Top 10**, the industry's reference list. The emphasis is conceptual and defensive: for each class you will learn *what the weakness is, how it arises in code, and how to prevent it* — so you can assess an application and recommend fixes. It is not an exploitation walkthrough; any hands-on offensive practice happens only in isolated labs within the authorisation and scope from the first module.

Understanding *why* applications fail is what lets you both find the weaknesses (as an assessor) and stop them (as a defender or developer).

## The web model and its attack surface

A web application is a conversation between an untrusted **client** (the browser, fully under the user's control) and a trusted **server**. Every input the server receives — URL parameters, form fields, headers, cookies, uploaded files, API bodies — comes from a place the server does not control and must therefore treat as potentially hostile.

```text
   BROWSER (untrusted)                          SERVER (trusted)
   user controls everything   ── request ──►    must validate EVERYTHING
   it sends                    ◄── response ──   it receives
```

The single root cause behind most web vulnerabilities is **trusting input or context that should not be trusted** — whether that input is data (injection), identity (access control), or configuration. Keep that lens and the Top 10 stops being a list to memorise and becomes a set of consequences of one mistake.

## The OWASP Top 10, conceptually

The **OWASP Top 10** is a widely used awareness list of the most critical web-application security risks, updated periodically by the Open Worldwide Application Security Project. The current categories, in plain terms:

| Category | What it is (concept) |
|---|---|
| **Broken Access Control** | Users can act outside their intended permissions (view others' data, reach admin functions) |
| **Cryptographic Failures** | Sensitive data not protected properly (weak/absent encryption, exposed secrets) |
| **Injection** | Untrusted input is interpreted as code/commands (SQL, OS, etc.) |
| **Insecure Design** | The design itself lacks necessary controls, before any coding bug |
| **Security Misconfiguration** | Insecure defaults, verbose errors, unnecessary features left enabled |
| **Vulnerable & Outdated Components** | Using libraries/frameworks with known vulnerabilities |
| **Identification & Authentication Failures** | Weak login, session or credential handling |
| **Software & Data Integrity Failures** | Trusting code/updates/data without verifying integrity |
| **Security Logging & Monitoring Failures** | Not detecting or recording attacks (ties to the SOC path) |
| **Server-Side Request Forgery (SSRF)** | The server is tricked into making requests to unintended destinations |

You do not need every detail today; you need the *shape* of each and its defence. The next sections go deeper on the ones a tester meets most.

## Injection

**Injection** happens when untrusted input is incorporated into a command or query in a way that lets the input change its structure, so data is interpreted as **code**. The classic example is SQL injection: a value meant to be a search term becomes part of the SQL statement and alters what the database does.

- **How it arises:** the application builds a query or command by concatenating user input into a string, so a crafted input changes the statement's meaning.
- **The prevention (the important part):** never build commands by string concatenation with untrusted input. Use **parameterised queries / prepared statements**, which send the code and the data separately so input can never be executed as code. Add input validation (allow-lists) and least-privilege database accounts as defence in depth.

:::tip You already saw the defence in this platform's own code
The application you are learning on uses prepared statements for every database query precisely to make SQL injection structurally impossible — the data layer has no API that interpolates values into SQL. That is the canonical injection defence: the fix is architectural (separate code from data), not a filter you bolt on afterwards.
:::

The same principle covers OS command injection, LDAP injection and others: keep untrusted data out of the interpreter's grammar.

## Broken access control

**Broken access control** means a user can do or see something their role should not allow — reading another user's record by changing an ID in the URL, or reaching an admin page by navigating straight to it.

- **How it arises:** the application checks *authentication* (who you are) but not *authorisation* (what you may do) on every sensitive action, or relies on the UI hiding a control rather than the server enforcing the rule.
- **The prevention:** enforce authorisation **server-side on every request**, deny by default, and check ownership (does this record belong to the requesting user?). Hiding a button is never access control — the server must re-check, exactly the RBAC/"server-side authorisation" principle from the GRC and SOC paths.

This is consistently among the most common and most serious web risks because it is a logic flaw, invisible to many automated scanners and specific to each application.

## Authentication, misconfiguration and components

Three more classes a tester meets constantly:

- **Identification & authentication failures** — weak password policies, no protection against credential stuffing or brute force, insecure session tokens, missing MFA. *Prevention:* strong authentication, rate limiting and account lockout (the throttle you saw in the SOC path), secure session management, and MFA — especially for privileged accounts.
- **Security misconfiguration** — default credentials left in place, verbose error messages leaking internals, directory listing enabled, unnecessary services or debug modes on in production. *Prevention:* harden by default, disable what you do not need, suppress detailed errors to users (log them server-side instead), and review configuration as part of deployment (the deploy checklist from the platform docs).
- **Vulnerable & outdated components** — the application depends on libraries and frameworks that have known CVEs. *Prevention:* maintain a software inventory, track dependencies, patch on a schedule, and monitor advisories (the vulnerability-assessment discipline applied to your own stack).

:::scenario One flaw class, traced end to end
An assessor finds that changing the numeric `account_id` in a URL returns another customer's statements — broken access control. The root cause: the server authenticates the session but never checks that the requested account *belongs to* the logged-in user. The fix is a single server-side ownership check on that endpoint (deny by default, verify ownership). The assessor reports it with the CVSS and the contextual risk (customer financial data, internet-facing → high), a redacted proof, and the one-line remediation — exactly the finding structure from the reporting lesson. Weakness understood → risk communicated → fix specified.
:::

## Cross-site scripting and CSRF (browser-side trust)

Two classic browser-side issues illustrate "untrusted context":

- **Cross-site scripting (XSS)** — the application reflects untrusted input into a page without proper encoding, so the browser runs attacker-supplied script in the victim's session. *Prevention:* **output-encode** everything rendered into a page for its context, validate input, and apply a strict **Content-Security-Policy** that forbids inline script (the exact approach this platform uses: all scripts external, no `unsafe-inline`).
- **Cross-site request forgery (CSRF)** — a malicious site causes the victim's browser to send an authenticated request to your app. *Prevention:* per-request **anti-CSRF tokens** and `SameSite` cookies (again, as implemented in this platform).

Both are consequences of trusting something from the browser — input, or a cross-origin request — that should be verified.

## Mapping weaknesses to defences

The assessor's value is turning each weakness into a defence the client can implement:

```text
 Injection                → parameterised queries, input allow-lists, least privilege
 Broken access control    → server-side authorisation on every request, deny by default
 Auth failures            → strong auth, rate limiting, MFA, secure sessions
 Misconfiguration         → harden defaults, suppress verbose errors, review config
 Vulnerable components    → inventory + patch + monitor advisories
 XSS / CSRF               → output encoding + strict CSP; anti-CSRF tokens + SameSite
 Logging/monitoring gaps  → log security events, feed the SOC (the blue-team link)
```

Every row is a finding you could write and a fix you could recommend — the whole purpose of assessing a web application.

## Common mistakes

- **Treating the Top 10 as a checklist to memorise.** It is a map of consequences of trusting untrusted input, identity or configuration; understand the root cause.
- **Filtering injection instead of separating code from data.** Blocklists are bypassable; parameterised queries make injection structurally impossible.
- **Enforcing access control in the UI.** Hiding a control is not a control; the server must re-check authorisation and ownership on every request.
- **Leaving defaults and verbose errors in production.** Misconfiguration is low-effort for attackers and common in reports.
- **Ignoring dependencies.** Known-vulnerable components are among the easiest ways in; inventory and patch them.
- **No logging.** If attacks are not recorded, the SOC cannot see them — a failure in its own right.

## Security implications

- Treat every client input and cross-origin context as untrusted; validate and enforce on the server.
- Prevent injection structurally with parameterised queries, and enforce authorisation server-side on every request.
- Harden configuration, patch components, and use strong auth with rate limiting and MFA.
- Encode output and apply a strict CSP and anti-CSRF tokens, and log security events for the SOC.

## Knowledge check

:::check Questions
1. What single root cause underlies most web-application vulnerabilities?
2. What is the structural defence against SQL injection, and why does it work?
3. Why is hiding a button in the UI not a valid access control?
4. Name the defensive controls for cross-site scripting (XSS).
:::

:::solution Answers
1. **Trusting input or context that should not be trusted** — data (injection), identity/authorisation (broken access control), or configuration. Most Top 10 categories are consequences of that one mistake.
2. **Parameterised queries / prepared statements**: they send the SQL code and the user data to the database separately, so input is always treated as data and can never change the query's structure or be executed as code. It is architectural, not a filter.
3. Because the **server**, not the browser, must enforce authorisation; an attacker can send the request directly regardless of what the UI shows. Access control must be re-checked server-side on every sensitive request (deny by default, verify ownership).
4. **Output-encoding** untrusted data for the context it is rendered in, **input validation**, and a strict **Content-Security-Policy** that forbids inline scripts (plus secure, `HttpOnly` cookies).
:::

## Exercise

For a simple web application (e.g. an online store with customer accounts and an admin area):

1. Pick **four** OWASP Top 10 categories most relevant to it and, for each, describe in one or two sentences how the weakness could arise in *this* app.
2. For each, give the **defensive control** that prevents it.
3. Take one as a **finding** (reuse the reporting lesson's structure): title, contextual risk, a described/redacted proof concept, and remediation.
4. State one way the application should **log** so that attempts against it are visible to a SOC.

:::solution Model answer (abbreviated)
1. *Injection* — a product search builds a SQL query by concatenating the search term. *Broken access control* — order history is fetched by an `order_id` in the URL with no ownership check. *Auth failures* — the admin login has no rate limiting or MFA. *Misconfiguration* — the app returns full stack traces to users on error.
2. Parameterised queries + least-privilege DB user; server-side ownership check (deny by default); rate limiting/lockout + MFA on admin; suppress error detail to users and log server-side.
3. *Finding:* "Broken access control on order history — any authenticated user can read any order by changing `order_id`." Risk: High (customer data, internet-facing). Proof (described/redacted): "changing the id returned a different customer's order summary." Remediation: enforce a server-side check that the order belongs to the session user; deny by default.
4. Log authentication failures and successes (for brute-force detection, as in the SOC path) and authorisation denials, with source IP and account, and forward them to the SIEM.
:::

## Summary and next steps

- Web vulnerabilities almost all stem from **trusting untrusted input, identity or configuration**; the **OWASP Top 10** maps the consequences.
- **Injection** is prevented structurally (parameterised queries); **broken access control** by server-side authorisation and ownership checks on every request.
- Harden configuration, patch components, use strong authentication with rate limiting and MFA, encode output with a strict CSP and anti-CSRF tokens, and log for the SOC.
- Each weakness maps to a concrete defence — which is exactly what you report and recommend.

Later web-security topics and any hands-on testing are performed only against intentionally vulnerable applications in isolated labs, within the authorisation and scope that govern every engagement.

===assignment===
Produce a **web-application security review outline** for an application of your choice: for at least six OWASP Top 10 categories, state in plain language how the weakness could manifest in the app and the specific defensive control that prevents it. Turn two of them into full findings (reporting-lesson structure, with contextual risk and remediation). Add a short note on what the application should log so that attacks are visible to a SOC, connecting the web and blue-team views.

===criteria===
- Complete the reading and the knowledge check, and produce the review outline.
- Each weakness must be paired with a concrete, correct defensive control (e.g. parameterised queries for injection, server-side authorisation for access control); the two findings must use contextual risk and specific remediation; and the content must stay conceptual/defensive (no exploitation steps).
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- OWASP Top 10 — https://owasp.org/Top10/
- OWASP Web Security Testing Guide (WSTG) — https://owasp.org/www-project-web-security-testing-guide/
- OWASP Cheat Sheet Series (defensive controls) — https://cheatsheetseries.owasp.org/
- OWASP Application Security Verification Standard (ASVS) — https://owasp.org/www-project-application-security-verification-standard/
- MITRE CWE (Common Weakness Enumeration) — https://cwe.mitre.org/
