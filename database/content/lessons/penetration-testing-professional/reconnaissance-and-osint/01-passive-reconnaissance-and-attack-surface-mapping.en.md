---
title: Passive Reconnaissance and Attack-Surface Mapping
chapter: Understanding the target before you touch it
summary: Learn how a penetration tester builds a picture of a target from public information alone — OSINT sources, the external attack surface, and the human and exposure risks it reveals — all without sending intrusive traffic, and all within the authorisation and scope that govern the engagement.
duration: 50
level: intermediate
preview: true
objectives:
- Distinguish passive reconnaissance from active scanning and know why the line matters
- Identify the main categories of open-source intelligence (OSINT) a tester uses
- Map an organisation's external attack surface from public data
- Recognise the exposure and human risks reconnaissance reveals, defensively
- Stay within authorisation, scope and privacy law while gathering information
prerequisites:
- Authorization, Scope and Rules of Engagement
---

## Introduction

Before a tester assesses or reports anything, they build a picture of the target: what it exposes to the internet, what technologies it runs, and what an attacker could learn about it from public sources alone. This is **reconnaissance**, and its first, safest phase is **passive** — gathering information that is already public without sending intrusive traffic to the target.

This lesson is conceptual and defensive in emphasis. It teaches *what categories of information exist and what risk they reveal*, so you understand your own organisation's exposure as an attacker would see it — not a cookbook of tools or commands. Everything here is still bounded by the authorisation, scope and legal frame from the first module: reconnaissance is part of the engagement, not a free-for-all.

## Passive vs active reconnaissance

| | Passive reconnaissance | Active reconnaissance |
|---|---|---|
| What it does | Collects already-public information | Sends traffic to / interacts with the target |
| Target awareness | The target usually cannot tell | The target can detect and log it |
| Examples (categories) | Search engines, public registries, leaked-data checks | Port scanning, service probing (the next module) |
| Risk of disruption | Essentially none | Can disrupt fragile systems |

The distinction matters for two reasons. First, **stealth and safety**: passive work does not touch the target, so it cannot disrupt it and is the natural first step. Second, and more importantly here, **scope and authorisation**: the moment you *interact* with a system (active recon) you are performing an intrusive action that must be in scope and permitted by the rules of engagement. Passive gathering of public data is lower-risk, but — as below — it is not lawless either.

:::note The line between passive and active is a scope decision
Querying a public DNS registry is passive; connecting to the target's own DNS server to enumerate records is active. Reading a company's public job posts is passive; logging into a portal is active. When you are unsure which side of the line an action falls on, treat it as active and confirm it is in scope and authorised before doing it.
:::

## Categories of OSINT

**Open-source intelligence (OSINT)** is information gathered from publicly available sources. A tester (and an attacker) draws on recognisable categories:

- **Organisational footprint** — the company's own websites, subsidiaries, brands, office locations, and the technologies it advertises using.
- **Domain and network registration** — public WHOIS/registry data, DNS records, IP ranges and certificate-transparency logs that reveal hostnames and infrastructure.
- **Technology fingerprint** — publicly visible indicators of the software, frameworks and services a site runs (from headers, public pages, job descriptions).
- **People and organisational structure** — public staff listings, professional networks and conference talks that reveal roles, reporting lines and who holds sensitive access.
- **Published documents and metadata** — public PDFs, spreadsheets and images whose metadata can leak usernames, software versions or internal paths.
- **Code and cloud exposure** — public code repositories, misconfigured public storage, and forgotten subdomains.
- **Leaked-credential exposure** — whether the organisation's email addresses appear in known public breach datasets (checked through reputable breach-notification services).

Each category is also a **defensive inventory**: a blue team uses the same sources to find what it is inadvertently exposing.

## Mapping the external attack surface

The **attack surface** is the sum of points where an attacker could attempt to interact with the organisation. Passive recon assembles it from the categories above:

```text
        EXTERNAL ATTACK SURFACE (from public data)
   ┌──────────────┬───────────────┬──────────────────┐
   │ Domains &    │ Exposed       │ People &         │
   │ subdomains   │ services /    │ credentials      │
   │ (DNS, certs) │ technologies  │ (staff, leaks)   │
   └──────┬───────┴───────┬───────┴────────┬─────────┘
          └───────────────┼────────────────┘
                          ▼
             a prioritised list of where the
             organisation is most exposed
```

