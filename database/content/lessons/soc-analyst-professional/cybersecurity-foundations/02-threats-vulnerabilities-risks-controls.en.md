---
title: Threats, Vulnerabilities, Risks and Controls
chapter: Security principles
summary: Distinguish the four terms that underpin every security conversation, recognise common attack vectors, and understand how a SOC fits into an organisation's control framework.
duration: 45
level: beginner
preview: true
objectives:
- Define threat, vulnerability, risk and control precisely and use them correctly
- Describe the most common initial attack vectors and the evidence each leaves
- Categorise controls by function (preventive, detective, corrective) and type (technical, administrative, physical)
- Explain the responsibilities of a SOC and its tiers within enterprise security operations
prerequisites:
- The CIA Triad and Core Security Principles
---

## Introduction

"We have a critical vulnerability." "This is a high risk." "The threat actor used phishing." These sentences are used daily — and frequently mixed up. A SOC analyst who uses the terms precisely writes clearer tickets, escalates more convincingly and is trusted by risk and audit teams. This lesson gives you that precision, then shows where the SOC sits in the organisation.

## Four terms, four different things

| Term | Definition | Example |
|---|---|---|
| **Asset** | Anything of value to the organisation | Customer database, payroll process, domain controller |
| **Threat** | A potential cause of an unwanted incident | A ransomware group; a careless insider; a flood |
| **Vulnerability** | A weakness that a threat can exploit | Unpatched VPN appliance; no MFA on email; weak password policy |
| **Risk** | The combination of likelihood that a threat exploits a vulnerability and the impact on an asset | "Likely compromise of email accounts leading to invoice fraud" |
| **Control** | A measure that modifies risk | MFA, patching, backups, training, segregation of duties |

A useful formula, popular in introductory risk management:

```text
Risk = Likelihood × Impact
```

Likelihood depends on the **threat** (capability, motivation) and the **vulnerability** (how exposed and exploitable it is). Impact depends on the **asset** (its value, and which CIA property is harmed). Controls reduce likelihood, impact, or both.

:::tip Say what you mean in tickets
Avoid "this vulnerability is high risk" when you have no impact information. Prefer: "CVE-2024-3400 (CVSS 10.0) affects our internet-facing firewall; exploitation is being observed in the wild; impact would include full network access. Recommend emergency patching." This separates *vulnerability severity* (CVSS) from *organisational risk*.
:::

### Threat actors and their motivations

| Actor | Typical motivation | Common techniques |
|---|---|---|
| Cyber-criminal groups | Financial gain | Phishing, ransomware, business email compromise |
| State-sponsored groups | Espionage, disruption | Supply-chain compromise, zero-days, long-term stealth |
| Hacktivists | Ideology, publicity | DDoS, defacement, data leaks |
| Insiders (malicious) | Revenge, profit | Data theft using legitimate access |
| Insiders (negligent) | None — mistakes | Misconfiguration, misdirected email, lost devices |

Understanding motivation helps predict behaviour: a financially motivated actor will move quickly toward data and backups; an espionage actor will prioritise stealth and persistence.

## Common attack vectors

An **attack vector** is the path an attacker uses to gain initial access. The following account for most real-world intrusions.

### 1. Phishing and social engineering

Emails, messages or calls that trick a user into opening a malicious file, entering credentials, or approving an MFA prompt.
*Evidence:* mail gateway logs, URL click logs, new inbox rules, sign-ins from unfamiliar infrastructure shortly after a click.

### 2. Credential abuse

Stolen, guessed or reused passwords used against VPN, webmail or cloud portals. Includes brute force and password spraying (MITRE ATT&CK **T1110**).
*Evidence:* bursts of failed logons (Windows **4625**, Entra ID sign-in failures with error codes such as 50126), followed by a success.

### 3. Exploitation of public-facing applications

Unpatched VPNs, firewalls, web applications and file-transfer servers (ATT&CK **T1190**).
*Evidence:* unusual requests in web/proxy logs, new processes spawned by web server accounts, outbound connections from DMZ hosts.

