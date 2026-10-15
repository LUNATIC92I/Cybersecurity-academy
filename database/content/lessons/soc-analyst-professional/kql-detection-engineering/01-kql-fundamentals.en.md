---
title: KQL Fundamentals for Security Analysts
chapter: Query foundations
summary: Learn the structure of Kusto Query Language and its essential operators — where, project, extend, summarize, bin, join and let — through security-focused examples against Microsoft Sentinel tables.
duration: 75
level: intermediate
objectives:
- Explain the tabular, pipe-based model of KQL and read a query top to bottom
- Filter, project and compute columns with where, project and extend
- Aggregate with summarize, count, dcount, make_set and bin
- Combine data with join and union, and structure queries with let
- Write efficient queries by filtering early on time and indexed columns
prerequisites:
- Windows Event Logs for Investigators
---

## Introduction

Kusto Query Language (KQL) is the query language of Microsoft Sentinel, Microsoft Defender XDR advanced hunting, Azure Monitor Log Analytics and Azure Data Explorer. For many SOC analysts it is the main tool of the job: every investigation, hunt and analytics rule is a KQL query.

KQL is **read-only** and **pipe-based**: data flows from a table through a sequence of operators, each transforming the result of the previous one. Once you understand that model and a dozen operators, you can express most security questions.

:::note Practice environments
Microsoft provides a free demo Log Analytics workspace with sample data, and Azure Data Explorer offers a free cluster with the `help` database. You can practise every query in this lesson without access to a production tenant. Never run exploratory queries against production data you are not authorised to access.
:::

## Query structure

```kusto
SecurityEvent                                   // 1. start from a table
| where TimeGenerated > ago(1d)                 // 2. filter rows (time first!)
| where EventID == 4625                         // 3. more filtering
| project TimeGenerated, Account, IpAddress, Computer   // 4. choose columns
| sort by TimeGenerated desc                    // 5. order
| take 50                                       // 6. limit
```

Rules to remember:

- Each line after the table starts with `|` (the pipe).
- Comments start with `//`.
- String comparisons with `==` are **case-sensitive**; `=~` is case-insensitive.
- Statements are separated by `;` when you use `let`.

## Common Sentinel tables

| Table | Contents |
|---|---|
| `SecurityEvent` | Windows Security events forwarded by the Azure Monitor Agent |
| `SigninLogs` | Microsoft Entra ID interactive sign-ins |
| `AADNonInteractiveUserSignInLogs` | Token refreshes and non-interactive sign-ins |
| `AuditLogs` | Entra ID directory changes (users, groups, apps) |
| `DeviceProcessEvents` | Defender for Endpoint process creation |
| `DeviceNetworkEvents` | Defender for Endpoint network connections |
| `CommonSecurityLog` | CEF logs from firewalls and proxies |
| `Syslog` | Linux and network device syslog |
| `OfficeActivity` | Microsoft 365 workload activity |

## Filtering with `where`

```kusto
SecurityEvent
| where TimeGenerated between (datetime(2026-03-14 00:00) .. datetime(2026-03-14 06:00))
| where EventID in (4624, 4625)
| where Account !endswith "$"            // exclude machine accounts
| where IpAddress !in ("-", "127.0.0.1", "::1")
```

Useful string operators:

| Operator | Meaning | Notes |
|---|---|---|
| `==`, `!=` | Equals / not equals (case-sensitive) | Fastest |
| `=~`, `!~` | Equals (case-insensitive) | |
| `has` | Contains a whole term | Uses the term index — **prefer over `contains`** |
| `contains` | Contains a substring | Slower; scans text |
| `startswith`, `endswith` | Prefix / suffix | |
| `in`, `!in`, `in~` | Membership in a list | |
| `matches regex` | Regular expression | Powerful but slowest |

:::tip Performance rule of thumb
Filter on **time first**, then on **exact or indexed columns** (`EventID`, `has`), and only then on expensive operations (`contains`, `matches regex`, `parse`). Queries that start with a broad regex over 30 days are slow, costly and may time out.
:::

## Selecting and computing columns

