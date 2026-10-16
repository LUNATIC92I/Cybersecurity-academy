---
title: Identity and Cloud Monitoring
chapter: Identity and cloud monitoring
summary: Monitor the modern perimeter — identity and cloud. Learn to read sign-in and audit logs from identity providers and cloud platforms, recognise account compromise, MFA attacks, risky OAuth grants and cloud misconfiguration, and understand the shared-responsibility model that decides what you must watch.
duration: 50
level: intermediate
preview: true
objectives:
- Explain why identity is the modern security perimeter
- Read identity-provider sign-in and audit logs for compromise signals
- Recognise MFA fatigue, impossible travel, token theft and risky OAuth consent
- Describe what cloud audit logs capture and the shared-responsibility model
- Detect common cloud misconfigurations and control-plane abuse, defensively
prerequisites:
- SIEM Operations — The Log Pipeline, Normalisation and Alerting
---

## Introduction

The earlier SOC lessons watched hosts and networks. But modern organisations run on **identity** and **cloud**: staff sign in to SaaS and cloud consoles from anywhere, and the old network perimeter barely contains the data. The consequence for a SOC is blunt — **identity is the new perimeter**, and the logs that matter most are often the identity provider's sign-in records and the cloud platform's audit trail, not a firewall. This lesson teaches you to read those sources and recognise the attacks specific to them. It is defensive throughout: understanding how identity and cloud are abused so you can detect and stop it.

Everything here flows into the SIEM you learned about: identity and cloud are just more sources to collect, normalise and detect on — but they carry the organisation's keys.

## Identity is the perimeter

When applications live in the cloud, an attacker rarely needs to breach a network — they need an **account**. A single compromised identity, especially a privileged one, can reach mail, files, and cloud infrastructure from anywhere on the internet. So the **identity provider (IdP)** — the system that authenticates users (e.g. a cloud directory / SSO) — is both the most valuable target and the richest monitoring source.

Two log types from an IdP matter most:

- **Sign-in logs** — every authentication attempt: who, when, from where (IP, geolocation, device), success or failure, the error/result code, and whether MFA was satisfied.
- **Audit logs** — every administrative change: a user created, a role granted, MFA reset, an application consented to, a policy changed.

These are the identity equivalent of the Windows/Linux auth logs you already read — the same "who authenticated, from where, and what did they then do?" questions, at cloud scale.

## Reading sign-ins: the compromise signals

Account compromise leaves recognisable patterns in sign-in logs:

- **Failure bursts then success** — the brute-force / password-spraying pattern from the detection module, now against the IdP (many failures with credential-error codes, then a success).
- **Impossible travel** — successful sign-ins from two locations too far apart to travel between in the elapsed time (e.g. London then Singapore 20 minutes later) — a strong token/credential-theft signal.
- **Sign-in from unusual infrastructure** — anonymising proxies, hosting-provider IPs, or a country the user never works from, especially for a privileged account.
- **Legacy / non-MFA authentication** — a sign-in that bypassed MFA via an old protocol is suspicious where policy requires MFA.

The highest-value correlation is the one from triage: **many failures then a success, from a new location, with no MFA challenge** on a privileged account is a near-certain compromise — enrich and escalate.

### MFA attacks

MFA is essential but not magic; attackers target it directly:

- **MFA fatigue / push bombing** — the attacker, holding a valid password, triggers repeated MFA push prompts hoping the user approves one out of annoyance. *Signal:* many MFA requests in a short window, then an approval. *Defence:* number-matching MFA, push throttling, and user training.
- **Token / session theft** — stealing a session token (e.g. via phishing a reverse-proxy) lets the attacker skip MFA entirely by replaying the authenticated session. *Signal:* a session used from a new IP/device without a fresh authentication; impossible travel on a token.
- **SIM swap / weak factors** — SMS codes are phishable and interceptable; their presence on privileged accounts is itself a weakness.

:::tip MFA satisfied is not the same as MFA secure
"MFA was satisfied" in a sign-in log does not mean the sign-in is safe — it could be an approved fatigue prompt or a replayed stolen token. Read *how* MFA was satisfied (a fresh number-match vs a reused session) and correlate with location and device. An approval immediately after a burst of push prompts is the fatigue attack, not a legitimate login.
:::

## Risky OAuth consent and app grants

Beyond logins, cloud identity has a second abuse path: **application consent**. A user can grant a third-party application access to their mailbox, files or directory via OAuth. Attackers abuse this with **illicit consent grants** — a malicious app that, once consented to, has persistent access to data *without needing the password or MFA again*.