### 4. Supply chain and third parties

Compromised software updates, managed-service-provider access, or vulnerable libraries.
*Evidence:* trusted software behaving unusually; remote-management tools connecting at unusual times.

### 5. Removable media and physical access

USB devices, unattended workstations, rogue devices on the network.
*Evidence:* device installation events, new MAC addresses, Windows Event ID **6416** (new external device recognised).

### 6. Misconfiguration

Public storage buckets, exposed management interfaces, default credentials.
*Evidence:* cloud configuration findings, access from unknown IPs to resources that should be private.

```text
Typical intrusion chain (simplified)

Initial access ──► Execution ──► Persistence ──► Privilege escalation
      │                                                 │
   (phishing,                                           ▼
  exploitation)            Lateral movement ◄── Credential access
                                  │
                                  ▼
                 Collection ──► Exfiltration ──► Impact (encryption)
```

Each arrow is an opportunity for detection. Mature SOCs aim to detect early stages so that impact never happens.

## Controls: function and type

Controls are classified two ways.

**By function**

- **Preventive** — stop an incident from occurring (MFA, firewall rules, patching).
- **Detective** — identify that an incident is occurring or has occurred (SIEM rules, IDS, log review). *The SOC is fundamentally a detective control.*
- **Corrective** — limit damage and restore (backups, incident response, re-imaging).
- Some frameworks add **deterrent** (warning banners, legal notices) and **compensating** (an alternative control when the preferred one is not feasible).

**By type**

- **Technical** (logical) — implemented in technology: encryption, EDR, access control.
- **Administrative** (managerial) — policies, procedures, training, background checks.
- **Physical** — locks, badges, CCTV, environmental controls.

| Control | Function | Type |
|---|---|---|
| Multi-factor authentication | Preventive | Technical |
| Security awareness training | Preventive | Administrative |
| SIEM correlation rule | Detective | Technical |
| CCTV recording | Detective | Physical |
| Immutable backups | Corrective | Technical |
| Incident response plan | Corrective | Administrative |

## Security architecture in brief

A security architecture organises controls coherently. Concepts you will meet throughout this course:

- **Network segmentation** — separating user, server, management and OT networks so a compromise in one zone does not expose all others.
- **DMZ** — a buffer network for internet-facing services.
- **Identity as the perimeter** — in cloud environments, identity providers (such as Microsoft Entra ID) become the main enforcement point.
- **Logging architecture** — collectors, forwarders and a central SIEM so evidence survives a compromised host.

## The SOC within enterprise security operations

A **Security Operations Center** is the team, process and technology that continuously monitors, detects, investigates and responds to security events. It is part of the "Blue Team" — the defensive side of security.

Typical SOC responsibilities:

1. **Monitoring and triage** of alerts from SIEM, EDR, email security, cloud and identity tools
2. **Investigation** to determine scope, root cause and impact
3. **Incident response** coordination with IT, legal, communications and management
4. **Detection engineering** — creating and tuning rules
5. **Threat hunting** — proactively searching for undetected activity
6. **Threat intelligence** consumption and sharing
7. **Reporting** metrics to management

Many SOCs use a tiered model:

| Tier | Focus |
|---|---|
| Tier 1 — Triage | Review alerts, enrich, close false positives, escalate true positives using playbooks |
| Tier 2 — Investigation / IR | Deep analysis, scoping, containment decisions |
| Tier 3 — Hunting / Engineering | Threat hunting, detection engineering, malware analysis, tooling |
| SOC manager | Process, staffing, metrics, stakeholder communication |

Modern SOCs increasingly blur tiers through automation (SOAR) and by giving all analysts time for engineering work.

### Policies and procedures that govern SOC work

