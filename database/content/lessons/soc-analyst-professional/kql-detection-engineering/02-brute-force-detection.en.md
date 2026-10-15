---
title: Detecting Brute-Force and Password-Spraying Attacks
chapter: Identity detections
summary: Build, tune and validate KQL detections for brute force, password spraying and the critical "failures followed by success" pattern across Windows and Microsoft Entra ID logs.
duration: 70
level: intermediate
objectives:
- Distinguish brute force, password spraying and credential stuffing by their log signatures
- Write KQL detections for each pattern using thresholds, time windows and distinct counts
- Detect a successful logon that follows a burst of failures from the same source
- Tune detections to reduce false positives and map them to MITRE ATT&CK T1110
- Document an analytics rule with severity, entity mapping and a triage playbook
prerequisites:
- KQL Fundamentals for Security Analysts
- Windows Event Logs for Investigators
resources:
- Synthetic Windows authentication events (CSV) | soc-auth-events.csv
---

## Introduction

Credential attacks are among the most frequent initial-access techniques because they are cheap and frequently succeed against accounts without MFA. They also generate abundant, recognisable telemetry. This lesson turns that telemetry into reliable detections, and — just as importantly — shows how to stop those detections from drowning the SOC in noise.

## Three attack patterns

All belong to MITRE ATT&CK **T1110 — Brute Force**:

| Sub-technique | Pattern | Log signature |
|---|---|---|
| T1110.001 Password Guessing | Many passwords tried against **one** account | High failures for one account, often from one source |
| T1110.003 Password Spraying | **One or few** common passwords tried against **many** accounts | Few failures per account, many distinct accounts per source |
| T1110.004 Credential Stuffing | Leaked username/password **pairs** tried | Many accounts, failures mixed with occasional successes, often from many IPs |

Spraying is designed to stay below per-account lockout thresholds, which is why per-account detections miss it and per-source distinct-account counts catch it.

## The data

**Windows (SecurityEvent)** — Event ID 4625 (failure) and 4624 (success). Useful fields: `Account`, `TargetUserName`, `IpAddress`, `LogonType`, `SubStatus`, `Computer`.

**Microsoft Entra ID (SigninLogs)** — `ResultType` holds the error code ("0" means success). Codes frequently seen in credential attacks:

| ResultType | Meaning |
|---|---|
| 50126 | Invalid username or password |
| 50053 | Account locked (smart lockout) or sign-in from malicious IP blocked |
| 50055 | Password expired |
| 50057 | Account disabled |
| 50074 / 50076 | Strong authentication (MFA) required |
| 500121 | MFA failed or denied |

:::note Why MFA codes matter
A correct password followed by `50074` or `500121` means the **password is compromised** even though the sign-in did not complete. Treat this as a confirmed credential compromise that MFA happened to stop.
:::

## Detection 1 — Brute force against a single account

```kusto
let window = 10m;
let threshold = 20;
SecurityEvent
| where TimeGenerated > ago(1h)
| where EventID == 4625
| where Account !endswith "$"
| summarize Failures = count(), Sources = make_set(IpAddress, 10)
          by TargetAccount = tolower(Account), Computer, bin(TimeGenerated, window)
| where Failures >= threshold
```

Choices to justify:

- **Window and threshold**: 20 failures in 10 minutes is far above typical human error (a user rarely fails more than 3–5 times) but low enough to catch slow attacks. Calibrate using your own baseline.
- **Lower-casing** the account avoids splitting `ADMIN` and `admin` into two groups.
- **Excluding machine accounts** (`$`) removes Kerberos/NTLM noise.

## Detection 2 — Password spraying from one source

```kusto
let window = 30m;
let minAccounts = 10;
SecurityEvent
| where TimeGenerated > ago(1d)
| where EventID == 4625 and SubStatus =~ "0xc000006a"      // valid user, wrong password
| summarize DistinctAccounts = dcount(TargetUserName),
            Attempts = count(),
            Accounts = make_set(TargetUserName, 50)
          by IpAddress, bin(TimeGenerated, window)
| where DistinctAccounts >= minAccounts
| extend AttemptsPerAccount = round(todouble(Attempts) / DistinctAccounts, 1)
| where AttemptsPerAccount <= 3                               // the spraying shape
```

