---
title: Third-Party Risk Management — Assessing and Monitoring Suppliers
chapter: Third-Party Risk Management
summary: Manage the risk that comes through the suppliers you depend on — inventory them, tier them by criticality, run due diligence, embed security in contracts, and monitor them over the whole relationship, from onboarding to offboarding.
duration: 55
level: intermediate
preview: true
objectives:
- Explain why third parties concentrate risk and why accountability stays with you
- Inventory and tier suppliers by criticality and data access
- Run proportionate due diligence using security questionnaires and evidence
- Embed security, privacy and continuity requirements in contracts
- Monitor suppliers across the lifecycle and offboard them safely
prerequisites:
- Business Continuity and Resilience — BIA, RTO and RPO
---

## Introduction

Almost nothing a modern organisation does is fully in-house. Payroll, email, hosting, payments, analytics, support tooling — each is a **third party** that processes your data or runs part of your operation. Every one is also a path for risk: a breach at a supplier is your breach too, and an outage at a supplier can stop your critical function just as surely as your own servers failing.

This lesson treats suppliers as risks to be governed, using the same engine as the rest of the path — identify, assess, treat, monitor — adapted to relationships you influence by contract rather than control directly. It ties together threads you have already met: the controller/processor split from privacy, the continuity dependencies from the last module, and the risk register throughout.

## Why third parties concentrate risk

- **You delegate the work, not the accountability.** As the privacy module established, a controller stays accountable for personal data even when a processor handles it. Outsourcing a function does not outsource responsibility for the risk.
- **Their weakness becomes your exposure.** An attacker who cannot breach you directly may breach a supplier with access to you — the supply-chain attack vector from the SOC path.
- **Concentration multiplies impact.** When many organisations depend on the same cloud, payment or software provider, one supplier incident becomes many organisations' incident at once.
- **You have less visibility.** You cannot audit a supplier's internals the way you audit your own; you must rely on evidence, contracts and monitoring instead of direct control.

:::note The fourth party problem
Your suppliers have suppliers. The cloud vendor you vetted may itself depend on a sub-processor you never assessed — a *fourth party*. You cannot assess everyone, but critical suppliers should disclose their key sub-processors and be contractually bound to hold them to equivalent standards, so the chain of accountability does not simply vanish one link down.
:::

## Inventory and tiering

You cannot manage suppliers you have not listed. Start with a **supplier inventory**: who they are, what service they provide, **what data they access**, and **which of your functions depend on them**. Then **tier** them by criticality — because you cannot and should not assess a stationery vendor as hard as your payment processor.

| Tier | Typical characteristics | Treatment |
|---|---|---|
| **Critical** | Accesses sensitive/personal data, or a continuity dependency for a critical function | Deep due diligence, strong contract terms, ongoing monitoring |
| **Important** | Some data access or moderate operational reliance | Standard questionnaire and contract, periodic review |
| **Low** | No sensitive data, easily replaced, little operational impact | Light-touch; basic checks |

Tiering is risk-based prioritisation again: it is driven by **data sensitivity** (from the privacy classification) and **operational dependency** (from the BIA). A supplier that both holds personal data *and* supports a critical function is unambiguously critical.

## Due diligence

Before onboarding — and periodically after — assess the supplier **proportionate to its tier**. Common instruments:

- **Security questionnaires.** Standardised question sets (e.g. a SIG-style questionnaire) covering the supplier's controls: access control, encryption, patching, incident response, business continuity, sub-processors.
- **Independent assurance.** Rather than trusting self-reported answers, ask for *evidence*: an ISO/IEC 27001 certificate (and its **Statement of Applicability / scope** — does the scope actually cover the service you buy?), a SOC 2 report, recent penetration-test summaries.
- **Privacy and legal checks.** Where the supplier processes personal data: lawful basis, data location, sub-processors, and the data-protection terms (connecting to international transfers from the privacy module).
- **Financial and operational viability.** A supplier that may go out of business is a continuity risk regardless of its security.

