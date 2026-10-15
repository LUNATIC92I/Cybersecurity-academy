---
title: The Incident Response Lifecycle
chapter: Running an incident
summary: Apply the NIST incident response lifecycle from preparation to lessons learned — classify and scope incidents, choose containment strategies, preserve evidence, build timelines and communicate with stakeholders.
duration: 65
level: intermediate
objectives:
- Describe each phase of the incident response lifecycle and its outputs
- Classify incidents by type and severity using impact and urgency
- Choose containment actions that balance business impact against evidence preservation
- Maintain an incident timeline and chain of custody for evidence
- Write an executive summary and run a blameless post-incident review
prerequisites:
- Windows Event Logs for Investigators
- Detecting Brute-Force and Password-Spraying Attacks
---

## Introduction

Detection is only valuable if it leads to effective response. Incident response (IR) is the disciplined process of limiting damage, removing the attacker, restoring operations and learning from what happened. Under pressure, structure is what prevents an incident from becoming a crisis: people know their roles, decisions are recorded, and evidence survives for later analysis or legal action.

This lesson follows the lifecycle described in NIST SP 800-61 (Computer Security Incident Handling Guide). Revision 3 (2025) aligns incident response with the NIST Cybersecurity Framework 2.0 functions; the classic four-phase model remains the clearest way to learn the work.

```text
 ┌──────────────┐   ┌────────────────────────┐   ┌──────────────────────────────┐   ┌───────────────────┐
 │ Preparation  │──►│ Detection & Analysis   │──►│ Containment, Eradication &   │──►│ Post-Incident     │
 │              │   │                        │◄──│ Recovery                     │   │ Activity          │
 └──────────────┘   └────────────────────────┘   └──────────────────────────────┘   └─────────┬─────────┘
        ▲                                                                                      │
        └──────────────────────────── lessons learned improve preparation ◄───────────────────┘
```

## Phase 1 — Preparation

Most of an incident's outcome is decided before it begins. Preparation includes:

- **Policy and authority**: who may declare an incident, isolate systems, or engage outside help.
- **Team and contacts**: IR roles (incident commander, lead investigator, scribe, communications, legal, IT liaisons), on-call rotation, out-of-band contact list (phone numbers that work if email is compromised).
- **Playbooks** for common incident types: phishing, malware, account compromise, ransomware, data leak, DDoS.
- **Tooling ready in advance**: EDR with isolation capability, forensic collection tools, a secure case-management system, clean admin workstations.
- **Logging and retention** sufficient to investigate weeks back.
- **Exercises**: tabletop exercises that test decisions, not just documents.

## Phase 2 — Detection and analysis

### Is it an incident?

An **event** is any observable occurrence. An **incident** is an event that violates, or imminently threatens to violate, security policies or acceptable use. Triage determines which events are incidents.

### Classification

Classify by **type** (what is happening) and **severity** (how bad it is):

| Type | Examples |
|---|---|
| Malware | Infection, ransomware, worm |
| Unauthorised access | Account compromise, privilege abuse |
| Data breach | Exfiltration, accidental exposure |
| Denial of service | DDoS, resource exhaustion |
| Insider misuse | Policy violation, data theft by employee |
| Web/application attack | Exploitation of a public-facing service |

A simple **severity matrix** combines impact and urgency:

| | Low urgency | Medium urgency | High urgency |
|---|---|---|---|
| **High impact** (critical systems, regulated data, many users) | High | Critical | Critical |
| **Medium impact** | Medium | High | High |
| **Low impact** (single non-critical asset) | Low | Low | Medium |

Severity drives response times, escalation and who must be informed.

### Scoping

Scoping answers: **which accounts, hosts, data and time range are affected?** Work outward from the initial indicator:

1. Pivot on the indicators you have: the compromised account, the malicious IP, the file hash, the C2 domain.
2. Search every relevant log source for those indicators across the full retention period.
3. Each new finding (another host, another account) becomes a new pivot.
4. Stop when pivots return nothing new — and document what you searched.

### The incident timeline

