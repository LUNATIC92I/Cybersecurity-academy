---
title: Threat Intelligence and Threat Hunting
chapter: Threat intelligence and hunting
summary: Learn how a SOC uses threat intelligence to sharpen detection and how proactive threat hunting finds the attacks that slipped past the alerts — the pyramid of pain, IOCs vs TTPs, hypothesis-driven hunting, and the MITRE ATT&CK map that ties them together.
duration: 50
level: intermediate
preview: true
objectives:
- Define threat intelligence and distinguish its strategic, operational and tactical levels
- Explain IOCs vs TTPs and the pyramid of pain
- Describe how intelligence feeds detection, triage and blocking
- Explain hypothesis-driven threat hunting and its loop
- Use MITRE ATT&CK as the shared language linking intelligence, hunting and detection
prerequisites:
- SIEM Operations — The Log Pipeline, Normalisation and Alerting
---

## Introduction

So far the SOC has been **reactive**: logs arrive, rules fire, analysts triage. Two disciplines make it **proactive**. **Threat intelligence** tells you which threats matter and what they look like, so your detections are aimed at real adversaries. **Threat hunting** actively searches for attackers who evaded the alerts entirely. Together they turn a SOC from a team that waits for alarms into one that anticipates and seeks out threats — using the same SIEM, logs and detections you already know.

This lesson is conceptual and blue-team throughout: it is about understanding adversaries to detect and stop them, not about carrying out attacks.

## What threat intelligence is

**Cyber threat intelligence (CTI)** is evidence-based knowledge about threats — who the adversaries are, what they target, and how they operate — produced so defenders can make better decisions. It is usually described at three levels:

| Level | Audience | Answers |
|---|---|---|
| **Strategic** | Leadership | Which threat actors and trends matter to *us*, and what should we invest in? |
| **Operational** | SOC/IR management | Which campaigns and adversary behaviours are active against our sector? |
| **Tactical** | Analysts & detection engineers | What specific indicators and techniques do we detect and block? |

Good intelligence is **actionable and relevant**: a feed of ten thousand random bad IPs is noise; knowing that a group targeting your sector uses a specific technique, which you can then detect, is intelligence. The test is always "what will we *do* differently because of this?"

:::note Intelligence must be relevant to you
The most common CTI failure is drowning in generic indicators that do not apply to your environment. Prioritise intelligence about threats that realistically target your sector, your technologies and your data — the same risk-driven focus as the GRC path. Relevance beats volume every time.
:::

## IOCs, TTPs and the pyramid of pain

Intelligence describes adversaries at different depths. Two terms matter:

- **Indicators of Compromise (IOCs)** — concrete artefacts of an attack: a malicious IP or domain, a file hash, a URL. Easy to share and to match, but **brittle** — an attacker changes them trivially.
- **Tactics, Techniques and Procedures (TTPs)** — *how* an adversary behaves: the methods they use (e.g. brute-forcing then using valid accounts, then scheduled-task persistence). Harder to detect, but **durable** — behaviour is far costlier for an attacker to change than an IP.

David Bianco's **pyramid of pain** ranks indicators by how much it hurts the adversary when you detect at that level:

```text
                 ▲  more pain to the attacker
        ┌────────────────────┐
        │       TTPs         │   hardest to change  →  detect these for durable defence
        ├────────────────────┤
        │  Tools             │
        ├────────────────────┤
        │  Network/Host      │   artifacts
        ├────────────────────┤
        │  Domain names      │
        ├────────────────────┤
        │  IP addresses      │
        ├────────────────────┤
        │  Hash values       │   trivial to change  →  least durable
        └────────────────────┘
                 ▼  less pain
```

The lesson of the pyramid: **detecting hash and IP indicators is cheap but easily evaded; detecting TTPs forces the attacker to change how they operate, which is expensive.** A mature SOC uses IOCs for quick wins and invests in TTP-based detection for lasting defence — the brute-force *behaviour* you detected in KQL is a TTP-level detection, which is why it keeps working even as source IPs change.

## How intelligence feeds the SOC

CTI is only valuable when it changes what the SOC does:

- **Detection engineering** — turn TTPs and IOCs into SIEM rules (the detection-as-code from the SIEM lesson), prioritising durable TTP detections.
- **Enrichment** — match observed indicators against intel at ingest so an alert arrives already flagged (the enrichment from the SIEM and triage lessons).
- **Blocking** — push high-confidence malicious indicators to firewalls, proxies and EDR to stop known-bad automatically.
- **Prioritisation** — intel about an actor actively targeting your sector raises the severity of related alerts (the impact×confidence judgement from triage).

