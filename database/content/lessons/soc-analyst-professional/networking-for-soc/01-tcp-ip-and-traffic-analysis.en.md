---
title: TCP/IP, the Handshake and Suspicious Traffic
chapter: Network fundamentals
summary: Understand the TCP/IP model, follow a TCP connection packet by packet, and recognise the traffic patterns of scanning, beaconing and exfiltration in flow logs and packet captures.
duration: 60
level: beginner
objectives:
- Map common protocols to the layers of the TCP/IP and OSI models
- Explain the TCP three-way handshake, connection teardown and the meaning of TCP flags
- Use Wireshark display filters and tcpdump to isolate relevant traffic
- Recognise port scanning, command-and-control beaconing and data exfiltration patterns
prerequisites:
- Threats, Vulnerabilities, Risks and Controls
- Ability to read an IPv4 address and a port number
resources:
- Sample firewall/flow log (CSV) | soc-network-flows.csv
---

## Introduction

Almost every intrusion crosses a network. Even when endpoint logs are deleted, network evidence — firewall logs, proxy logs, DNS logs, NetFlow, packet captures — often survives on separate systems. To use it, an analyst must be able to read it fluently. This lesson builds that fluency from the protocol stack up to concrete detection patterns.

## The models: OSI and TCP/IP

The **OSI model** has seven layers and is mainly a teaching and troubleshooting tool. The **TCP/IP model** has four layers and reflects how the internet is actually built.

| TCP/IP layer | OSI layers | Examples | What the SOC sees |
|---|---|---|---|
| Application | 7 Application, 6 Presentation, 5 Session | HTTP, DNS, SMTP, SSH, RDP, TLS | URLs, domains, user agents, email headers |
| Transport | 4 Transport | TCP, UDP | Ports, connection state, flags |
| Internet | 3 Network | IPv4, IPv6, ICMP | Source/destination IPs, TTL |
| Link | 2 Data link, 1 Physical | Ethernet, Wi-Fi, ARP | MAC addresses, VLANs |

When you read "layer 7 firewall", it means a firewall that inspects application content (for example, URL categories), not just IP addresses and ports.

## Addressing essentials

### IPv4 and CIDR

An IPv4 address has 32 bits, written as four octets: `192.168.10.25`. CIDR notation adds the network prefix length: `192.168.10.0/24` means the first 24 bits identify the network, leaving 8 bits (256 addresses, 254 usable hosts) for hosts.

Private ranges (RFC 1918) that you will see constantly in internal logs:

```text
10.0.0.0/8        (10.0.0.0 – 10.255.255.255)
172.16.0.0/12     (172.16.0.0 – 172.31.255.255)
192.168.0.0/16    (192.168.0.0 – 192.168.255.255)
```

Also recognise `127.0.0.0/8` (loopback), `169.254.0.0/16` (link-local, often a DHCP failure) and documentation ranges such as `198.51.100.0/24` and `203.0.113.0/24`, which are used in examples throughout this course.

:::tip Quick subnet arithmetic
Hosts per subnet = 2^(32 − prefix) − 2. A `/26` gives 2^6 − 2 = 62 usable hosts. Knowing whether two IPs share a subnet tells you whether traffic between them crosses a router or firewall — and therefore whether a firewall log should exist.
:::

### IPv6

IPv6 addresses have 128 bits, written in hexadecimal groups (`2001:db8:85a3::8a2e:370:7334`). Many networks run IPv6 alongside IPv4 without monitoring it equally — a classic blind spot. Ensure your SIEM parses IPv6 fields and that detections are not written only for IPv4 formats.

## Transport: TCP and UDP

**UDP** is connectionless: packets are sent without setup or delivery guarantees. DNS (usually), DHCP, NTP and many streaming protocols use UDP.

**TCP** is connection-oriented and reliable. It establishes a session with the **three-way handshake**:

