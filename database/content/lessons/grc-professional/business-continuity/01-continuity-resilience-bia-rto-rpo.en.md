---
title: Business Continuity and Resilience — BIA, RTO and RPO
chapter: Business Continuity and Resilience
summary: Keep critical services running and recover them when they stop — run a business impact analysis, set RTO and RPO, build continuity and disaster-recovery plans on a sound backup strategy, and prove they work with tabletop and recovery exercises.
duration: 55
level: intermediate
preview: true
objectives:
- Distinguish business continuity, disaster recovery and resilience
- Run a business impact analysis to rank what must be recovered first
- Define RTO and RPO and derive a backup and recovery strategy from them
- Describe the 3-2-1 backup rule and why backups must be tested and isolated
- Explain how tabletop and recovery exercises prove a plan actually works
prerequisites:
- Privacy and Data Protection — Governing Personal Data Across Its Lifecycle
---

## Introduction

Every control you have learned so far reduces the chance of a bad event. This module accepts that some bad events happen anyway — a ransomware outbreak, a data-centre fire, a failed migration, a key supplier going dark — and asks a different question: **when a critical service stops, how fast must it come back, how much data can we afford to lose, and have we proven we can actually do it?**

That is business continuity and resilience. It is where the ransomware risk from your register, the backups in your Statement of Applicability, and the SOC's incident response all converge into one tested plan.

## Three related disciplines

| Term | Scope | Core question |
|---|---|---|
| **Business continuity (BC)** | The whole organisation's critical functions | How do we keep (or quickly resume) delivering critical services during a disruption? |
| **Disaster recovery (DR)** | IT systems and data specifically | How do we restore the technology those services depend on? |
| **Resilience** | Design philosophy | How do we build so that disruptions cause less harm in the first place? |

DR is a subset of BC: continuity is about the *business function* (can we still take orders?), disaster recovery is about the *technology* that function needs (is the order database back?). Resilience is the broader goal of absorbing shocks — redundancy, graceful degradation, no single points of failure — so that fewer disruptions become disasters.

:::note Continuity is about functions, not just servers
A classic mistake is to plan only for servers. If the order-processing *server* is restored but the staff who run the process cannot reach a building and the payment *supplier* is also down, the business function is still stopped. BC plans for people, premises, suppliers and processes — not only IT.
:::

## The Business Impact Analysis

You cannot protect everything equally, so the **Business Impact Analysis (BIA)** decides what matters most. For each business function it asks:

1. **How critical is it?** What is the impact of it being down — financial, legal, reputational, safety — and how does that impact grow *over time*?
2. **What does it depend on?** Systems, data, people, suppliers, facilities.
3. **How quickly must it recover?** This sets the RTO (below).
4. **How much recent data can it afford to lose?** This sets the RPO (below).

The BIA output is a ranked list: the functions whose loss hurts fastest and worst are recovered first. This is the same risk-prioritisation logic as your risk register, applied to *disruption* rather than *attack*.

```text
 Impact over time (why timing matters)

 Impact │                                   ███  catastrophic
        │                          ████████
        │                 ████████
        │        ████████
        │███████
        └───────┬────────┬────────┬────────┬────────► time down
               1h        4h       1 day    3 days

 A function tolerable for an hour may be catastrophic after a day.
 The BIA captures that curve, not a single number.
```

## RTO and RPO

Two objectives, set by the BIA, drive every continuity decision. They are easy to confuse, so anchor them firmly:

```text
          RPO ◄──────────── incident ──────────────► RTO
      (how much DATA              (how much TIME to
       you can lose,               restore the service,
       measured backwards)         measured forwards)

   ...last good backup          outage          service restored...
        │                         │                    │
        └──── RPO window ─────────┤                    │
         data created here is        └──── RTO window ──┘
         lost if not yet backed up
```

- **RTO — Recovery Time Objective:** the maximum acceptable *time* to restore a function after it goes down. "Orders must be back within 4 hours."
- **RPO — Recovery Point Objective:** the maximum acceptable *amount of data loss*, expressed as time. "We can lose at most 15 minutes of order data." RPO drives how *often* you back up or replicate: an RPO of 15 minutes means backups/replication at least every 15 minutes.

A memory hook: **RTO = time to be back; RPO = data you can lose.** RTO is measured forward from the incident; RPO is measured backward to the last recoverable point.