- **Incident response policy** — authority, definitions and severity levels.
- **Playbooks / runbooks** — step-by-step handling for specific alert types (phishing, malware, impossible travel).
- **Escalation matrix** — who to call, when, and how.
- **Evidence handling procedure** — how to preserve and document evidence.
- **Acceptable use policy** — what employees may do, which defines what counts as misuse.

## Common mistakes

- **Confusing vulnerability severity with risk.** A CVSS 9.8 on an isolated lab host may be a lower organisational risk than a CVSS 6.5 on the payment gateway.
- **Treating the SOC as the only control.** Detection cannot compensate for missing basics like MFA and patching.
- **Escalating without context.** "Suspicious login" is not actionable; "successful login for the CFO from a new country 10 minutes after 40 failures, no MFA challenge recorded" is.

## Security implications

- Inventory assets; you cannot assess risk to assets you do not know exist.
- Prefer layered, independent controls for high-impact risks.
- Ensure every playbook defines when to escalate and to whom — ambiguity costs time during incidents.

## Knowledge check

:::check Questions
1. Is "an unpatched Exchange server" a threat, a vulnerability or a risk?
2. Classify "SIEM alert on mass file deletion" by function and type.
3. Which attack vector is most associated with Windows Event ID 4625 bursts?
4. What distinguishes a Tier 1 from a Tier 2 analyst's responsibilities?
:::

:::solution Answers
1. A **vulnerability**. The risk is the likelihood that a threat exploits it, combined with the impact on the business.
2. **Detective, technical.**
3. **Credential abuse** — brute force or password spraying.
4. Tier 1 triages and escalates using playbooks; Tier 2 performs deep investigation, scoping and containment decisions.
:::

## Exercise

Your organisation's public webmail does not enforce MFA. Threat intelligence reports active password-spraying campaigns against your sector.

1. Identify the asset, threat, vulnerability and resulting risk.
2. Propose one preventive, one detective and one corrective control.
3. Write a two-sentence escalation note for your SOC manager.

:::solution Model answer
1. **Asset:** mailboxes and the data and trust they hold. **Threat:** criminal groups conducting password spraying. **Vulnerability:** no MFA on webmail. **Risk:** likely account compromise leading to data exposure and business email compromise fraud.
2. **Preventive:** enforce MFA and block legacy authentication. **Detective:** SIEM rule for many accounts failing from one source, followed by any success. **Corrective:** forced password reset, session revocation and mailbox rule review for affected accounts.
3. "Webmail lacks MFA while password spraying against our sector is active; a single successful guess would give an attacker mailbox access and enable invoice fraud. I recommend enforcing MFA this week and have enabled a spraying detection rule in the meantime."
:::

## Summary and next steps

- Threat, vulnerability, risk and control are distinct; use them precisely.
- Most intrusions start with phishing, credential abuse or exploitation of public-facing services — each leaves recognisable evidence.
- The SOC is a detective and corrective capability inside a broader control framework.

The next module, **Networking for SOC Analysts**, gives you the network fundamentals needed to read that evidence directly.

===assignment===
Extend the asset list from the previous assignment. For each of your three assets, describe **one realistic threat scenario** using the vocabulary from this lesson (threat → vulnerability → impact), state the attack vector, and list the **log sources** a SOC would need to detect it.

===criteria===
- Complete the reading, knowledge check and exercise.
- Mark the lesson complete. The Foundations module quiz draws questions from this lesson and the previous one.

===references===
- ISO/IEC 27000:2018, *Overview and vocabulary* (definitions of risk and control) — https://www.iso.org/standard/73906.html
- MITRE ATT&CK Enterprise Matrix — https://attack.mitre.org/matrices/enterprise/
- MITRE ATT&CK T1110, Brute Force — https://attack.mitre.org/techniques/T1110/
- MITRE ATT&CK T1190, Exploit Public-Facing Application — https://attack.mitre.org/techniques/T1190/
- CISA, *Known Exploited Vulnerabilities Catalog* — https://www.cisa.gov/known-exploited-vulnerabilities-catalog