```text
Client 10.0.5.20:51544                      Server 203.0.113.10:443
        │ ── SYN (seq=x) ───────────────────────► │
        │ ◄────────────── SYN-ACK (seq=y, ack=x+1)│
        │ ── ACK (ack=y+1) ──────────────────────► │
        │        ... application data (TLS) ...     │
        │ ── FIN ─────────────────────────────────► │
        │ ◄──────────────────────────────── FIN/ACK │
        │ ── ACK ─────────────────────────────────► │
```

TCP flags you must recognise:

| Flag | Meaning | Analyst interpretation |
|---|---|---|
| SYN | Open a connection | Many SYNs to many ports without completion → port scan |
| ACK | Acknowledge data | Present on almost all packets after the handshake |
| FIN | Graceful close | Normal teardown |
| RST | Abort / refuse | RST in response to SYN → port closed |
| PSH | Push data to application | Interactive data transfer |
| URG | Urgent | Rare; unusual combinations may indicate crafted packets |

A **SYN followed by RST/ACK** from the target means the port is closed. **SYN with no reply** often means a firewall silently dropped it. **SYN, SYN-ACK, then RST from the client** is the signature of a "half-open" SYN scan (`nmap -sS`).

## Protocols the SOC sees every day

| Protocol | Port(s) | Notes for analysts |
|---|---|---|
| DNS | 53/UDP, 53/TCP; 853 (DoT); 443 (DoH) | Every connection starts with a lookup — invaluable for detection |
| DHCP | 67/68 UDP | Maps IPs to MAC/hostnames at a point in time — essential for attribution |
| HTTP / HTTPS | 80 / 443 | Most C2 traffic hides in HTTPS; inspect SNI, JA3/JA4, certificate data |
| SMTP | 25, 587, 465 | Email delivery; outbound 25 from workstations is suspicious |
| SSH | 22 | Remote admin on Linux/network devices; watch for brute force and unusual sources |
| RDP | 3389 | Windows remote desktop; internet-exposed RDP is a top ransomware entry point |
| SMB | 445 | Windows file sharing; lateral movement and ransomware propagation |
| LDAP / LDAPS | 389 / 636 | Directory queries; mass queries can indicate AD reconnaissance |
| Kerberos | 88 | Windows authentication; anomalies indicate ticket attacks |

:::warning Ports are conventions, not guarantees
Nothing prevents an attacker from running SSH on port 443 or HTTP on port 53. Treat "port 443 = HTTPS" as a hypothesis to verify with protocol inspection, not a fact.
:::

## Network security controls and the logs they produce

- **Firewalls** log allowed/denied connections with 5-tuple data (source IP, source port, destination IP, destination port, protocol).
- **Proxies** log URLs, user identity, categories and bytes transferred.
- **VPN concentrators** log remote user sessions — critical for impossible-travel analysis.
- **Segmentation** (VLANs, security groups) limits paths; denied internal traffic between segments is often a high-value signal.
- **NetFlow/IPFIX** records summarise conversations (who talked to whom, how much, how long) without payloads.

## Packet analysis with Wireshark and tcpdump

Packet captures (PCAP) contain full packets — the most detailed network evidence. Capture only where you are authorised: packets may contain personal data and credentials.

Useful **tcpdump** capture examples (run on a host you administer):

```bash
# Capture DNS traffic on interface eth0 and write to a file
sudo tcpdump -i eth0 -w dns.pcap 'port 53'

# Show SYN packets only (connection attempts), no name resolution
sudo tcpdump -nn -i eth0 'tcp[tcpflags] & (tcp-syn) != 0 and tcp[tcpflags] & (tcp-ack) = 0'

# Read a capture file and show traffic to one host
tcpdump -nn -r capture.pcap 'host 203.0.113.50'
```

Useful **Wireshark display filters**:

