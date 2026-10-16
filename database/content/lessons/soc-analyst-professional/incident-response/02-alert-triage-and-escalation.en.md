---
title: Alert Triage and Escalation
chapter: Incident response
summary: Master the SOC analyst's core daily skill — turning a flood of alerts into decisions: triage true from false positives, enrich and set severity, escalate with a clear handoff, and feed what you learn back into better detections.
duration: 50
level: beginner
preview: true
objectives:
- Work an alert queue with a repeatable triage funnel
- Distinguish true positive, false positive, benign and duplicate outcomes
- Enrich an alert and assign severity from impact and confidence
- Write an escalation and handoff note another analyst can act on without you
- Close the loop so triage improves detections and reduces false positives
prerequisites:
- The Incident Response Lifecycle
---

## Introduction

The lifecycle lesson showed the shape of a whole incident. But most of a SOC analyst's day is spent *before* anything is confirmed an incident: working a queue of alerts, most of which are noise, to find the few that matter. That skill — **triage** — is what separates a SOC that catches intrusions early from one that drowns in alerts and misses the real one.

This lesson is the Tier-1/Tier-2 daily craft: a repeatable way to work the queue, decide each alert, set severity, and hand off cleanly when something is real — using the evidence-reading you built in the logs and detection modules.

## The triage funnel

Alerts arrive faster than they can be deeply investigated, so triage is a funnel: spend seconds on most, minutes on some, and escalate the few that earn it.

```text
        ALERT QUEUE  (hundreds/day)
              │  quick look: is this obviously noise?
              ▼
        INITIAL TRIAGE  ── false positive / benign / duplicate ──► close with reason
              │  not obviously noise
              ▼
        ENRICH & ASSESS  ── still benign on inspection ──────────► close with reason
              │  suspicious and plausible
              ▼
        SEVERITY + ESCALATE  ──► hand off to deeper investigation / IR
```