Keep a single, shared timeline from the first minute. Record **observed activity** (what the attacker did, in UTC) and **response actions** (what the team did) separately.

```text
Time (UTC)          Source            Observation / Action                                   By
2026-03-14 01:56:41 SecurityEvent     svc_backup logon from 10.0.9.77 after 38 failures      (attacker)
2026-03-14 01:58:05 Sysmon 3          upd.exe on FS-01 connects to 198.51.100.77:443         (attacker)
2026-03-14 02:20:00 SIEM              Incident INC-0142 created, severity High               Tier 1 (A. Diallo)
2026-03-14 02:31:00 EDR               FS-01 isolated from network                            Tier 2 (M. Rossi)
```

Distinguishing facts from hypotheses ("likely C2") keeps analysis honest.

## Phase 3 — Containment, eradication and recovery

### Containment

Goal: stop the damage from spreading while preserving the ability to investigate.

**Short-term containment** (minutes to hours):
- Isolate hosts using EDR network containment (keeps the agent connected for investigation)
- Disable or reset compromised accounts; revoke sessions and tokens
- Block malicious IPs/domains at firewall, proxy and DNS
- Remove malicious emails from all mailboxes

**Long-term containment** (while eradication is prepared):
- Temporary firewall rules, segmenting affected network zones
- Additional monitoring on likely next targets
- Rebuilding critical services in a clean environment

:::warning Containment trade-offs
Powering off a host destroys volatile evidence (memory, network connections, running processes). Alerting the attacker too early — for example, resetting one account while they control five others — may trigger destructive actions. Decide deliberately, and when possible **collect volatile evidence before disruptive containment**.
:::

### Evidence handling

- Prefer **forensic copies**; record **hash values** (SHA-256) of collected files and images.
- Maintain a **chain of custody**: what was collected, by whom, when, from where, how it was stored and every transfer.
- Collect in **order of volatility**: CPU/memory → network state → running processes → disk → logs and backups.
- Store evidence in access-controlled locations; never analyse live malware on production or personal machines.

### Eradication

Remove the attacker's presence and the root cause:

- Remove malware and persistence (services, scheduled tasks, Run keys, rogue accounts, OAuth grants)
- Patch the exploited vulnerability or fix the misconfiguration
- Reset credentials that may have been exposed — including service accounts and, after domain compromise, the `krbtgt` account (twice, with an interval)
- Rebuild systems where integrity cannot be assured — re-imaging is often faster and safer than cleaning

### Recovery

- Restore from **known-good backups**, verified as predating the compromise and scanned before reconnection
- Bring systems back in stages with **heightened monitoring**
- Confirm business owners accept the restored service
- Define the criteria for declaring the incident closed

## Communication and escalation

| Audience | Needs | Typical channel |
|---|---|---|
| Executives | Business impact, decisions required, timeline to recovery | Brief written updates at fixed intervals |
| IT operations | Specific actions and their timing | Incident bridge / ticket |
| Legal and privacy | Whether regulated data is involved; notification deadlines | Direct, privileged communication |
| Employees | What to do (or not do) | Approved internal announcement |
| Customers / regulators | Accurate, approved disclosures | Via legal and communications teams only |

Rules: **one voice** for external communications; **out-of-band channels** if email or chat may be compromised; communicate facts, clearly labelled uncertainties, and next update time.

:::note Regulatory notification
Many jurisdictions impose deadlines for notifying regulators or affected individuals of personal-data breaches (for example, 72 hours under the EU GDPR for notifying the supervisory authority, where feasible). The SOC's job is to escalate promptly to legal and privacy teams with accurate scoping facts; legal teams determine obligations.
:::

## Phase 4 — Post-incident activity

### The executive summary

A good executive summary fits on one page:

1. **What happened** — in plain language
2. **Impact** — systems, data, users, downtime, regulatory implications
3. **Root cause** — how the attacker got in and why it worked
4. **Response** — key actions and timeline
5. **Current status** — contained, eradicated, recovered?
6. **Recommendations** — prioritised improvements with owners

### The lessons-learned review

Hold a **blameless** review within two weeks. Ask:

