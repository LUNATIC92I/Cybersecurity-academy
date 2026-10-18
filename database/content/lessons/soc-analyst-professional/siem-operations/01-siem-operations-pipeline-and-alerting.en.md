---
title: SIEM Operations — The Log Pipeline, Normalisation and Alerting
chapter: SIEM operations
summary: Understand the system a SOC runs on — how logs are collected, parsed and normalised into one searchable store, how detection rules turn events into alerts, and how tuning and correlation keep the signal high and the noise low.
duration: 50
level: beginner
preview: true
objectives:
- Explain what a SIEM is and the job it does for a SOC
- Describe the log pipeline from source to searchable store
- Explain parsing, normalisation and why a common schema matters
- Describe how detection rules, correlation and enrichment create alerts
- Explain tuning, false positives and the health signals of a log pipeline
prerequisites:
- Windows Event Logs for Investigations
- Linux Logs and Auditing for SOC Analysts
---

## Introduction

Earlier lessons read individual logs — Windows events, Linux auth logs — and wrote detection logic in KQL. But a SOC does not log in to each server to read files; it works from a **SIEM** (Security Information and Event Management), the system that collects logs from everywhere, makes them searchable in one place, and turns them into alerts. This lesson explains that system: the pipeline that feeds it, how raw logs become normalised events, and how rules and correlation produce the alerts you triaged in the incident-response module.

Understanding the SIEM is what connects every other SOC skill: the logs you learned to read flow into it, the KQL you learned to write runs on it, and the alerts you learned to triage come out of it.

## What a SIEM is and does

A **SIEM** centralises security-relevant data and provides four core capabilities:

| Capability | What it gives the SOC |
|---|---|
| **Collection** | Logs and telemetry from across the estate, in one place |
| **Storage & search** | A durable, fast-to-query store over huge volumes |
| **Detection** | Rules that match patterns and raise alerts |
| **Investigation** | Pivoting, correlation and dashboards across all sources |

Its value is **centralisation and correlation**: evidence survives even if a source host is compromised (the point from the logs lessons), and a single query can span every host at once — so the brute-force rule you wrote in KQL watches the whole organisation, not one machine. A related term, **SOAR** (Security Orchestration, Automation and Response), adds automated response and playbook execution on top; many SOCs run the two together.

## The log pipeline

Getting data into the SIEM is a pipeline, and each stage is where things succeed or quietly fail:

:::diagram The SIEM log pipeline, source to alert
```text
 SOURCES ─► COLLECTION ─► PARSE & NORMALISE ─► ENRICH ─► STORE/INDEX ─► DETECT ─► ALERT
 (endpoints,  (agents,      (fields extracted,  (geoIP,    (searchable)  (rules)   (to triage)
  servers,    forwarders,    mapped to a         threat
  firewalls,  syslog,        common schema)      intel,
  cloud, IdP)  APIs)                             asset info)
```
:::

- **Sources** — the logs from earlier lessons and more: endpoints and EDR, servers (Windows/Linux), firewalls and proxies, cloud platforms, identity providers, applications.
- **Collection** — agents on hosts, syslog forwarding (the rsyslog point from the Linux-logs lesson), or API pulls from cloud/SaaS. Reliable collection is the foundation; a source that stops sending is a blind spot.
- **Parse & normalise** — the critical transformation, below.
- **Enrich** — add context as data arrives: geolocation for IPs, threat-intel matches, which user or asset an identifier maps to (the enrichment you did by hand in triage, done automatically).
- **Store & index** — a store optimised for fast search over large volumes and time ranges.
- **Detect & alert** — rules run over the stored (and often streaming) data and raise alerts for triage.

## Parsing and normalisation: the common schema

Raw logs are a mess of formats: a Windows event, a Linux `auth.log` line and a firewall record all express "a login from an IP" completely differently. **Parsing** extracts the fields from each format; **normalisation** maps them to a **common schema** so the same concept has the same field name everywhere.

:::diagram Normalising three log formats to one common schema
```text
 Windows 4624  ── Account_Name, Source_Network_Address ──┐
 Linux auth.log ── user, rhost ────────────────────────► │  normalise to:
 Firewall log  ── src_user, src_ip ─────────────────────┘   user, src_ip, action, host, time
```
:::