- **Signal:** audit-log events for a new application consent, especially one requesting broad permissions (read all mail, access all files) granted by a regular user; a newly registered app receiving tokens.
- **Why it matters:** this is persistence and data access that survives a password reset — so it is easy to miss if you only watch logins. *Defence:* restrict who can consent to apps, review app grants, and alert on high-privilege consents.

This is the cloud analogue of the persistence mechanisms from the Linux-logs lesson: the attacker establishes a foothold that outlives the initial compromise.

## Cloud platform monitoring and shared responsibility

Cloud infrastructure (IaaS/PaaS) adds the **control plane** — the API and console through which everything is created, changed and deleted. Every cloud platform records this in an **audit/activity log** (the cloud equivalent of an audit trail): who made which API call, when, from where, and whether it succeeded.

### The shared-responsibility model

Crucially, the cloud provider secures *some* layers and the customer secures others — the **shared-responsibility model** — and this decides **what the SOC must monitor**:

```text
            CLOUD PROVIDER secures          CUSTOMER secures (you monitor)
 IaaS   │  physical, host, hypervisor   │  OS, network config, IAM, data, app
 PaaS   │  + OS and runtime             │  IAM, config, data, app
 SaaS   │  + application                │  identity, access, data sharing, config
```

The provider will not tell you that an employee made a storage bucket public or granted an over-broad role — that is **your** responsibility, visible in **your** audit logs. Misreading this model (assuming "the cloud is secure") is how breaches happen; the SOC monitors the layers the customer owns.

### What to watch in cloud audit logs

- **IAM changes** — a new access key, a role or policy granting broad/admin permissions, a new privileged user, cross-account trust changes. Control-plane privilege escalation is the cloud's version of the escalation you saw on hosts.
- **Misconfiguration** — storage made public, security-group/firewall rules opened to the internet, logging or monitoring being disabled (an attacker blinding you — the tampering signal again).
- **Anomalous API activity** — a burst of enumeration calls, resource creation in unusual regions (e.g. spinning up compute for crypto-mining), mass data access or export.
- **Root/break-glass account use** — the most privileged cloud accounts should almost never be used; any activity is high-signal.

:::warning Someone disabling logging is an attack, not an accident
In both identity and cloud, an audit-log event that *turns off* logging, deletes a log sink, or reduces monitoring is one of the highest-signal events you can see — it is often an attacker covering their tracks (the "absence of logs is evidence" principle from the Linux-logs lesson, at cloud scale). Alert on changes to the logging and monitoring configuration itself, and protect those settings.
:::

## Bringing it into the SOC

Identity and cloud are just more sources for the SIEM pipeline — but high-value ones:

- **Collect** IdP sign-in and audit logs and cloud control-plane logs via their APIs (the collection stage from the SIEM lesson).
- **Normalise** them to the common schema (`user`, `src_ip`, `action`, `result`) so identity events correlate with host and network events — a sign-in from a bad IP ties to that IP's activity elsewhere.
- **Detect** the patterns above as rules; **enrich** with geolocation, user role and device; **prioritise** by privilege (a change to an admin account outranks a normal user).
- **Map** to MITRE ATT&CK cloud techniques so you can see your identity/cloud coverage gaps (from the threat-intel lesson).

## Common mistakes

- **Still thinking the network is the perimeter.** Identity is; the IdP's logs are often the most important source you have.
- **Trusting "MFA satisfied".** Fatigue approvals and replayed tokens satisfy MFA; read how it was satisfied and correlate.
- **Ignoring OAuth/app consent.** Illicit grants are persistence that survives password resets and are missed if you only watch logins.
- **Misreading shared responsibility.** The provider does not secure your IAM, config and data; those are yours to monitor.
- **Not alerting on logging changes.** Disabling cloud/identity logging is an attacker blinding you — one of the highest-signal events.

## Security implications

- Treat the identity provider as the primary perimeter and its sign-in/audit logs as top-tier sources.
- Detect account-compromise patterns (failure-then-success, impossible travel, unusual infra) and MFA attacks (fatigue, token theft), reading *how* MFA was satisfied.
- Monitor OAuth/app consent for illicit grants — persistence that outlives a password reset.
- Know the shared-responsibility model and watch the customer-owned layers: IAM changes, misconfiguration, anomalous API activity, and any disabling of logging.

## Knowledge check

:::check Questions
1. Why is identity described as the modern perimeter, and which IdP logs matter most?
2. What is impossible travel, and why is it a strong compromise signal?
3. Why can an attacker with a stolen session token or an MFA-fatigue approval still get in despite MFA?
4. Under the shared-responsibility model, who is responsible for a storage bucket being made public, and where would the SOC see it?
:::

