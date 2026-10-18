---
title: The CIA Triad and Core Security Principles
chapter: Security principles
summary: Learn the three properties every security control protects, and the design principles — least privilege, defence in depth, zero trust — that SOC analysts use to reason about alerts.
duration: 40
level: beginner
preview: true
objectives:
- Define confidentiality, integrity and availability and give an operational example of each
- Classify a security incident by the property it primarily affects
- Explain least privilege, separation of duties, defence in depth and zero trust
- Use the CIA triad to prioritise alerts in a SOC queue
prerequisites:
- Basic familiarity with computers, networks and user accounts
---

## Introduction

Every alert that reaches a Security Operations Center (SOC) ultimately asks one question: *is something we care about being harmed?* To answer it quickly, analysts need a shared vocabulary for what "harm" means. The **CIA triad** — Confidentiality, Integrity and Availability — is that vocabulary. It is deliberately simple, and that simplicity is its strength: it lets a new analyst and a CISO describe the same incident in the same terms.

In this lesson you will learn the three properties, the classic principles used to protect them, and how a SOC uses them to triage work.

## The three properties

### Confidentiality

Confidentiality means information is disclosed **only to authorised people, processes or systems**. It is violated when data is read, copied or exposed by someone who should not have it — whether an external attacker or an over-privileged employee.

Typical confidentiality controls:

- Access control lists and role-based access control (RBAC)
- Encryption at rest (disk, database) and in transit (TLS)
- Data loss prevention (DLP) and classification labels
- Multi-factor authentication protecting accounts that can read data

Operational example: a SOC receives an alert that 4 GB was uploaded from a finance file server to a personal cloud storage domain at 02:00. If confirmed, this is primarily a **confidentiality** incident.

### Integrity

Integrity means information and systems are **accurate, complete and changed only in authorised ways**. Attacks on integrity are often quieter than data theft: an attacker who alters payroll bank details, edits audit logs, or tampers with a software update may cause far more damage than one who simply reads data.

Typical integrity controls:

- Hashing and digital signatures (code signing, signed updates)
- Change management and approval workflows
- File integrity monitoring (FIM)
- Write-once or append-only log storage
- Database constraints and input validation

Operational example: Sysmon shows `C:\Windows\System32\drivers\etc\hosts` was modified by a non-administrative process, redirecting a banking domain to an unknown IP address. This is primarily an **integrity** incident.

### Availability

Availability means systems and data are **accessible to authorised users when needed**. Ransomware, distributed denial of service (DDoS), destructive malware and even misconfigurations threaten availability.

Typical availability controls:

- Redundancy, clustering and load balancing
- Tested backups with offline or immutable copies
- DDoS protection and rate limiting
- Capacity monitoring and patching windows

Operational example: hundreds of file-rename events with a new `.lockd` extension appear on a file server within two minutes. This is an **availability** incident (and likely a confidentiality one too, because modern ransomware groups usually exfiltrate data before encrypting).

:::note Incidents rarely affect only one property
Classify by the **primary** impact for routing and severity, but record secondary impacts. Ransomware with data theft is an availability *and* confidentiality incident, and the response plan must cover both.
:::

## Beyond CIA: authenticity, accountability and non-repudiation

Many frameworks extend the triad:

| Property | Meaning | SOC relevance |
|---|---|---|
| Authenticity | An identity or message is genuinely what it claims to be | Phishing, spoofed senders, forged tokens |
| Accountability | Actions can be traced to a responsible entity | Requires reliable, centralised logging |
| Non-repudiation | An actor cannot plausibly deny an action | Signed transactions, tamper-evident logs |

Accountability is especially important for SOC work: **if an action is not logged, it cannot be investigated.** Log coverage is therefore a security control in its own right.

## Core design principles

### Least privilege

Every user, service and process should have **only the permissions needed for its task, for only as long as needed.** Least privilege limits the "blast radius" of a compromised account.

In practice:

- Standard user accounts for daily work; separate admin accounts used only for administration
- Service accounts scoped to specific resources, not Domain Admins
- Just-in-time (JIT) elevation that expires automatically

SOC signal: a helpdesk account suddenly added to `Domain Admins` (Windows Event ID **4728** / **4732** / **4756** depending on group scope) violates least privilege and deserves immediate attention.

### Separation of duties

No single person should be able to complete a sensitive action alone. The person who requests a payment should not approve it; the engineer who writes a detection rule should not be the only reviewer who deploys it. Separation of duties defends against both fraud and honest mistakes.

### Defence in depth

Assume any single control can fail, and layer independent controls so one failure is not catastrophic:

:::diagram Defence in depth: independent layers between the internet and the data
```text
 Internet
    │
 [ Email gateway ]  ← blocks known-bad attachments
    │
 [ Endpoint protection / EDR ]  ← detects malicious execution
    │
 [ Least-privilege account ]  ← limits what malware can reach
    │
 [ Network segmentation ]  ← stops lateral movement
    │
 [ SIEM detection + SOC ]  ← notices what slipped through
    │
 [ Backups ]  ← restores availability if all else fails
```
:::

For a SOC analyst, defence in depth explains why **an alert from one layer is a reason to check the others.** If EDR blocked a payload, did the email gateway miss it? Did the user also enter credentials on a phishing page?

### Zero trust