:::tip A certificate is a claim until you read its scope
"We're ISO 27001 certified" means little until you check *what* is in scope. A certificate covering only the corporate head office does not assure the cloud service you are buying. Treat supplier attestations exactly as you treat your own controls (from the controls module): a claim to verify with evidence, not to accept on faith. Ask for the certificate, the scope, and the Statement of Applicability.
:::

Record the result as a **risk score** for the supplier and feed material findings into your risk register with an owner and a treatment — a weak answer is a risk to mitigate (require a fix), transfer (contract liability), avoid (choose another supplier), or consciously accept.

## Contracts: where the controls get teeth

With a third party you cannot enforce controls directly, so the **contract** is your primary control. Security-relevant clauses to seek, scaled to tier:

- **Security requirements** — the controls the supplier must maintain (e.g. encryption, MFA, patching timelines).
- **Breach notification** — the supplier must tell you of a security or personal-data breach within a defined, short period, so you can meet *your* regulatory clock (privacy module).
- **Right to audit / assess** — the ability to request evidence, assessments, or (for critical suppliers) an audit.
- **Sub-processor control** — disclosure of sub-processors and a duty to bind them to equivalent terms.
- **Data handling and return/deletion** — where data may be stored, and secure return or destruction at the end (offboarding, below).
- **Continuity and exit** — availability commitments (SLAs) and support for an orderly exit.

A contract clause only helps if someone checks it is honoured — which is what monitoring is for.

## Monitoring across the lifecycle

Third-party risk is not a one-time gate at onboarding; it runs the length of the relationship.

```text
 ONBOARD ──► OPERATE / MONITOR ──────────────► OFFBOARD
    │                 │                             │
 due diligence   periodic re-assessment,      return/delete data,
 + contract      watch for incidents,         revoke access,
 + tiering       SLA & KRI tracking           confirm destruction
```

- **Onboard** — tier, assess, contract, and grant only the access the service needs (least privilege applies to suppliers too).
- **Operate / monitor** — re-assess on a cadence matched to tier; track supplier **Key Risk Indicators** (missed SLAs, disclosed incidents, expired certificates, news of a breach); re-check when the service or data scope changes.
- **Offboard** — the most-forgotten stage. On exit, **revoke access**, ensure data is returned or securely destroyed (with confirmation), and remove the supplier from systems that still trust it. A terminated supplier whose API key still works is a live risk.

:::warning Offboarding is a security control, not an afterthought
Breaches regularly trace back to a former supplier whose credentials, VPN access or API keys were never revoked. Treat offboarding with the same rigour as onboarding: a checklist that revokes every access, confirms data destruction in writing, and closes the loop. "We stopped using them" is not the same as "they can no longer reach our data".
:::

## Common mistakes

- **No inventory.** You cannot manage or tier suppliers you have not listed — and shadow suppliers signed up by individual teams are the ones that bite.
- **Assessing everyone the same.** Either you exhaust the team on trivial vendors or under-assess the critical ones. Tier first.
- **Trusting the badge, not the scope.** An ISO/SOC attestation whose scope excludes your service assures nothing.
- **Security that lives only in the questionnaire.** If requirements are not in the contract, you cannot enforce them.
- **One-and-done.** A supplier assessed only at onboarding can degrade for years unnoticed.
- **Forgetting to offboard.** Lingering access and un-destroyed data turn a former supplier into a standing risk.

## Security implications

- Maintain a living supplier inventory and tier it by data sensitivity and operational dependency.
- Make due diligence proportionate; verify attestations by reading scope and evidence, not the badge.
- Put security, breach-notification, audit, sub-processor and exit requirements in the contract — that is how controls get teeth.
- Monitor across the lifecycle and offboard deliberately: revoke access and confirm data destruction.

## Knowledge check

:::check Questions
1. If you outsource a function to a supplier, what happens to your accountability for the associated risk?
2. What two factors most drive how you tier a supplier?
3. Why is reading a certificate's scope more important than seeing that the supplier "is certified"?
4. Name two offboarding actions that reduce third-party risk when a supplier relationship ends.
:::