The final filter is what distinguishes spraying from a misconfigured service hammering one account: **many accounts, few attempts each**.

The Entra ID equivalent:

```kusto
SigninLogs
| where TimeGenerated > ago(1d)
| where ResultType == "50126"
| summarize DistinctUsers = dcount(UserPrincipalName), Attempts = count(),
            Users = make_set(UserPrincipalName, 50), Apps = make_set(AppDisplayName, 10)
          by IPAddress, bin(TimeGenerated, 1h)
| where DistinctUsers >= 10
```

## Detection 3 — Failures followed by a success (highest value)

A burst of failures that ends in a success from the same source is the moment an attack becomes an incident.

```kusto
let lookback = 1h;
let failThreshold = 10;
let failures = SecurityEvent
  | where TimeGenerated > ago(lookback)
  | where EventID == 4625 and Account !endswith "$"
  | summarize FailedAttempts = count(), FirstFailure = min(TimeGenerated), LastFailure = max(TimeGenerated)
            by IpAddress, TargetAccount = tolower(Account);
let successes = SecurityEvent
  | where TimeGenerated > ago(lookback)
  | where EventID == 4624 and Account !endswith "$"
  | project SuccessTime = TimeGenerated, IpAddress, TargetAccount = tolower(Account), LogonType, Computer;
failures
| where FailedAttempts >= failThreshold
| join kind=inner successes on IpAddress, TargetAccount
| where SuccessTime > LastFailure and SuccessTime - LastFailure < 30m
| project IpAddress, TargetAccount, FailedAttempts, FirstFailure, LastFailure, SuccessTime, LogonType, Computer
```

The time condition ensures the success comes **after** the failures and close to them, which removes coincidental matches such as a user who failed in the morning and logged in normally in the afternoon.

```text
Timeline that this rule catches

 01:52 ─ 4625 ─ 4625 ─ 4625 ─ ... (38 failures) ... ─ 4625 01:56:20
                                                        │
 01:56:41 ──────────────────────────────────────── 4624  ← ALERT (success 21 s after last failure)
```

## Tuning: from noisy to trustworthy

Expect false positives from:

- **Vulnerability scanners and monitoring tools** with stale credentials → exclude by known IP *and* account, never by IP alone.
- **Service accounts with expired passwords** retrying every few seconds → these show the *same* account and *same* source for hours; investigate once, fix the credential, then exclude temporarily with an expiry date.
- **NAT and VPN egress addresses** where many users share one IP → spraying logic may trigger on a busy office gateway. Add the egress IPs to a watchlist and require a higher threshold for them.
- **Mobile mail clients** retrying an old password after a change.

Tuning techniques:

```kusto
let scanners = _GetWatchlist('KnownScanners') | project IP = tostring(SearchKey);
SecurityEvent
| where TimeGenerated > ago(1h) and EventID == 4625
| where IpAddress !in (scanners)
// ...
```

- Use **watchlists** for exclusions so they are reviewed and auditable, not hard-coded in rules.
- Add **context columns** (account department, asset criticality) rather than suppressing.
- Raise severity when the target is privileged (members of admin groups) or the source is external.

## Validating a detection

A detection is not finished until you have shown it fires on the attack and stays quiet otherwise.

1. **Positive test** — replay or generate the attack in a lab (e.g. repeated wrong passwords against a test account) and confirm the alert.
2. **Negative test** — run the query over two weeks of historical data and review every hit: how many would have been false positives?
3. **Boundary test** — what happens at threshold − 1? At a slower attack rate (one attempt per minute)?
4. **Document** — description, ATT&CK mapping, data sources, thresholds and their rationale, known false positives, triage steps.

## From query to analytics rule

In Microsoft Sentinel, a scheduled analytics rule combines the query with:

- **Schedule**: run every 10 minutes, look back 1 hour (overlap compensates for ingestion delay).
- **Alert threshold**: generate an alert when results > 0.
- **Entity mapping**: map `TargetAccount` to *Account* and `IpAddress` to *IP* so incidents correlate across rules.
- **Severity**: Medium for spraying with no success; **High** for failures followed by success.
- **Event grouping / suppression**: group all results into a single alert per run to avoid floods.
- **Tactics**: Credential Access; technique T1110.