Why it matters: with a common schema, **one query finds a behaviour across every source** — "show successful logins from this IP across Windows, Linux and the VPN" is a single search, not three. Without normalisation, each source needs its own query and correlation is nearly impossible. A good schema (many SOCs adopt a published one) is what makes the KQL detection lesson's rules portable and the cross-source pivots from the triage lesson fast. Consistent **timestamps in a single timezone (UTC)** are part of this — events from different sources must line up on one timeline.

:::tip Normalisation is why correlation is possible
The single most valuable thing a SIEM does is let you ask one question of everything at once. That is only possible because parsing and normalisation have already turned "Source_Network_Address", "rhost" and "src_ip" into the same field. When a new log source is onboarded, writing its parser correctly is what makes it useful; an unparsed source is just text nobody can query.
:::

## From events to alerts: detection, correlation, enrichment

Events become **alerts** through detection logic running on the SIEM:

- **Rules / analytics** — the detections you wrote in KQL: a condition over the data (e.g. many failed logons from one source then a success) that raises an alert. These may run on a schedule or over streaming data.
- **Correlation** — combining events from *different* sources into one higher-confidence signal: a firewall allow + an endpoint process start + an identity sign-in that together describe one attack. Correlation across sources is exactly what normalisation enables and what makes an alert worth escalating.
- **Enrichment** — attaching context (geo, threat intel, asset criticality, user role) so the analyst gets an alert they can triage immediately, not a bare event — the manual enrichment from the triage lesson, automated at ingest.

The output is the alert queue the incident-response/triage lesson taught you to work: the better the rules, correlation and enrichment, the higher the quality of what reaches a human.

## Tuning, false positives and signal-to-noise

A SIEM's usefulness is decided by its **signal-to-noise ratio**. Too many low-value alerts and real threats are missed in the flood (alert fatigue). Keeping signal high is ongoing work, and it connects directly to triage:

- **Tuning** — every false positive from triage feeds back into the rule (an exclusion, a tighter threshold) so that noise does not recur — the feedback loop from the triage lesson, applied to SIEM content.
- **Baselining** — knowing what is normal for an environment so rules alert on the abnormal, not the merely unusual-looking.
- **Suppression and aggregation** — collapsing duplicate or known-benign events so analysts see one meaningful alert, not a thousand.
- **Detection as code** — managing rules like software (versioned, reviewed, tested), so detections improve deliberately over time.

:::warning A SIEM is only as good as its inputs and its tuning
Two failure modes sink SIEMs. First, **coverage gaps**: a critical source not sending logs, or sending unparsed ones, is a blind spot the SOC cannot see through — the attack happens in the dark. Second, **noise**: thousands of untuned alerts bury the real one, so analysts stop trusting the queue. Monitoring log-source health (is every source still sending?) and relentless tuning are not optional extras — they are what makes the SIEM work.
:::

## Pipeline health: the SOC's own monitoring

Because missing logs are invisible, a SOC must monitor the pipeline itself:

- **Source health** — is every expected source still sending, at its normal volume? A source that goes quiet is a blind spot (and sometimes a sign an attacker disabled logging — the tampering point from the Linux-logs lesson).
- **Ingestion lag** — are events arriving in near-real-time, or is the pipeline backed up so alerts fire late?
- **Parser health** — are new log formats being parsed, or arriving as unsearchable text?

These are operational Key Risk Indicators for the SOC itself (the KRI idea from the GRC path), and watching them is as important as watching the alerts.

## Common mistakes

- **Collecting without parsing.** Unnormalised logs cannot be correlated or queried consistently; the parser is what makes a source useful.
- **No log-source health monitoring.** A source that silently stops is an invisible blind spot; monitor coverage, not just alerts.
- **Never tuning.** Untuned rules flood the queue, cause alert fatigue, and hide the real threat.
- **Inconsistent timestamps/timezones.** Events that do not line up on one UTC timeline make correlation unreliable.
- **Treating the SIEM as a log archive.** Its value is detection and correlation, not just storage; rules and enrichment are the point.

## Security implications

- Centralise logs so evidence survives host compromise and one query spans the whole estate.
- Parse and normalise every source to a common schema with consistent UTC timestamps — correlation depends on it.
- Build detection from rules, cross-source correlation and ingest-time enrichment; feed triage outcomes back as tuning.
- Monitor pipeline health (source coverage, lag, parsing) as first-class SOC telemetry — missing logs are invisible.

## Knowledge check