```text
ip.addr == 10.0.5.20                         # all traffic to/from a host
tcp.flags.syn == 1 && tcp.flags.ack == 0     # connection attempts
tcp.flags.reset == 1                         # resets (closed ports, aborted sessions)
dns.qry.name contains "update"               # DNS queries containing a string
http.request.method == "POST"                # uploads and form submissions
tls.handshake.extensions_server_name         # show SNI (server names in TLS)
frame.len > 1400 && ip.dst == 203.0.113.50   # large packets to a destination
```

Workflow for a PCAP investigation:

1. **Statistics → Conversations**: which hosts exchanged the most data?
2. **Statistics → Protocol Hierarchy**: any unexpected protocols?
3. Filter DNS: what was resolved just before suspicious connections?
4. **Follow TCP/HTTP stream** on interesting sessions to see the dialogue.
5. Export objects (File → Export Objects → HTTP) to retrieve transferred files — then handle them as potential malware, in an isolated environment.

## Recognising suspicious traffic

### Pattern 1 — Port scanning

One source contacting **many ports** on one host (vertical scan) or **one port on many hosts** (horizontal scan), mostly with failed or reset connections.

```text
src=10.0.9.77 dst=10.0.2.15 dport=21  action=deny
src=10.0.9.77 dst=10.0.2.15 dport=22  action=allow  bytes=60
src=10.0.9.77 dst=10.0.2.15 dport=23  action=deny
src=10.0.9.77 dst=10.0.2.15 dport=25  action=deny
... 950 more ports in 40 seconds ...
```

An internal workstation scanning a server is far more concerning than internet noise hitting your perimeter.

### Pattern 2 — Command-and-control beaconing

Malware often "calls home" at regular intervals. Indicators:

- Connections to the **same destination at near-constant intervals** (e.g. every 60 s ± a few seconds of jitter)
- **Small, similar request sizes**, often with small responses
- Destination is **newly registered**, rarely visited by others in the organisation, or uses an unusual TLS fingerprint
- Activity continues **outside working hours**

```text
08:00:03  10.0.5.20 → 198.51.100.77:443  bytes_out=412 bytes_in=1203
08:01:04  10.0.5.20 → 198.51.100.77:443  bytes_out=409 bytes_in=1198
08:02:02  10.0.5.20 → 198.51.100.77:443  bytes_out=415 bytes_in=1201
08:03:05  10.0.5.20 → 198.51.100.77:443  bytes_out=411 bytes_in=1203
```

Legitimate software (update checkers, telemetry agents) also beacons — which is why **rarity** (how many internal hosts contact this destination?) is such a powerful enrichment.

### Pattern 3 — Data exfiltration

- **Large outbound volume** to a single external destination, especially compared with that host's baseline
- **Asymmetry**: bytes out ≫ bytes in (normal browsing is the opposite)
- Transfers to **personal cloud storage**, file-sharing sites or uncategorised domains
- **DNS tunnelling**: many long, random-looking subdomains under one domain (`a9f3k2...x7.exfil.example`)
- Activity at **unusual hours**

### Pattern 4 — Lateral movement

Workstation-to-workstation **SMB (445), RDP (3389), WinRM (5985/5986)** or **SSH** connections are rare in well-run networks. A single workstation contacting many others on these ports is a strong signal.

:::scenario Investigation walkthrough
At 02:14 a proxy alert fires: workstation `WS-FIN-07` (10.0.5.20) uploaded 3.8 GB to `files-transfer-cdn.example` (first seen in the organisation that day).

1. **Baseline:** in the past 30 days, WS-FIN-07's daily upload averaged 45 MB. → strong anomaly.
2. **Rarity:** no other host has contacted this domain. → suspicious.
3. **DNS:** the domain was resolved at 02:09 for the first time; registration date is 3 days old (from threat intel enrichment).
4. **Earlier flows:** from 01:30, beaconing every ~60 s to 198.51.100.77:443.
5. **User context:** the assigned user was not logged in (no interactive logon events since 18:05).