The goal is not to investigate every alert fully — that is impossible — but to make a **defensible decision** on each, fast, and to route the real ones to deeper investigation (the lifecycle's identification→containment phases) without delay.

## The four triage outcomes

Every alert you touch ends in one of four dispositions. Naming them — and the *reason* — is what makes triage auditable and tunable.

| Outcome | Meaning | What you record |
|---|---|---|
| **True positive** | Real malicious or policy-violating activity | What fired, the evidence, why it is real → escalate |
| **False positive** | The rule fired but the activity is not what it detected | Why it is wrong → feed back to tune the rule |
| **Benign true positive** | The activity is real but authorised/expected | The legitimate explanation (e.g. a known admin task) |
| **Duplicate** | Same activity already covered by another alert/case | Link to the original |

:::tip Always record the reason, not just the verdict
"Closed – false positive" with no reason teaches nobody and tunes nothing. "False positive: the 4625 burst was the backup service with an expired password, confirmed with the server owner; recommend excluding svc-backup from this rule" turns one triage decision into a permanent noise reduction. The reason is the value.
:::

## Enrichment: turn an alert into context

A raw alert is a fragment. **Enrichment** adds the context that lets you decide, drawing on exactly the sources from earlier modules:

- **Who / what:** the user, host, process, and whether they are high-value (a domain admin, a server holding regulated data).
- **Where from:** the source IP — internal or external, known-good or flagged by threat intelligence, geolocation vs. where the user actually is.
- **When / how often:** first time seen, or a recurring pattern? During business hours for that user?
- **Corroboration:** does another log source agree? A failed-logon burst (Windows 4625 / Linux `auth.log`) *followed by a success* is far stronger than either alone — the correlation you practised in the logs lessons.

Enrichment is where a "suspicious login" becomes either "the CFO signing in from their usual city, benign" or "a success from a new country right after 40 failures, no MFA — escalate."

## Severity: impact × confidence

Not every true positive is an emergency. Set severity from two axes so the most dangerous, most certain alerts rise first:

- **Impact** — how bad if it is real? (A domain controller or regulated-data host ranks far above a test VM — the asset classification from the GRC/foundations work.)
- **Confidence** — how sure are you it is real, given the evidence and corroboration?

```text
 Impact
  high │  medium    HIGH      CRITICAL
       │  low       medium    HIGH
   low │  low       low       medium
       └───────────────────────────────
           low      med      high   Confidence
```

High-impact *and* high-confidence is where you drop other work. Low-impact, low-confidence can wait or close. Severity drives the clock: a critical gets worked now and escalated immediately; a low can be batched. This is the same risk-prioritisation logic as the risk register, applied minute to minute.

## Escalation and the handoff note

When an alert earns escalation, you hand it to deeper investigation or incident response. The hand-off succeeds or fails on **one artefact: a note the next analyst can act on without talking to you.** A good handoff states:

1. **What fired and when** — the alert, the time, the rule.
2. **Why it is real / suspicious** — the evidence you found, with the specific log lines or IDs.
3. **Scope so far** — which users, hosts and IPs are involved.
4. **Severity and why** — your impact × confidence call.
5. **Actions already taken** — what you checked, and anything already done (e.g. "disabled the account pending review").
6. **Recommended next step** — what you think should happen now.

> **Weak:** "Suspicious login, please investigate."
> **Strong:** "02:19 UTC, rule *SSH brute-force success*: 60 failed SSH logons for several users from 198.51.100.23, then `Accepted password for deploy`, then `sudo ... /bin/bash`. Scope: host web-03, account `deploy`, source 198.51.100.23. Severity High (prod web host, high confidence — clear brute-force-then-escalation chain). I pulled the auth.log extract (attached) and have NOT contained yet. Recommend isolating web-03 and disabling `deploy`."

The strong note lets the next person act in seconds. Writing it well is as much a SOC skill as reading the logs.

:::warning Know your escalation path before you need it
Mid-incident is the wrong time to work out who to call. An **escalation matrix** — who handles what severity, who to wake at 3 a.m. for a critical, when to involve IR, legal or management — belongs in your runbooks (lifecycle lesson) and should be known before the alert fires. Triage speed is wasted if the escalation stalls.
:::

## Closing the loop: triage improves detection

Triage is not just disposal; it is the feedback that makes detections better over time.

- **False positives** → tune the rule (add an exclusion, tighten the condition) so that noise does not return. The brute-force rule you built in the KQL module gets sharper every time a benign burst is explained.
- **Repeated benign true positives** → document the known-good pattern so future analysts close it in seconds (or suppress it at the source).
- **True positives that the rules nearly missed** → propose a new or broader detection.

A SOC that closes this loop sees its false-positive rate fall and its real-catch rate rise. One that closes alerts without reasons stays just as noisy next month — the metric to watch is not "alerts closed" but "noise permanently removed and real threats caught earlier."

## Common mistakes

- **Investigating every alert fully.** The queue wins; triage means fast, defensible decisions, deep work only for the few that earn it.
- **Closing without a reason.** Teaches nobody and tunes nothing; the reason is the value.
- **Verdict without enrichment.** "Suspicious login" is not actionable; who, from where, and corroborated by what is.
- **Severity by gut.** Use impact × confidence so the dangerous-and-certain rise first.
- **A vague handoff.** If the next analyst must call you to act, the note failed.
- **No feedback loop.** Closing false positives without tuning leaves the same noise forever.

## Security implications

- Work the queue as a funnel: a fast, reasoned disposition on every alert, deep investigation only for the few that earn it.
- Enrich before you judge, and set severity from impact × confidence, not instinct.
- Make escalation a clean, self-contained handoff, and know the escalation matrix in advance.
- Feed every false positive back into tuning so the queue gets quieter and the catches get earlier.

## Knowledge check

:::check Questions
1. Name the four triage outcomes an alert can end in.
2. What two axes set an alert's severity, and which combination means "drop everything"?
3. Give two things a good escalation/handoff note must contain.
4. How does triage make future detections better?
:::

:::solution Answers
1. **True positive, false positive, benign true positive, and duplicate** — each recorded with its reason.
2. **Impact** (how bad if real) and **confidence** (how sure it is real). **High impact + high confidence** is the critical combination where you drop other work and escalate immediately.
3. Any two of: **what fired and when**; **why it is real/suspicious with specific evidence**; **scope (users/hosts/IPs)**; **severity and the reasoning**; **actions already taken**; **the recommended next step** — enough that the next analyst can act without contacting you.
4. By feeding outcomes back: **false positives drive rule tuning** (exclusions, tighter conditions), **repeated benign patterns get documented or suppressed**, and **near-misses prompt new/broader detections** — lowering the false-positive rate and catching real threats earlier.
:::

## Exercise

You pick up three queued alerts:
- **A:** one failed VPN logon for a normal user, during business hours, from their usual city.
- **B:** 50 failed logons for `svc-sql` from an internal host in 2 minutes, then a success, outside any change window.
- **C:** antivirus quarantined a known test EICAR file on a sandbox VM used for malware training.

1. Give each a **disposition** (true/false/benign/duplicate) with a one-line reason.
2. For the one you escalate, assign **severity** (impact × confidence) and justify it.
3. Write a short **handoff note** for that alert using the six elements above.

:::solution Model answer
1. **A — benign / low:** a single failed logon in-pattern is normal; close (note the reason). **B — true positive:** failure-burst-then-success on a service account outside a change window is a likely brute-force compromise; escalate. **C — benign true positive:** EICAR on a known malware-training sandbox is expected; close, and consider suppressing that path.
2. **B — severity High/Critical:** impact is high (a SQL service account can reach regulated data) and confidence is high (clear burst-then-success pattern, no change window) → drop other work and escalate now.
3. *Handoff:* "14:07 UTC, rule *Service-account brute-force success*: 50 failed logons for `svc-sql` from internal host 10.0.4.9 in ~2 min, then a successful logon, with no matching change ticket. Scope: account `svc-sql`, source 10.0.4.9, target DB host. Severity High (regulated-data access, high confidence). I confirmed the burst in the auth logs (extract attached) and have NOT contained. Recommend disabling `svc-sql`, isolating 10.0.4.9, and checking for lateral movement from it."
:::

## Summary and next steps

- Triage is the SOC's core daily skill: work the queue as a funnel and make a fast, defensible, *reasoned* decision on every alert.
- Enrich before judging; set severity from impact × confidence so the dangerous-and-certain rise first.
- Escalate with a self-contained handoff note, and know your escalation matrix before the alert fires.
- Close the loop — every false positive tuned, every pattern documented — so the queue quietens and real threats are caught earlier.

With triage and the lifecycle together, you can take an alert from the queue to a contained, documented incident — the full arc of SOC analysis. The final exam draws across every module; passing it earns your **SOC Analyst Professional** certificate (a recognition of achievement on this platform, not an accredited qualification).

===assignment===
Take five sample alerts (invent them, or reuse ones from earlier labs) and triage each: record a disposition with a reason, enrich at least the two most suspicious with who/where/when/corroboration, assign severity via impact × confidence, and write a full handoff note for the single alert you would escalate. Finish with one **tuning recommendation** derived from a false positive in your set.

===criteria===
- Complete the reading, the knowledge check and the exercise.
- Every alert must have a disposition *and a reason*; the escalated one must have an impact × confidence severity and a handoff note containing all six elements; and you must propose one concrete detection-tuning change.
- Mark the lesson complete. The module quiz draws on this lesson and the lifecycle lesson.

===references===
- NIST SP 800-61 Rev. 2, *Computer Security Incident Handling Guide* — https://csrc.nist.gov/pubs/sp/800/61/r2/final
- MITRE ATT&CK Enterprise Matrix (for mapping observed activity) — https://attack.mitre.org/matrices/enterprise/
- SANS, *Incident Handler's Handbook* — https://www.sans.org/white-papers/33901/
- FIRST, *Common Vulnerability Scoring System* (impact/severity concepts) — https://www.first.org/cvss/