## Triage playbook (Tier 1)

1. Confirm the pattern: number of failures, accounts, time span, source IP.
2. Enrich the source: internal or external? Known scanner? Geolocation and reputation? Other alerts from it?
3. Check for success: any 4624 / ResultType 0 from the same source for targeted accounts?
4. If **no success**: block the source if external and malicious, notify identity team, close with notes.
5. If **success**: treat as compromised — escalate to Tier 2, disable or reset the account, revoke sessions, review activity after the success (mailbox rules, new MFA methods, process execution, lateral movement).

## Common mistakes

- **Per-account thresholds only** — spraying passes underneath.
- **Excluding by IP alone** — an attacker on a compromised scanner host becomes invisible.
- **No time ordering** in the success join — produces misleading alerts.
- **Ignoring non-interactive sign-ins** in Entra ID — attackers using legacy protocols may appear only there.

## Security implications

- The best mitigation is **phishing-resistant MFA** and blocking legacy authentication; detections are the safety net.
- Smart lockout and conditional access reduce attack effectiveness but do not replace monitoring.

## Knowledge check

:::check Questions
1. Which feature of the data distinguishes password spraying from brute force?
2. Why does Detection 3 require `SuccessTime > LastFailure`?
3. A user's password is correct but sign-in returns ResultType 500121. What should you conclude?
4. Why use a watchlist instead of hard-coding exclusions?
:::

:::solution Answers
1. Spraying shows **many distinct accounts with few attempts each** from one source; brute force shows **many attempts against one account**.
2. To ensure the success **follows** the failures, eliminating coincidental successes that occurred earlier.
3. The **password is compromised**; MFA blocked the sign-in. Reset the password and investigate.
4. Watchlists are centrally managed, reviewable and auditable, and can be updated without editing every rule.
:::

## Exercise

Download `soc-auth-events.csv` (attached to this lesson) and, using any tool you like (spreadsheet, `grep`, or the in-browser viewer in the related lab):

1. Identify the source IP responsible for the brute-force burst.
2. Count its failed attempts.
3. Identify the account that was eventually compromised and the time of the successful logon.
4. Write the KQL you would deploy to detect this pattern in production.

:::solution Model answer
1. **10.0.9.77**.
2. **38** failed attempts (Event ID 4625, SubStatus `0xC000006A`).
3. **svc_backup**, successful network logon (type 3) at **2026-03-14 01:56:41 UTC**.
4. Detection 3 from this lesson (failures followed by success), with `failThreshold = 10` and a 30-minute success window, entity-mapped to Account and IP, severity High.
:::

## Summary and next steps

- Brute force, spraying and stuffing leave different shapes in the data; detect each deliberately.
- "Failures then success" is the highest-value identity detection.
- A detection includes thresholds with rationale, tuning via watchlists, validation tests and a triage playbook.

Complete the **SOC Lab: Brute-Force Investigation** to apply this lesson to a full dataset, scored on the server.

===assignment===
Write a one-page **detection specification** for Detection 3 using this template: name; description; ATT&CK mapping; data sources; query; schedule and look-back; thresholds and rationale; entity mapping; severity logic; known false positives and tuning; triage steps; validation evidence (describe how you tested it).

===criteria===
- Complete the reading, knowledge check and dataset exercise.
- Mark the lesson complete and pass the SOC brute-force lab.

===references===
- MITRE ATT&CK T1110, Brute Force (and sub-techniques) — https://attack.mitre.org/techniques/T1110/
- Microsoft Learn, *Microsoft Entra authentication and authorization error codes* — https://learn.microsoft.com/entra/identity-platform/reference-error-codes
- Microsoft Learn, *Create scheduled analytics rules in Microsoft Sentinel* — https://learn.microsoft.com/azure/sentinel/create-analytics-rules
- Microsoft Learn, *Watchlists in Microsoft Sentinel* — https://learn.microsoft.com/azure/sentinel/watchlists
- CISA, *Phishing-resistant MFA fact sheet* — https://www.cisa.gov/resources-tools/resources/implementing-phishing-resistant-mfa