- What happened, and when did we know?
- What went well? What slowed us down?
- Which detections fired, which should have, and why didn't they?
- What would have prevented or limited the incident?
- Which actions will we take, who owns them, and by when?

Track metrics such as time to detect, time to contain and time to recover, and feed improvements back into preparation.

## Common mistakes

- **No scribe** — decisions and timestamps are lost.
- **Destroying evidence** by re-imaging before collection.
- **Containing piecemeal** while the attacker retains other footholds.
- **Declaring victory early** — attackers often keep backup access; monitor after recovery.
- **Blame-focused reviews** — people stop reporting mistakes, and the organisation stops learning.

## Knowledge check

:::check Questions
1. What distinguishes an event from an incident?
2. Why should volatile evidence be collected before powering off a host?
3. What is a chain of custody?
4. Name three elements of a good executive summary.
:::

:::solution Answers
1. An incident is an event that violates or imminently threatens security policy; most events are benign.
2. Memory, network connections and running processes are lost at power-off and may hold the only evidence of the attacker's tools and activity.
3. A documented record of who collected, handled, stored and transferred each piece of evidence, when and how — proving it has not been altered.
4. Any three of: what happened, impact, root cause, response actions, current status, recommendations.
:::

## Exercise

At 09:10 a user reports their mailbox sent invoices they did not write. Investigation shows: a sign-in to their account from an unfamiliar country at 03:12; a new inbox rule moving replies containing "invoice" to an RSS folder; and 140 outgoing emails to customers between 03:20 and 03:40.

1. Classify the incident type and severity, with justification.
2. List four containment actions in the order you would perform them.
3. Which evidence would you collect, and from where?
4. Draft two sentences for the executive update.

:::solution Model answer
1. **Unauthorised access / business email compromise.** Severity **High**: customer-facing fraud with financial and reputational impact; urgency high because fraudulent payments may be in progress.
2. (a) Revoke all sessions and reset the password; (b) remove the malicious inbox rule and review mailbox forwarding; (c) block the sign-in source and require MFA re-registration; (d) recall or warn recipients of the fraudulent invoices with the communications team.
3. Entra ID sign-in and audit logs (SigninLogs, AuditLogs), mailbox audit logs and message trace (OfficeActivity), the inbox rule configuration, and copies of the fraudulent emails with headers — all hashed and recorded in the chain of custody.
4. "A staff mailbox was accessed by an unauthorised party overnight and used to send fraudulent invoices to 140 customers. Access has been removed, customers are being warned not to pay, and we are confirming whether any other accounts or data were affected; next update at 13:00."
:::

## Summary and next steps

- IR is a cycle: preparation → detection and analysis → containment, eradication and recovery → lessons learned.
- Classification and scoping drive the response; the timeline and chain of custody keep it rigorous.
- Communication is part of the response, not an afterthought.

Use this lifecycle in every lab investigation from now on, and attempt the SOC final exam when your required lessons and labs are complete.

===assignment===
Write an **incident report** for the FS-01 scenario from the Windows Event Logs lesson: executive summary (one page), detailed timeline, scoping method, containment and eradication actions, evidence list with hashes (you may use placeholder hash values clearly marked as such), root cause and five prioritised recommendations with owners.

===criteria===
- Complete the reading, knowledge check and BEC exercise.
- Mark the lesson complete; the course final exam includes incident response scenarios.

===references===
- NIST SP 800-61 Rev. 3, *Incident Response Recommendations and Considerations for Cybersecurity Risk Management* — https://csrc.nist.gov/pubs/sp/800/61/r3/final
- NIST SP 800-86, *Guide to Integrating Forensic Techniques into Incident Response* — https://csrc.nist.gov/pubs/sp/800/86/final
- RFC 3227, *Guidelines for Evidence Collection and Archiving* — https://www.rfc-editor.org/rfc/rfc3227
- CISA, *Federal Government Cybersecurity Incident and Vulnerability Response Playbooks* — https://www.cisa.gov/resources-tools/resources/federal-government-cybersecurity-incident-and-vulnerability-response-playbooks