Zero trust replaces "trusted inside, untrusted outside" with **"never trust, always verify."** Every access request is evaluated on identity, device health, location and context — even from the internal network. NIST SP 800-207 describes the architecture formally.

SOC signal: in a zero-trust environment, *sign-in risk* and *device compliance* become first-class telemetry. A successful sign-in from a non-compliant device in an unusual country is meaningful even if the password was correct.

### Secure defaults and fail securely

Systems should start in a secure state and, when they fail, fail closed (deny access) rather than open. A firewall that allows all traffic when its rule engine crashes is not failing securely.

## Using the triad to triage alerts

A SOC queue might contain dozens of alerts at the start of a shift. A disciplined analyst asks, for each:

1. **Which property is at risk?** (C, I, A — possibly several)
2. **What asset is involved, and how critical is it?** A domain controller outranks a kiosk PC.
3. **What is the evidence quality?** A single low-fidelity signal versus multiple correlated signals.
4. **Is the activity ongoing?** Active encryption or exfiltration is time-critical.

:::scenario Worked example: three alerts, one analyst
**Alert A** — Ten failed logons for a marketing user from an internal IP, then nothing.
**Alert B** — A backup server reports its scheduled job failed because the backup volume is "in use".
**Alert C** — An EDR alert: `vssadmin.exe delete shadows /all /quiet` executed on a file server by a service account.

Alert C is the clear priority. Deleting Volume Shadow Copies is a classic ransomware preparation step (MITRE ATT&CK **T1490 — Inhibit System Recovery**) and targets **availability** on a critical asset. Alert B may be related — check whether backups are being tampered with. Alert A is most likely a user who forgot a password; it can wait, but should still be closed with a note.
:::

## Common mistakes

- **Treating confidentiality as the only property that matters.** Integrity attacks (log tampering, fraud) are often discovered late precisely because teams do not look for them.
- **Ignoring availability incidents caused by your own controls.** An overly aggressive automated block that takes down a production service is still an availability incident.
- **Over-privileged service accounts "because it works".** These are a top cause of lateral movement in real breaches.
- **Assuming the internal network is safe.** Most serious incidents involve an attacker who is already inside.

## Security implications for defenders

- Map each critical asset to the property that matters most to the business; this drives alert severity.
- Treat logging pipelines as integrity-critical: if attackers can delete logs, accountability disappears. Forward logs off-host quickly.
- Review privileged group membership continuously, not annually.

## Knowledge check

:::check Questions
1. An attacker changes the destination account number in a pending wire transfer. Which property is primarily affected?
2. Why is logging considered a security control?
3. Give one example of defence in depth that applies to phishing.
4. What does "fail securely" mean for an authentication service?
:::

:::solution Answers
1. **Integrity** — data was modified without authorisation. Confidentiality may not be affected at all.
2. Logs provide **accountability**: without them, actions cannot be attributed or investigated, and detections cannot be built.
3. Email filtering, user awareness training, MFA (so stolen passwords alone are insufficient), EDR on endpoints, and SOC monitoring of risky sign-ins — each layer catches what the previous one missed.
4. If the service cannot verify a user (for example, its database is unreachable), it should **deny** access rather than allow it.
:::

## Exercise

Classify each event by its primary CIA property, and name one control that would have prevented or detected it.

1. A laptop containing unencrypted customer records is stolen from a car.
2. An administrator accidentally deletes a production database with no recent backup.
3. Malware modifies the `Run` registry key to start at every logon.
4. A DDoS attack makes the customer portal unreachable for three hours.

:::solution Model answer
1. **Confidentiality** — full-disk encryption (BitLocker/FileVault) would have prevented disclosure.
2. **Availability** (and integrity of the data set) — tested, versioned backups; change control and separation of duties for destructive commands.
3. **Integrity** of the system configuration — EDR or Sysmon Event ID 13 (registry value set) monitoring of autorun keys; application allow-listing.
4. **Availability** — DDoS protection service, rate limiting, and an incident runbook for traffic diversion.
:::

## Summary and next steps

- The CIA triad gives the SOC a shared language for impact.
- Least privilege, separation of duties, defence in depth and zero trust are the design principles you will see reflected in every control.
- Classifying alerts by affected property and asset criticality is the first step of disciplined triage.

Next, you will extend this vocabulary to **threats, vulnerabilities, risks and controls** — the terms you need to explain *why* an alert matters to the business.

===assignment===
Choose an organisation you know (your employer, school, or a fictional company). Identify **three critical assets**, state which CIA property matters most for each and why, and propose **one preventive and one detective control** per asset. Write no more than one page; you will reuse this in the risk-management lesson.

===criteria===
- Read the full lesson and attempt the knowledge check before revealing the answers.
- Complete the classification exercise.
- Mark the lesson complete; the module quiz and the course final exam assess these concepts.

===references===
- NIST SP 800-12 Rev. 1, *An Introduction to Information Security* — https://csrc.nist.gov/pubs/sp/800/12/r1/final
- NIST SP 800-207, *Zero Trust Architecture* — https://csrc.nist.gov/pubs/sp/800/207/final
- MITRE ATT&CK T1490, Inhibit System Recovery — https://attack.mitre.org/techniques/T1490/
- Microsoft, *Security auditing events for group membership changes* — https://learn.microsoft.com/windows/security/threat-protection/auditing/event-4728