:::check Questions
1. What four core capabilities does a SIEM give a SOC?
2. What is normalisation, and why does it make correlation possible?
3. How does the triage feedback loop relate to SIEM tuning?
4. Why must a SOC monitor log-source health, not just alerts?
:::

:::solution Answers
1. **Collection** (logs/telemetry from across the estate), **storage & search** (durable, fast querying over large volumes), **detection** (rules that raise alerts), and **investigation** (pivoting, correlation and dashboards across sources).
2. **Normalisation** maps parsed fields from every log format to a **common schema**, so the same concept (e.g. a source IP) has the same field name everywhere. It makes correlation possible because one query can then span all sources at once — instead of a different query per format, which makes cross-source correlation nearly impossible.
3. Every **false positive** resolved in triage should feed back into the SIEM rule (an exclusion or tighter threshold) so that noise does not recur — the same feedback loop, applied to detection content, which raises signal-to-noise over time.
4. Because **missing logs are invisible**: a source that silently stops sending (or sends unparsed data) is a blind spot the SOC cannot see through — and silence can even signal an attacker disabling logging. Monitoring coverage, ingestion lag and parser health is what guarantees the alerts are based on complete data.
:::

## Exercise

For a small organisation standing up a SIEM:

1. List **five log sources** you would onboard first and, for each, one threat it helps detect (reuse the earlier lessons).
2. Explain, with a concrete field example, what **normalisation** would do across two of those sources.
3. Describe **one detection rule** (plain language) and the **enrichment** that would make its alerts triage-ready.
4. Name **two pipeline-health checks** you would put in place and why.

:::solution Model answer (abbreviated)
1. Windows security logs (brute force / 4625→4624); Linux `auth.log` (SSH brute force, sudo abuse); firewall/proxy (C2 beaconing, exfiltration); identity provider (impossible travel, MFA failures); EDR (malicious process execution).
2. Across Windows and Linux auth logs, normalisation maps `Account_Name`/`Source_Network_Address` and `user`/`rhost` to the same `user` and `src_ip` fields, so one query finds "successful logins from a given IP" on both.
3. *Rule:* many failed logons from one source IP followed by a success within a short window. *Enrichment:* geolocation and threat-intel reputation for the source IP, plus the asset criticality of the target — so the analyst sees "success from a flagged IP, new country, on a domain controller" and can triage immediately.
4. *Source health* — alert if any expected source stops sending or drops below normal volume (blind-spot / possible tampering); *ingestion lag* — alert if events arrive late, because delayed detection means delayed response.
:::

## Summary and next steps

- A **SIEM** collects, stores, detects and investigates — its power is centralisation and cross-source correlation.
- The **pipeline** runs sources → collection → parse/normalise → enrich → store → detect → alert; each stage can succeed or quietly fail.
- **Normalisation to a common schema** (with consistent UTC time) is what lets one query and one rule span every source.
- **Tuning** (fed by triage) keeps signal-to-noise high, and **pipeline-health monitoring** ensures the data is complete — missing logs are invisible.

This completes the picture the SOC path has been building: read the logs, write the detections, run them on the SIEM, and triage the alerts they produce. Later modules (threat intelligence and hunting, malware investigation, identity and cloud monitoring) extend the sources and techniques this pipeline carries.

===assignment===
Design a one-page **SIEM onboarding and tuning plan** for an organisation of your choice: the priority log sources and the threats each addresses; a short description of the common-schema fields you would normalise to (with an example mapping across two sources); two detection rules with the enrichment that makes them triage-ready; and a pipeline-health monitoring section (source coverage, lag, parser health) explaining why each matters. Tie at least one rule back to a detection you wrote in the KQL module.

===criteria===
- Complete the reading and the knowledge check, and produce the plan.
- The plan must show normalisation across at least two sources, connect tuning to the triage feedback loop, and include pipeline-health monitoring with a rationale.
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- NIST SP 800-92, *Guide to Computer Security Log Management* — https://csrc.nist.gov/pubs/sp/800/92/final
- MITRE ATT&CK (mapping detections to techniques) — https://attack.mitre.org/
- The Open Cybersecurity Schema Framework (OCSF) — https://schema.ocsf.io/
- Sigma, generic signature format for SIEM detections — https://github.com/SigmaHQ/sigma
- MITRE, *Cyber Analytics Repository (CAR)* — https://car.mitre.org/