The output is not an attack — it is a **map**: the internet-facing domains and hosts, the technologies they appear to run, the people who might be targeted (for example by phishing, which you saw as an attack vector in the SOC path), and any exposure already visible in public breach data. That map feeds the later assessment phase: it tells you where to look, and it tells a defender what to shut down, monitor or protect first.

:::scenario Reconnaissance as a defensive mirror
A tester maps a client and finds: a forgotten `staging.` subdomain still resolving to a live server; a public document whose metadata names an internal software version; and several staff emails appearing in old breach datasets. None of this required touching the target's systems — it was all public. The value to the client is immediate and defensive: retire the staging host, scrub document metadata before publishing, and force password resets plus MFA for the exposed accounts. Recon's first product is a to-do list for the defender.
:::

## The risks reconnaissance reveals

Passive recon repeatedly surfaces the same classes of organisational risk — the ones a defender should close:

- **Forgotten exposure** — stale subdomains, old test or staging systems, decommissioned-but-still-live hosts. Attackers love what defenders have stopped watching.
- **Information leakage** — document metadata, verbose error pages, and public repositories revealing internal detail (versions, paths, even secrets accidentally committed).
- **Oversharing by people** — detailed public profiles and posts that help an attacker craft convincing phishing or impersonation.
- **Credential exposure** — reused passwords from past breaches enabling credential-stuffing (the credential-abuse vector from the SOC path).

For each, the defensive remedy is concrete: inventory and retire stale assets, strip metadata and lock down repositories, train staff on what not to overshare, and monitor for leaked credentials while enforcing MFA. Reconnaissance, read defensively, is simply an external attack-surface assessment — and reducing that surface is one of the cheapest security wins an organisation can make.

## Authorisation, scope and the law

Passive recon feels low-risk, but it is still governed:

- **Scope still applies.** Gather information about the organisation you are authorised to test; do not wander into unrelated third parties, partners or individuals outside the engagement.
- **Do not cross into active without authorisation.** Collecting public data is passive; the moment you probe or log in, you need explicit permission and in-scope targets.
- **Respect privacy and data-protection law.** OSINT about *people* is personal data; handle it lawfully, minimally and only for the authorised purpose (the privacy principles from the GRC path apply). Do not collect more than the engagement needs, and protect what you gather.
- **Handle findings securely.** A recon package is a map of how to attack the client — store and transmit it like the sensitive document it is (as in the reporting lesson).

:::warning Public does not mean unrestricted
That information is publicly accessible does not mean any use of it is lawful or in scope. Accessing a "public" dataset through a flaw, scraping in violation of terms, or gathering personal data beyond the authorised purpose can cross legal lines even without touching the target's servers. When in doubt, confirm with the engagement owner before proceeding.
:::

## Common mistakes

- **Confusing passive with active.** Probing or logging in is active and needs in-scope authorisation; do not slip across the line because it "felt like research".
- **Wandering out of scope.** Recon on partners, subsidiaries or individuals outside the engagement is a scope and legal problem.
- **Mishandling personal data.** OSINT on people is personal data; collecting too much or handling it carelessly breaches privacy obligations.
- **Treating recon as trivia.** Its real output is a defensive to-do list — prioritise and hand it over, do not just collect.
- **Insecure storage.** A reconnaissance package is a sensitive attack map; protect it.

## Security implications

- Start passive: build the picture from public data before any interaction, which is safest and the natural first step.
- Map the external attack surface across domains, exposed technology, people and leaked credentials — the same inventory a defender needs.
- Convert findings into concrete defensive actions: retire stale assets, strip metadata, train people, enforce MFA and monitor for leaks.
- Keep every step within scope, authorisation and privacy law, and protect the sensitive recon output.

## Knowledge check

:::check Questions
1. What is the key difference between passive and active reconnaissance, and why does it matter for scope?
2. Name three categories of OSINT a tester might use.
3. Give two concrete defensive actions a client could take from a typical recon map.
4. Why must OSINT about people be handled with special care?
:::