The flow is a loop: intelligence sharpens detection → detections and hunts produce new observations → those feed back as fresh intelligence (often shared with the community).

:::tip Share intelligence as structured data, not prose
Intelligence is exchanged in machine-readable formats (e.g. STIX/TAXII) so it can flow straight into detection and blocking rather than sitting in a PDF. The goal is always to get an indicator or technique into the SIEM, the block-list or a hunt — not merely to read about it.
:::

## Threat hunting: proactive search

**Threat hunting** is the proactive, hypothesis-driven search for threats that have evaded existing detections. It assumes a breach may already be present and goes looking, rather than waiting for an alert. The distinction from alerting:

```text
 DETECTION (reactive)        HUNTING (proactive)
 a rule fires on a known     an analyst forms a hypothesis and
 pattern → alert → triage    searches the data for evidence of it
                             → find it → turn it into a new detection
```

A hunt is **hypothesis-driven**. Rather than "look for anything bad" (unbounded, unproductive), the hunter starts from a specific, testable idea — usually drawn from intelligence or ATT&CK:

1. **Hypothesis** — "if an adversary used scheduled-task persistence (a known TTP for actors in our sector), we would see new or modified cron/systemd timers or Windows scheduled tasks created outside change windows."
2. **Hunt** — query the SIEM for that evidence across the estate (using the normalised data and the log knowledge from earlier lessons).
3. **Findings** — either you find suspicious activity (escalate as an incident) or you do not (still valuable: you have validated coverage).
4. **Improve** — turn a successful hunt into a **new automated detection** so the SOC never has to hunt that same thing manually again.

That last step is the point: hunting continuously *expands* automated detection. A good hunt either catches an intrusion or leaves behind a new rule.

:::scenario A hunt from intelligence to detection
Operational intelligence reports that a group active against your sector establishes persistence via scheduled tasks and beacons to rare domains every few minutes. The SOC forms two hypotheses and hunts: (1) scheduled tasks created outside change windows — the hunt finds one on a server nobody recognises; (2) periodic connections to newly-seen domains — found on the same host (the beaconing pattern from the networking lesson). What began as intelligence became a hunt, the hunt became an incident, and the SOC then wrote two new detections so the next occurrence alerts automatically. Intelligence → hunt → incident → durable detection.
:::

## MITRE ATT&CK: the shared map

**MITRE ATT&CK** is a public knowledge base of adversary **tactics** (the *why* — e.g. Persistence, Credential Access, Exfiltration) and **techniques** (the *how* — e.g. Brute Force T1110, Scheduled Task/Job T1053). It is the common language that ties this path together:

- **Intelligence** describes actors by the ATT&CK techniques they use.
- **Hunting** forms hypotheses around specific techniques.
- **Detection** maps each rule to the technique it covers, revealing **coverage gaps** — which techniques you can and cannot currently detect.
- **Incident response** (from that module) maps observed activity to techniques to understand the whole attack.

Using ATT&CK, a SOC can honestly answer "which adversary behaviours can we detect?" and prioritise closing the gaps — the same map you saw referenced throughout the SOC and Pentest paths.

## Common mistakes

- **Drowning in generic IOCs.** Volume is not value; prioritise intelligence relevant to your sector, tech and data.
- **Only detecting low-pyramid indicators.** Hashes and IPs are trivially changed; invest in durable TTP-based detection.
- **Intelligence that changes nothing.** If a report does not become a detection, a block or a hunt, it was not actionable.
- **Unbounded "hunting".** "Look for bad stuff" is not a hunt; a hunt starts from a specific, testable hypothesis.
- **Hunts that do not improve detection.** A successful hunt should leave behind a new automated rule so it need not be repeated by hand.
- **No ATT&CK mapping.** Without it you cannot see your detection coverage gaps.

## Security implications

- Prioritise relevant, actionable intelligence and drive it into detection, enrichment and blocking.
- Detect at the TTP level for durable defence; use IOCs for quick, cheap wins.
- Hunt from specific hypotheses (often intelligence- or ATT&CK-derived), and convert every successful hunt into a new detection.
- Map intelligence, hunts and detections to MITRE ATT&CK to measure and close coverage gaps.

## Knowledge check

:::check Questions
1. What are the three levels of threat intelligence, and who is each for?
2. What is the difference between an IOC and a TTP, and which is more durable to detect?
3. What makes a threat hunt "hypothesis-driven", and what should a successful hunt leave behind?
4. How does MITRE ATT&CK tie intelligence, hunting and detection together?
:::