:::scenario Deriving a strategy from objectives
The BIA says order processing needs **RTO 4 hours, RPO 15 minutes**. That immediately shapes the technology: a nightly backup gives an RPO of up to 24 hours — far too much loss — so you need near-continuous replication to meet 15 minutes; and a restore that takes a day cannot meet a 4-hour RTO, so you need a warm standby, not a cold one. The payroll system, by contrast, might tolerate **RTO 3 days, RPO 24 hours**, so a nightly backup and a slower rebuild is perfectly adequate — and far cheaper. Objectives set by the BIA tell you where to spend.
:::

Tighter objectives cost more. Matching each function's RTO/RPO to its true criticality — not gold-plating everything — is the whole art, and it is exactly the risk-appetite reasoning from module 1 applied to downtime.

## Backup strategy

Backups are the foundation of recovery, and the enduring guideline is the **3-2-1 rule**:

```text
 3  copies of the data
 2  different media / storage types
 1  copy kept off-site (and, today, offline or immutable)
```

Three things separate a real backup strategy from a false sense of security:

1. **Isolation.** Backups reachable from the production network are encrypted along with everything else in a ransomware attack. At least one copy must be **offline or immutable** so it cannot be altered or deleted by an attacker who holds production — this is exactly the treatment for the ransomware risk `R-004` in your sample register.
2. **Restoration testing.** A backup you have never restored is a hope, not a control. "The backup job reports success" does not prove the data can be recovered intact and within the RTO. Test restores on a schedule — it is the operating-effectiveness test from the controls module applied to backups.
3. **Coverage and retention.** Confirm *what* is backed up (databases, configuration, encryption keys, not just files) and for how long, consistent with the retention rules from the privacy module.

:::warning The backup you never restored is not a backup
Organisations discover during a real disaster that backups were incomplete, corrupt, encrypted by the same attacker, or take three days to restore against a four-hour RTO. Every one of those is found cheaply by a restoration test and catastrophically by an actual incident. If you test only one thing in this module, test a restore.
:::

## Continuity and disaster-recovery plans

The BIA and objectives feed two documents:

- A **Business Continuity Plan (BCP)** covers how critical *functions* continue or resume: alternate work locations, manual workarounds, staff call-trees, supplier fallbacks, and communication to customers and regulators.
- A **Disaster Recovery Plan (DRP)** covers how *IT* is restored: recovery order (dependencies first), step-by-step procedures, who does what, and where the backups and standby systems are.

Both must name **roles**, not people by memory, and both must be reachable **when systems are down** — a DR plan stored only on the server that just failed is useless. Keep an offline/printed copy.

## Proving the plan works: exercises

A plan on a shelf is untested theory. Two kinds of exercise turn it into a capability:

| Exercise | What happens | What it proves |
|---|---|---|
| **Tabletop** | The team talks through a scenario ("ransomware encrypts the file servers overnight") step by step | Gaps in the *plan*, roles and decisions — cheap, low-risk, do often |
| **Recovery / functional test** | You actually restore systems from backup in a test environment, or fail over to standby | That the *technology and RTO/RPO* really work — more effort, run periodically |

Exercises routinely surface the unglamorous failures that sink real recoveries: an out-of-date call-tree, a DR runbook missing a step, a backup that restores but takes twice the RTO, a key nobody can find. Each finding goes into corrective action (the improvement loop from the ISO 27001 and controls modules). The point of an exercise is to **fail cheaply in practice so you do not fail expensively in a real disaster.**

## Common mistakes

- **Planning for servers, not functions.** Restoring IT while people, premises or suppliers are still down leaves the business stopped.
- **One RTO/RPO for everything.** Either you overspend protecting the trivial, or you underspend protecting the critical. Set them per function from the BIA.
- **Backups reachable from production.** Ransomware takes those too. Keep one copy offline or immutable.
- **Never testing restores.** A successful backup *job* is not a proven *recovery*.
- **A DR plan stored only where the disaster hits.** Keep it reachable offline, with roles not names.
- **Writing the plan and never exercising it.** Untested plans fail exactly when you need them.

## Security implications

- Let the BIA, not intuition, rank what to recover first and how fast.
- Derive backup frequency from RPO and recovery architecture from RTO; match both to each function's real criticality.
- Keep at least one backup offline or immutable, and test restores on a schedule.
- Exercise plans regularly (tabletop often, recovery periodically) and feed every finding into corrective action.

## Knowledge check