:::solution Answers
1. It **stays with you**. You can delegate the work and some operational responsibility, but accountability for the risk (and, for personal data, the controller's responsibility) remains yours.
2. **Data sensitivity** (what/how sensitive the data the supplier accesses) and **operational dependency** (how critical the function they support is, from the BIA).
3. Because a certificate only assures what is **in scope**; one covering, say, only the head office does not assure the specific service you are buying. The scope (and Statement of Applicability) tells you whether the attestation is relevant.
4. Any two of: **revoke all access** (accounts, VPN, API keys); **ensure data is returned or securely destroyed, with written confirmation**; **remove the supplier from systems that still trust it**; update the inventory/risk register.
:::

## Exercise

For an organisation using a cloud hosting provider, a payroll SaaS, and a marketing-analytics tool:

1. **Tier** each supplier (critical / important / low) and justify using data access and operational dependency.
2. For the **critical** one, list four due-diligence items you would require, including one piece of independent evidence.
3. Draft three **contract clauses** you would insist on for that supplier.
4. Describe the **offboarding** steps if you later replaced it.

:::solution Model answer (abbreviated)
1. Cloud hosting → **critical** (hosts systems and data; a continuity dependency). Payroll SaaS → **critical/important** (holds sensitive personal data, but used periodically). Marketing analytics → **important or low** depending on whether it receives personal data; if only aggregate/pseudonymous, low.
2. For cloud hosting: a completed security questionnaire; the **ISO 27001 certificate with its scope and Statement of Applicability** (independent evidence) or a SOC 2 report; data-location and sub-processor disclosure; and evidence of a tested business-continuity/DR capability.
3. (a) Maintain stated security controls (encryption, MFA, patch timelines); (b) notify us of any breach within a short defined period; (c) support secure data return/deletion and an orderly exit, with availability SLAs.
4. **Offboard:** migrate data out; revoke all accounts, keys and network access; obtain written confirmation that our data has been securely destroyed; remove the provider from any systems that trusted it; and update the inventory and risk register.
:::

## Summary and next steps

- Third parties concentrate risk because you delegate the work but keep the accountability, and their weaknesses become your exposure.
- Inventory suppliers and **tier** them by data sensitivity and operational dependency; assess each proportionately.
- Verify attestations by scope and evidence, and put security, breach-notification, audit, sub-processor and exit terms in the **contract**.
- Manage the whole lifecycle — onboard, monitor, and **offboard** by revoking access and confirming data destruction.

The remaining modules — **GRC Tools and Documentation** and the **GRC Capstone** — bring the whole path together: the registers, matrices and forms that make all of this auditable, and a simulated end-to-end organisational assessment.

===assignment===
Produce a one-page **third-party risk sheet** for an organisation of your choice: a supplier inventory of at least four suppliers with service, data accessed, dependent function and an assigned tier; a proportionate due-diligence checklist for your most critical supplier (including at least one independent evidence item and a note on verifying its scope); three contract clauses you would require; and an offboarding checklist. Tie at least one supplier to a risk in your risk register with an owner and treatment.

===criteria===
- Complete the reading, the knowledge check and the exercise.
- Tiering must be justified by data sensitivity and operational dependency; due diligence must verify evidence and scope, not just accept a badge; and the offboarding checklist must revoke access and confirm data destruction.
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- ISO/IEC 27036 (series), *Information security for supplier relationships* — https://www.iso.org/standard/59648.html
- NIST SP 800-161 Rev. 1, *Cybersecurity Supply Chain Risk Management Practices* — https://csrc.nist.gov/pubs/sp/800/161/r1/final
- NIST Cybersecurity Framework 2.0, *Govern — Cybersecurity Supply Chain Risk Management (GV.SC)* — https://www.nist.gov/cyberframework
- Shared Assessments, *Standardized Information Gathering (SIG) questionnaire* — https://sharedassessments.org/sig/