:::solution Answers
1. Because cloud/SaaS applications are reachable from anywhere with an **account**, so a compromised identity — not a breached network — is the usual path in. The **identity provider's sign-in logs** (authentication attempts, location, MFA) and **audit logs** (admin changes, role grants, app consents) matter most.
2. **Impossible travel** is two successful sign-ins from locations too far apart to travel between in the elapsed time (e.g. London then Singapore 20 minutes later). It strongly indicates credential or token theft, because one identity cannot physically be in both places.
3. Because **MFA fatigue** (the user approves a repeated push the attacker triggered) and **token/session theft** (replaying a stolen authenticated session) both result in a sign-in where "MFA was satisfied" without the attacker proving a fresh factor — MFA being satisfied is not the same as the sign-in being legitimate.
4. The **customer** is responsible (it is a configuration/data-layer action, not the provider's infrastructure), and the SOC would see it in the **cloud platform's audit/activity log** as an API call that changed the bucket's access to public.
:::

## Exercise

For an organisation using a cloud IdP (SSO) and a cloud platform:

1. List **three identity sign-in signals** and **two identity audit-log signals** you would alert on, and why.
2. Describe how you would distinguish a **legitimate MFA approval** from an **MFA-fatigue attack** in the logs.
3. Give **two cloud control-plane events** you would treat as high-signal, mapping each to the risk it represents.
4. Explain, using the shared-responsibility model, one thing the SOC must monitor that the cloud provider will not.

:::solution Model answer (abbreviated)
1. *Sign-in:* failure-burst-then-success (likely brute force/spray); impossible travel (token/credential theft); sign-in from an anonymising proxy or new country on a privileged account. *Audit:* a new high-privilege role grant; a new OAuth app consent requesting broad mailbox/file access.
2. A legitimate approval is a single prompt the user expected, from their usual device/location; a fatigue attack shows **many MFA prompts in a short window then an approval**, often with the password having been entered from an unfamiliar IP — correlate the approval with the burst and the source.
3. A new admin/over-broad IAM role or access key (control-plane privilege escalation); logging/monitoring being disabled or a log sink deleted (attacker covering tracks). (Also: public storage exposure; resource creation in unusual regions.)
4. Under shared responsibility the provider secures the infrastructure but the **customer** owns IAM, configuration and data — so the SOC must monitor, for example, **IAM role changes and public-exposure misconfigurations** in the customer's own audit logs; the provider will not alert on an employee granting an over-broad role.
:::

## Summary and next steps

- **Identity is the perimeter**: the IdP's sign-in and audit logs are often a SOC's most important sources.
- Detect account compromise (failure-then-success, impossible travel, unusual infra) and MFA attacks (fatigue, token theft), reading *how* MFA was satisfied, and watch **OAuth consent** for persistence.
- Cloud adds the **control plane**; the **shared-responsibility model** decides what you must monitor — IAM, configuration, data and any disabling of logging.
- Identity and cloud are high-value SIEM sources: collect, normalise, detect, enrich and map to ATT&CK like any other.

The final SOC module, **Advanced SOC Operations**, brings the whole path together — metrics, automation (SOAR), purple teaming and continuous improvement — all within the platform's defensive, responsible-use frame.

===assignment===
Design a one-page **identity-and-cloud monitoring plan** for an organisation of your choice: the IdP and cloud log sources you would collect; five detections across sign-ins, MFA, OAuth consent and cloud control-plane changes (each with the signal and why it matters); and a short explanation, using the shared-responsibility model, of which layers your SOC is responsible for monitoring. Map at least two detections to MITRE ATT&CK techniques and note how you would prioritise by privilege.

===criteria===
- Complete the reading and the knowledge check, and produce the plan.
- Detections must cover identity and cloud (not just logins), read MFA correctly, include a logging-tampering alert, and reflect the shared-responsibility model; at least two must map to ATT&CK.
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- MITRE ATT&CK Cloud Matrix — https://attack.mitre.org/matrices/enterprise/cloud/
- NIST SP 800-63, *Digital Identity Guidelines* (authentication) — https://pages.nist.gov/800-63-3/
- CISA, *Secure Cloud Business Applications (SCuBA)* guidance — https://www.cisa.gov/resources-tools/services/secure-cloud-business-applications-scuba-project
- Cloud Security Alliance, *Shared Responsibility Model* — https://cloudsecurityalliance.org/
- OWASP, *Credential Stuffing and account-takeover defence* — https://cheatsheetseries.owasp.org/cheatsheets/Credential_Stuffing_Prevention_Cheat_Sheet.html