```kusto
SecurityEvent
| where TimeGenerated > ago(1h) and EventID == 4688
| project TimeGenerated, Computer, Account, NewProcessName, CommandLine, ParentProcessName
| extend ProcessFile = tostring(split(NewProcessName, "\\")[-1])
| extend IsEncoded = CommandLine has "-enc" or CommandLine has "-EncodedCommand"
```

- `project` keeps (and optionally renames) only listed columns: `project User = Account`.
- `project-away` removes columns.
- `extend` adds computed columns without removing others.

## Aggregation with `summarize`

`summarize` groups rows and computes aggregates — the heart of detection logic.

```kusto
SecurityEvent
| where TimeGenerated > ago(1d) and EventID == 4625
| summarize Failures = count(),
            DistinctAccounts = dcount(Account),
            Accounts = make_set(Account, 20),
            FirstSeen = min(TimeGenerated),
            LastSeen  = max(TimeGenerated)
          by IpAddress
| sort by Failures desc
```

Common aggregation functions: `count()`, `dcount()` (approximate distinct count), `countif()`, `sum()`, `avg()`, `min()`, `max()`, `make_set()`, `make_list()`, `arg_max()` (row with the maximum value), `percentile()`.

### Time buckets with `bin`

```kusto
SecurityEvent
| where TimeGenerated > ago(1d) and EventID == 4625
| summarize Failures = count() by IpAddress, bin(TimeGenerated, 5m)
| where Failures > 20
```

`bin(TimeGenerated, 5m)` rounds each timestamp down to its 5-minute window, so you count events **per source per window** — the basis of rate-based detections.

### Visualising

```kusto
SigninLogs
| where TimeGenerated > ago(7d)
| summarize SignIns = count() by bin(TimeGenerated, 1h), ResultType
| render timechart
```

## Combining data: `join` and `union`

`union` stacks rows from several tables:

```kusto
union SigninLogs, AADNonInteractiveUserSignInLogs
| where TimeGenerated > ago(1d) and UserPrincipalName =~ "a.martin@contoso.example"
```

`join` matches rows from two tables on a key:

```kusto
let failures = SecurityEvent
  | where TimeGenerated > ago(1h) and EventID == 4625
  | summarize FailedCount = count() by IpAddress, Account;
let successes = SecurityEvent
  | where TimeGenerated > ago(1h) and EventID == 4624
  | project SuccessTime = TimeGenerated, IpAddress, Account, LogonType;
failures
| where FailedCount >= 10
| join kind=inner successes on IpAddress, Account
```

Join kinds you need: `inner` (matches on both sides), `leftouter` (all left rows), `leftanti` (left rows with **no** match — great for "seen today but never before"), `innerunique` (the default, which de-duplicates the left side — be aware of it).

## Structuring with `let`

`let` names a value, a list or a sub-query, making queries readable and reusable:

```kusto
let lookback = 14d;
let threshold = 25;
let excludedIPs = dynamic(["10.0.0.5", "10.0.0.6"]);   // vulnerability scanners
SecurityEvent
| where TimeGenerated > ago(lookback) and EventID == 4625
| where IpAddress !in (excludedIPs)
| summarize Failures = count() by IpAddress
| where Failures > threshold
```

## Working with dynamic and nested data

Many logs contain JSON. KQL's `dynamic` type and functions handle it:

```kusto
SigninLogs
| where TimeGenerated > ago(1d)
| extend City    = tostring(LocationDetails.city),
         Country = tostring(LocationDetails.countryOrRegion),
         OS      = tostring(DeviceDetail.operatingSystem)
| project TimeGenerated, UserPrincipalName, IPAddress, City, Country, OS, ResultType
```

- Dot or bracket notation accesses properties: `LocationDetails.city` or `LocationDetails["city"]`.
- Always cast with `tostring()`, `toint()`, `todatetime()` before comparing or grouping.
- `mv-expand` turns an array into multiple rows; `parse_json()` converts a JSON string to `dynamic`.
- `parse` extracts fields from free text: `| parse CommandLine with * "-enc " Encoded " " *`.

## A complete investigative query

Question: *Which accounts signed in successfully from more than one country in the last 24 hours?*