:::solution Answers
1. **Strategic** (for leadership — which threats/trends matter and what to invest in), **operational** (for SOC/IR management — active campaigns and behaviours against the sector), and **tactical** (for analysts/detection engineers — specific indicators and techniques to detect and block).
2. An **IOC** is a concrete artefact (IP, domain, hash) — easy to match but brittle, trivially changed by the attacker. A **TTP** is *how* the adversary behaves — harder to detect but **far more durable**, because changing behaviour is much costlier than changing an IP. TTP-level detection is more durable.
3. It starts from a **specific, testable hypothesis** (usually from intelligence or ATT&CK) rather than an unbounded "look for bad things", so the hunt can be executed and answered against the data. A successful hunt should leave behind a **new automated detection** so the SOC never has to hunt that same thing by hand again.
4. ATT&CK is a shared catalogue of adversary **tactics and techniques**: intelligence describes actors by the techniques they use, hunts form hypotheses around techniques, detections are mapped to the techniques they cover (exposing coverage gaps), and IR maps observed activity to techniques — one common language across all four.
:::

## Exercise

Using a TTP relevant to a sector of your choice (e.g. credential access via brute force, or scheduled-task persistence):

1. Write it as a **hunting hypothesis** in the "if the adversary did X, we would see Y in log source Z" form.
2. Describe the **SIEM query logic** (plain language) you would run and which normalised fields/log sources it uses (reuse earlier lessons).
3. State what you would do if the hunt **finds** something and if it finds **nothing**.
4. Name the **MITRE ATT&CK technique** it maps to and the **new detection** you would create from a successful hunt.

:::solution Model answer (abbreviated)
1. *Hypothesis:* "If an adversary established persistence via scheduled tasks, we would see new or modified cron jobs / systemd timers / Windows scheduled tasks created outside approved change windows, especially on servers." 
2. Query the SIEM for scheduled-task/cron/timer creation or modification events (from Windows and Linux logs, normalised to a common `action`/`host`/`user`/`time` schema), filtered to times outside change windows and correlated with the account that created them.
3. *Finds something:* treat it as a potential incident — enrich (who/where/when), assess severity, and escalate per the triage lesson. *Finds nothing:* record the hunt as coverage validated for that technique, and still create the detection below.
4. *ATT&CK:* Scheduled Task/Job (**T1053**). *New detection:* an automated rule alerting on scheduled-task/cron/timer creation outside change windows, so future occurrences alert without a manual hunt.
:::

## Summary and next steps

- **Threat intelligence** (strategic/operational/tactical) makes detection relevant by aiming it at real adversaries — prioritise relevance and actionability over volume.
- Detect at the **TTP** level for durable defence (the pyramid of pain); use **IOCs** for cheap quick wins; feed both into detection, enrichment and blocking.
- **Threat hunting** is proactive and **hypothesis-driven**, and every successful hunt should become a new automated detection.
- **MITRE ATT&CK** is the shared map that links intelligence, hunting, detection and incident response and exposes coverage gaps.

The remaining SOC modules — malware and endpoint investigation, identity and cloud monitoring, and advanced SOC operations — extend these proactive capabilities to more data sources and techniques; all remain defensive, within the platform's responsible-use frame.

===assignment===
Produce a one-page **threat-intelligence-and-hunting plan** for an organisation of your choice: identify two threats realistically relevant to its sector (with the ATT&CK techniques they use), describe how you would operationalise intelligence about them (detection, enrichment, blocking), and write two **hunting hypotheses** with the log sources and query logic for each. For each hunt, state the new automated detection you would create from a positive result, and map everything to MITRE ATT&CK techniques.

===criteria===
- Complete the reading and the knowledge check, and produce the plan.
- Intelligence must be relevant and actionable (not a generic IOC dump); hunts must be specific, testable hypotheses tied to log sources; and each must name an ATT&CK technique and the detection a successful hunt would create.
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- MITRE ATT&CK — https://attack.mitre.org/
- David Bianco, *The Pyramid of Pain* — https://detect-respond.blogspot.com/2013/03/the-pyramid-of-pain.html
- MITRE, *TTP-Based Hunting* — https://www.mitre.org/sites/default/files/2021-11/prs-19-3892-ttp-based-hunting.pdf
- OASIS, *STIX/TAXII* (structured threat-intelligence sharing) — https://oasis-open.github.io/cti-documentation/
- CISA, *Cybersecurity Advisories and threat information sharing* — https://www.cisa.gov/news-events/cybersecurity-advisories
