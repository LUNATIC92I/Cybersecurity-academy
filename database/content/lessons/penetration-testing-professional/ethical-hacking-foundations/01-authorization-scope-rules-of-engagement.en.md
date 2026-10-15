---
title: Authorization, Scope and Rules of Engagement
chapter: The legal and ethical frame
summary: Understand why written authorization is the line between penetration testing and crime, how to define scope precisely, what belongs in rules of engagement, and how established methodologies structure an engagement.
duration: 45
level: beginner
preview: true
objectives:
- Explain why written authorization from the asset owner is mandatory before any testing
- Define in-scope and out-of-scope targets, testing windows and permitted techniques
- List the essential contents of a rules-of-engagement document
- Compare PTES, OWASP WSTG, NIST SP 800-115 and OSSTMM as methodologies
- Describe responsible disclosure and evidence-handling obligations
prerequisites:
- No technical prerequisites; this lesson is the foundation of the path
---

## Introduction

The techniques used by a penetration tester and a criminal attacker can be identical. What separates them is **authorization**: explicit, written permission from someone with the legal authority to grant it, for specific systems, at specific times, using specific methods. Without it, scanning, logging in with discovered credentials or exploiting a vulnerability may be a criminal offence in most jurisdictions — regardless of intent.

This lesson comes first in the path for a reason. Every lab and technique that follows assumes you operate inside this frame.

:::warning Platform rule
Cybersecurity-Academy labs must only be performed against the intentionally vulnerable environments described in each lab, running on infrastructure you own or are explicitly authorised to use. Never test third-party systems, your employer's production systems or public websites without written authorization. See the **Responsible Use and Lab Safety Policy**.
:::

## Why authorization matters

Computer-misuse laws (for example, the US Computer Fraud and Abuse Act, the UK Computer Misuse Act 1990, or Articles 323-1 and following of the French Penal Code) generally criminalise accessing or interfering with systems **without authorization**. Key practical consequences:

- **The person who authorizes must have authority over the asset.** An IT manager cannot authorize testing of a cloud provider's infrastructure; a client cannot authorize testing of a third-party SaaS application it merely uses.
- **Cloud and hosting providers have their own policies.** Many publish penetration-testing policies that define what customers may test on their own resources without prior notice, and what is always prohibited (for example, denial-of-service testing).
- **Authorization must be specific.** "Test our security" is not sufficient; the document must state what, when and how.
- **Exceeding scope is unauthorized access.** A tester who pivots into an out-of-scope network has left the protection of the agreement.

## Defining scope

Scope answers: **what may be tested, and what may not?**

| Element | Examples |
|---|---|
| In-scope targets | `app.example.org`, `203.0.113.0/28`, the mobile app `com.example.wallet` v4.2, AD domain `corp.example.org` |
| Out-of-scope targets | Payment processor endpoints, third-party chat widget, production databases, employees' personal devices |
| Test type | External, internal, web application, API, wireless, social engineering, physical |
| Knowledge level | Black box (no information), grey box (some credentials/docs), white box (full source and architecture) |
| Accounts provided | Two standard user accounts and one administrator account in the staging environment |

Scope should be expressed with **unambiguous identifiers** — exact hostnames, IP ranges in CIDR notation, application versions — not descriptions like "the website". If DNS for an in-scope name resolves to an IP owned by a third party (a CDN, for example), clarify before testing.

## Rules of engagement (RoE)

The RoE translates scope into operating rules. A solid RoE document includes:

1. **Parties and contacts** — client sponsor, technical contacts, tester team, and a **24/7 emergency contact** on both sides
2. **Scope** — the in/out lists above, attached as an appendix
3. **Testing window** — dates, times and time zone; blackout periods (e.g. month-end processing)
4. **Permitted and prohibited techniques** — e.g. no denial of service, no destructive payloads, no social engineering of executives, password attacks limited to 3 attempts per account per hour to avoid lockouts
5. **Data handling** — what data testers may access or copy, how evidence is encrypted and stored, retention and destruction after the engagement
6. **Stop conditions** — e.g. discovery of evidence of a real compromise, instability of a production system, access to unexpectedly sensitive data (health records, payment card data)
7. **Communication plan** — status update frequency, how critical findings are reported immediately
8. **Source IP addresses** of the testers, so defenders can distinguish test traffic
9. **Legal clauses** — authorization statement, liability, confidentiality, signatures with dates

:::scenario When to stop and call
During an authorised internal test, you find a scheduled task on a server running an unfamiliar binary that beacons to an external IP every five minutes. It is not part of your testing. This is a **stop condition**: you may have found an actual intruder. Stop related activity, record what you observed, and contact the client's emergency contact immediately. Do not investigate further on your own — the client's incident response process takes over.
:::

## Methodologies

Methodologies make testing consistent, repeatable and defensible.

| Methodology | Focus | Use it for |
|---|---|---|
| **PTES** (Penetration Testing Execution Standard) | Full engagement lifecycle: pre-engagement, intelligence gathering, threat modelling, vulnerability analysis, exploitation, post-exploitation, reporting | Overall engagement structure |
| **OWASP WSTG** (Web Security Testing Guide) | Detailed test cases for web applications and APIs | Web and API testing checklists |
| **NIST SP 800-115** | Technical guide to information security testing and assessment | Government and compliance-oriented testing |
| **OSSTMM** | Scientific approach to operational security measurement | Metrics-oriented assessments |