```kusto
SigninLogs
| where TimeGenerated > ago(1d)
| where ResultType == "0"                                  // success
| extend Country = tostring(LocationDetails.countryOrRegion)
| where isnotempty(Country)
| summarize Countries = make_set(Country), CountryCount = dcount(Country),
            IPs = make_set(IPAddress, 10), First = min(TimeGenerated), Last = max(TimeGenerated)
          by UserPrincipalName
| where CountryCount > 1
| sort by CountryCount desc
```

This is the starting point for **impossible-travel** investigation; you would then check timing, VPN egress IPs and device details before concluding anything.

## Common mistakes

- **Forgetting a time filter** — scans the whole retention period.
- **Using `contains` everywhere** — prefer `has` for whole terms.
- **Case-sensitive mismatches** — `Account == "ADMIN"` will not match `admin`; use `=~`.
- **Comparing dynamic values without casting** — `LocationDetails.city == "Paris"` may silently fail; use `tostring()`.
- **Assuming `join` defaults to inner** — the default is `innerunique`; specify `kind=` explicitly.
- **Ignoring ingestion delay** — scheduled rules looking back exactly 5 minutes may miss late events; overlap windows slightly.

## Security implications

- Queries can expose personal data; follow your organisation's data-access policies and avoid exporting results unnecessarily.
- Saved queries and analytics rules are code: review them, version them and test them.

## Knowledge check

:::check Questions
1. What is the difference between `has` and `contains`?
2. What does `bin(TimeGenerated, 10m)` do inside `summarize`?
3. Which join kind returns left rows that have no match on the right?
4. Why should the time filter come first?
:::

:::solution Answers
1. `has` matches whole indexed terms and is fast; `contains` matches any substring and must scan text.
2. It groups timestamps into **10-minute buckets** so aggregates are computed per window.
3. `leftanti`.
4. It reduces the amount of data scanned by every subsequent operator, improving speed and cost.
:::

## Exercise

Write KQL for each question (assume the standard Sentinel tables):

1. The top 10 computers by number of process creations (Event ID 4688) in the last 24 hours.
2. Every PowerShell process (from `SecurityEvent` 4688) whose command line contains an encoded command in the last 7 days, showing time, computer, account and command line.
3. Accounts added to any security group (Event IDs 4728, 4732, 4756) in the last 24 hours, with who made the change.

:::solution Model answers
```kusto
// 1
SecurityEvent
| where TimeGenerated > ago(1d) and EventID == 4688
| summarize Processes = count() by Computer
| top 10 by Processes

// 2
SecurityEvent
| where TimeGenerated > ago(7d) and EventID == 4688
| where NewProcessName has "powershell"
| where CommandLine has_any ("-enc", "-EncodedCommand", "-e ")
| project TimeGenerated, Computer, Account, CommandLine

// 3
SecurityEvent
| where TimeGenerated > ago(1d) and EventID in (4728, 4732, 4756)
| project TimeGenerated, Computer, ChangedBy = SubjectAccount,
          MemberAdded = MemberName, Group = TargetUserName, EventID
```
:::

## Summary and next steps

- KQL is a pipeline: table → filter → shape → aggregate → output.
- `where`, `project`, `extend`, `summarize`, `bin`, `join`, `union` and `let` cover most needs.
- Filter early on time and indexed columns; cast dynamic values explicitly.

Next you will apply these operators to a complete detection: **brute-force and password-spraying attacks**.

===assignment===
In the Microsoft demo workspace or Azure Data Explorer's `help` cluster, write three queries of your own: one using `summarize ... by bin()`, one using `join kind=leftanti` to find something "new", and one that parses a dynamic column. Save each with a comment explaining the question it answers and what you found.

===criteria===
- Complete the reading, knowledge check and query exercise.
- Mark the lesson complete; the KQL query tasks in the brute-force lab require these operators.

===references===
- Microsoft Learn, *Kusto Query Language overview* — https://learn.microsoft.com/kusto/query/
- Microsoft Learn, *summarize operator* — https://learn.microsoft.com/kusto/query/summarize-operator
- Microsoft Learn, *join operator* — https://learn.microsoft.com/kusto/query/join-operator
- Microsoft Learn, *Best practices for KQL queries* — https://learn.microsoft.com/kusto/query/best-practices
- Microsoft Sentinel data reference (SecurityEvent, SigninLogs) — https://learn.microsoft.com/azure/azure-monitor/reference/tables/securityevent