Conclusion: probable C2-driven exfiltration from a compromised host. Escalate to Tier 2, isolate the host via EDR, preserve proxy and firewall logs, and start incident response.
:::

## Common mistakes

- **Trusting port numbers** as proof of protocol.
- **Ignoring DNS** — it is the cheapest, richest network telemetry available.
- **Forgetting time zones** — firewall in UTC, proxy in local time: correlate only after normalising.
- **Missing NAT** — the IP in a perimeter log may be the firewall's public address, not the internal host. Use NAT translation logs or the proxy's user field to attribute.
- **Analysing a PCAP on your own workstation** that contains live malware objects without isolation.

## Security implications

- Ensure firewall *allow* logs are collected, not just *deny* logs; most attacks succeed through allowed paths.
- Keep DHCP and VPN logs long enough to attribute historical IPs to devices and users.
- Restrict outbound traffic: workstations rarely need direct outbound SMTP, SMB or arbitrary ports.

## Knowledge check

:::check Questions
1. A client sends SYN; the server replies RST/ACK. What does this indicate?
2. Which Wireshark filter shows only TCP connection attempts?
3. Name two characteristics of C2 beaconing.
4. Why are internal workstation-to-workstation SMB connections suspicious?
:::

:::solution Answers
1. The port is **closed** on the server (the host is reachable but nothing is listening).
2. `tcp.flags.syn == 1 && tcp.flags.ack == 0`
3. Regular intervals with low jitter; small, consistent payload sizes; rare or newly registered destinations; activity outside working hours.
4. Workstations rarely need to share files with each other; such traffic is typical of **lateral movement** and ransomware propagation.
:::

## Exercise

Download the sample flow log attached to this lesson (`soc-network-flows.csv`) and answer:

1. Which internal host shows a vertical port-scan pattern, and against which target?
2. Which internal host beacons to an external IP, and at roughly what interval?
3. Which host transferred the largest outbound volume, and to which destination?

:::solution Model answer
1. **10.0.9.77** scans **10.0.2.15** across sequential ports within seconds, with mostly `deny` actions.
2. **10.0.5.20** connects to **198.51.100.77:443** about **every 60 seconds** with near-identical byte counts.
3. **10.0.5.20** also sends the largest volume — about **3.8 GB** to **203.0.113.200:443** — consistent with exfiltration following C2 activity.
:::

## Summary and next steps

- Map protocols to layers and know the ports you will see daily — but verify protocols rather than trusting ports.
- The TCP handshake and flags explain what scans and refused connections look like.
- Beaconing, exfiltration, scanning and lateral movement each have distinctive, detectable traffic shapes.

Next, the **Operating Systems and Logs** module moves from the network to the endpoint, starting with Windows Event Logs.

===assignment===
Using Wireshark on traffic you are authorised to capture (for example, your own lab VM browsing a website), capture a session and document: the DNS query and response, the three TCP handshake packets with their flags, the TLS Server Name Indication, and the connection teardown. Include screenshots and explain each step in your own words.

===criteria===
- Complete the reading, knowledge check and the flow-log exercise.
- Mark the lesson complete. The networking module quiz and the course final exam assess this material.

===references===
- RFC 9293, *Transmission Control Protocol (TCP)* — https://www.rfc-editor.org/rfc/rfc9293
- RFC 1918, *Address Allocation for Private Internets* — https://www.rfc-editor.org/rfc/rfc1918
- Wireshark User's Guide, Display Filters — https://www.wireshark.org/docs/wsug_html_chunked/ChWorkBuildDisplayFilterSection.html
- tcpdump manual — https://www.tcpdump.org/manpages/tcpdump.1.html
- MITRE ATT&CK T1071, Application Layer Protocol — https://attack.mitre.org/techniques/T1071/
- MITRE ATT&CK T1048, Exfiltration Over Alternative Protocol — https://attack.mitre.org/techniques/T1048/