A typical engagement flow, mapped to PTES:

```text
Pre-engagement ─► Reconnaissance ─► Enumeration ─► Vulnerability analysis
  (scope, RoE)                                            │
                                                          ▼
     Reporting ◄── Post-exploitation ◄── Controlled exploitation
     & retest       (impact, cleanup)      (proof of concept)
```

## Evidence collection

From the first minute, record:

- **Timestamps** (with time zone) of every significant action
- **Commands** executed and tool versions
- **Raw output** (scan results, HTTP requests and responses)
- **Screenshots** demonstrating findings — with sensitive data redacted in the report
- **Hashes** of any files uploaded to targets, so they can be identified and removed during cleanup

Store evidence encrypted, restrict access to the engagement team, and destroy it according to the agreed retention period.

## Responsible disclosure

Outside of a contracted engagement — for example, if you notice a vulnerability while using a website normally — **do not test further**. Follow the organisation's vulnerability disclosure policy (often published at `/.well-known/security.txt`, per RFC 9116) or bug bounty programme rules. Coordinated disclosure means reporting privately, allowing reasonable time to fix, and not publishing details that would enable exploitation of unpatched systems.

## Common mistakes

- **Verbal approval only** — if it is not written and signed, it does not protect you.
- **Testing an IP because "it's next to" an in-scope one** — adjacency is not authorization.
- **Ignoring the cloud provider's policy** for resources hosted there.
- **Running aggressive scans during business hours** against fragile systems without agreement.
- **Keeping client data after the engagement** beyond the agreed retention period.

## Knowledge check

:::check Questions
1. A client asks you to test `shop.example.com`, which is hosted on a third-party e-commerce SaaS platform. What must you verify first?
2. Name four elements that belong in rules of engagement.
3. During testing, you gain access to a database containing patient health records that was not expected in scope. What should you do?
4. Which methodology provides detailed test cases for web applications?
:::

:::solution Answers
1. Whether the client has the authority to authorize testing of that platform — usually the SaaS provider's permission or published testing policy is required, and the provider's own infrastructure is normally off limits.
2. Any four of: contacts and emergency contacts, scope, testing window, permitted and prohibited techniques, data handling, stop conditions, communication plan, tester source IPs, legal authorization and signatures.
3. Stop accessing the data, record what happened without copying sensitive records, and notify the client contact immediately as defined by the stop conditions.
4. The **OWASP Web Security Testing Guide (WSTG)**.
:::

## Exercise

Draft the **scope and rules-of-engagement summary** for this request: *"Northwind Clinics wants an external test of its patient portal `portal.northwind.example` and its public IP range `198.51.100.32/29` before launch. The portal uses a third-party identity provider. Testing must not disrupt the clinics' booking system, which shares the same range at `198.51.100.36`."*

:::solution Model answer
- **In scope:** `portal.northwind.example` (staging and production login, patient functions using provided test accounts); `198.51.100.32/29` excluding `.36`.
- **Out of scope:** `198.51.100.36` (booking system); the third-party identity provider's infrastructure (test only Northwind's integration and configuration, not the provider); denial-of-service; social engineering.
- **Window:** e.g. 3–14 June, 20:00–06:00 CET; emergency contact available throughout.
- **Rules:** no destructive payloads; password attacks limited to provided accounts; stop immediately if real patient data is accessed; critical findings reported by phone within 2 hours.
- **Data handling:** evidence encrypted at rest, patient data never copied, all evidence destroyed 90 days after report acceptance.
- **Authorization:** signed by an officer of Northwind Clinics with authority over the portal and IP range, plus confirmation of the hosting provider's testing policy.
:::

## Summary and next steps

- Written, specific authorization from the asset owner is the foundation of every engagement.
- Precise scope and rules of engagement protect the client, the tester and third parties.
- Methodologies such as PTES and the OWASP WSTG bring consistency; evidence discipline makes findings defensible.

Continue with the technical foundations, then **Network Enumeration**, always within this frame.

===assignment===
Using the PTES pre-engagement section as a guide, create a reusable **rules-of-engagement template** (two to three pages) with placeholders for client details, scope tables, windows, technique restrictions, stop conditions, communication plan, data handling and signatures.

===criteria===
- Complete the reading, knowledge check and scoping exercise.
- Mark the lesson complete. You must also accept the Responsible Use policy before starting elevated-risk labs.

===references===
- PTES, *Pre-engagement Interactions* — http://www.pentest-standard.org/index.php/Pre-engagement
- OWASP Web Security Testing Guide — https://owasp.org/www-project-web-security-testing-guide/
- NIST SP 800-115, *Technical Guide to Information Security Testing and Assessment* — https://csrc.nist.gov/pubs/sp/800/115/final
- RFC 9116, *A File Format to Aid in Security Vulnerability Disclosure (security.txt)* — https://www.rfc-editor.org/rfc/rfc9116
- Microsoft Cloud Penetration Testing Rules of Engagement — https://www.microsoft.com/msrc/pentest-rules-of-engagement