:::check Questions
1. In one line each, how do RTO and RPO differ?
2. What is the purpose of a Business Impact Analysis?
3. State the 3-2-1 backup rule, and why at least one copy should be offline or immutable.
4. What does a tabletop exercise prove that simply having a written plan does not?
:::

:::solution Answers
1. **RTO (Recovery Time Objective)** is the maximum acceptable *time* to restore a function after an outage (measured forward from the incident); **RPO (Recovery Point Objective)** is the maximum acceptable *data loss*, expressed as time (measured backward to the last recoverable point).
2. To identify and **rank** the organisation's critical functions by the impact of their disruption over time, and capture their dependencies and recovery objectives — so recovery effort and spend go to what matters most, first.
3. **3** copies of the data, on **2** different media/storage types, with **1** kept off-site. At least one copy should be **offline or immutable** so ransomware (or an attacker holding production) cannot encrypt or delete the backups too.
4. That the *plan, roles and decisions actually work* when walked through a realistic scenario — surfacing gaps (stale call-trees, missing steps, unclear decision ownership) that a document sitting unused never reveals.
:::

## Exercise

For a small online retailer with an order-processing system, a marketing website and a payroll system:

1. Run a mini-**BIA**: rank the three functions by criticality and justify the order.
2. Propose an **RTO and RPO** for each, with a one-line reason.
3. For the order system, derive the **backup/recovery approach** its RTO/RPO implies (frequency and recovery architecture).
4. Write a one-paragraph **tabletop scenario** you would use to exercise the ransomware case, and name two gaps you would expect it to surface.

:::solution Model answer (abbreviated)
1. **Order processing** (highest — direct revenue and customer impact, grows severe within hours) → **marketing website** (moderate — reputational, some lost acquisition) → **payroll** (time-sensitive only near pay dates, otherwise tolerant for days).
2. Orders: **RTO 4h / RPO 15min** — revenue-critical, little data loss tolerable. Website: **RTO 8h / RPO 24h** — informational, rebuildable. Payroll: **RTO 3 days / RPO 24h** — only urgent near pay dates.
3. Orders need **near-continuous replication** (to meet RPO 15min — a nightly backup loses up to a day) and a **warm standby** (to meet RTO 4h — a from-scratch restore is too slow), plus an offline/immutable copy for ransomware.
4. *Scenario:* "Overnight, ransomware encrypts the order database and reaches the primary backup share; staff discover it at 07:00 as orders fail." Expected gaps: the primary backup was reachable from production and is also encrypted (need an offline/immutable copy); and the team is unsure who authorises failover and customer communication (unclear decision ownership).
:::

## Summary and next steps

- Business continuity keeps *functions* running; disaster recovery restores the *IT*; resilience designs out single points of failure.
- The **BIA** ranks what to recover first and sets each function's **RTO** (time to restore) and **RPO** (tolerable data loss).
- Those objectives drive backup frequency and recovery architecture; follow **3-2-1**, keep a copy **offline/immutable**, and **test restores**.
- **Tabletop and recovery exercises** turn a written plan into a proven capability and feed corrective action.

The next module, **Third-Party Risk Management**, extends continuity and control thinking to the suppliers — including cloud and payment providers — whose own disruptions can stop your critical functions.

===assignment===
Produce a one-page **continuity brief** for an organisation of your choice: a BIA ranking at least four functions with justification; an RTO and RPO for each with reasons; a backup strategy that states how it meets the tightest RPO and RTO and how it survives ransomware; and a short tabletop plan (scenario, participants, and three questions you would ask during it). Tie the backup strategy to a specific risk in your risk register.

===criteria===
- Complete the reading, the knowledge check and the exercise.
- RTO/RPO must be set per function (not one value for all) and justified; the backup strategy must address isolation and restoration testing, not just "we take backups".
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- ISO 22301:2019, *Security and resilience — Business continuity management systems — Requirements* — https://www.iso.org/standard/75106.html
- ISO/IEC 27031:2011, *Guidelines for ICT readiness for business continuity* — https://www.iso.org/standard/44374.html
- NIST SP 800-34 Rev. 1, *Contingency Planning Guide for Federal Information Systems* — https://csrc.nist.gov/pubs/sp/800/34/r1/upd1/final
- CISA, *Business Continuity and Disaster Recovery resources* — https://www.cisa.gov/topics/critical-infrastructure-security-and-resilience