:::solution Answers
1. **Passive** reconnaissance collects already-public information without sending traffic to the target; **active** reconnaissance interacts with the target (and can be detected and can disrupt). It matters for scope because the moment you interact (active) you are performing an intrusive action that must be explicitly in scope and authorised, whereas passive gathering is lower-risk (though still governed).
2. Any three of: organisational footprint; domain/network registration (WHOIS, DNS, certificate transparency); technology fingerprint; people/organisational structure; published documents and metadata; code and cloud exposure; leaked-credential exposure.
3. Any two of: retire forgotten/stale subdomains and test systems; strip metadata from public documents and lock down public repositories; train staff against oversharing/phishing; force password resets and enforce MFA for accounts found in breach data; monitor for leaked credentials.
4. Because OSINT about people is **personal data**, subject to privacy and data-protection law: it must be collected minimally, only for the authorised purpose, handled lawfully, and protected — gathering too much or mishandling it breaches those obligations even though the data is public.
:::

## Exercise

Imagine you are authorised to assess a fictional company's external exposure (passive only).

1. List **four OSINT categories** you would review and, for each, one example of what you might find and why it matters.
2. Sketch the **attack-surface map** you would produce (domains/hosts, exposed technology, people, credential exposure) as a short structured list.
3. For three of your findings, give the **defensive remediation** you would recommend to the client.
4. State two **scope or legal limits** you would observe throughout.

:::solution Model answer (abbreviated)
1. *Domain/network registration* → a forgotten `vpn-old.company.com` still resolving (stale exposure). *Technology fingerprint* → a public page revealing an outdated framework version (patch target). *People* → staff emails and roles on a professional network (phishing targeting). *Leaked credentials* → several company emails in old breach datasets (credential-stuffing risk).
2. *Map:* **Domains/hosts** — main site, `vpn-old.`, `staging.`; **Technology** — web framework vX, mail provider; **People** — IT admin and finance staff identifiable; **Credentials** — 4 emails in breach data.
3. Retire/secure `vpn-old.` and `staging.`; upgrade or patch the outdated framework; force resets and enforce MFA on the breached accounts (and monitor for leaks).
4. I would (a) gather information only about the authorised organisation, not partners or unrelated individuals; and (b) treat any personal data minimally and lawfully, collecting only what the engagement needs and protecting the recon package as sensitive.
:::

## Summary and next steps

- Reconnaissance builds the picture of a target; its first phase is **passive** — public data only, no intrusive traffic, the natural and safest start.
- **OSINT** spans organisational footprint, domain/network registration, technology fingerprint, people, documents/metadata, code/cloud exposure and leaked credentials.
- The product is an **external attack-surface map** that doubles as a defender's to-do list: retire stale assets, strip leakage, train people, enforce MFA.
- Passive does not mean unrestricted — stay within scope and authorisation, respect privacy law, and protect the sensitive output.

The next module, **Network Enumeration**, moves from passive to *active* reconnaissance — interacting with in-scope targets to enumerate services — performed only within explicit authorisation and, where exploitation follows, in isolated lab environments.

===assignment===
For an organisation of your choice (or a fictional one you define), produce a **passive-recon plan and attack-surface map** on paper: the OSINT categories you would review, a structured attack-surface map (domains, technology, people, credential exposure), and — most importantly — a prioritised **defensive remediation list** derived from it. Add a short paragraph stating the scope boundaries and the privacy/legal limits you would observe, and how you would protect the resulting package.

===criteria===
- Complete the reading and the knowledge check, and produce the plan, map and remediation list.
- Your work must stay strictly passive/conceptual (no instructions to probe or access systems), convert findings into concrete defensive actions, and explicitly state scope, privacy and handling limits.
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- OSINT Framework (categories of open-source intelligence) — https://osintframework.com/
- NIST SP 800-115, *Technical Guide to Information Security Testing and Assessment* (reconnaissance phase) — https://csrc.nist.gov/pubs/sp/800/115/final
- MITRE ATT&CK, Reconnaissance tactic (TA0043) — https://attack.mitre.org/tactics/TA0043/
- Certificate Transparency (public certificate logs) — https://certificate.transparency.dev/
- Have I Been Pwned (reputable breached-credential notification) — https://haveibeenpwned.com/
