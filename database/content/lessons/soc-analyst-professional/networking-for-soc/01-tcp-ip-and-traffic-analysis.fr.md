---
title: TCP/IP, la poignée de main et le trafic suspect
chapter: Fondamentaux réseau
summary: Comprenez le modèle TCP/IP, suivez une connexion TCP paquet par paquet et reconnaissez les schémas de trafic du balayage, du beaconing et de l'exfiltration dans les journaux de flux et les captures de paquets.
duration: 60
level: beginner
objectives:
- Associer les protocoles courants aux couches des modèles TCP/IP et OSI
- Expliquer la poignée de main TCP en trois temps, la fermeture de connexion et la signification des drapeaux TCP
- Utiliser les filtres d'affichage Wireshark et tcpdump pour isoler le trafic pertinent
- Reconnaître le balayage de ports, le beaconing de commande et contrôle et l'exfiltration de données
prerequisites:
- Menaces, vulnérabilités, risques et mesures
- Savoir lire une adresse IPv4 et un numéro de port
---

## Introduction

Presque toutes les intrusions traversent un réseau. Même lorsque les journaux des postes sont effacés, les preuves réseau — journaux de pare-feu, de proxy, DNS, NetFlow, captures de paquets — survivent souvent sur d'autres systèmes. Pour les exploiter, un analyste doit savoir les lire couramment. Cette leçon construit cette aisance, de la pile de protocoles jusqu'à des schémas de détection concrets.

## Les modèles : OSI et TCP/IP

Le **modèle OSI** compte sept couches et sert surtout à l'enseignement et au dépannage. Le **modèle TCP/IP** compte quatre couches et reflète la façon dont Internet est réellement construit.

| Couche TCP/IP | Couches OSI | Exemples | Ce que voit le SOC |
|---|---|---|---|
| Application | 7 Application, 6 Présentation, 5 Session | HTTP, DNS, SMTP, SSH, RDP, TLS | URL, domaines, user agents, en-têtes de courriel |
| Transport | 4 Transport | TCP, UDP | Ports, état de connexion, drapeaux |
| Internet | 3 Réseau | IPv4, IPv6, ICMP | IP source/destination, TTL |
| Liaison | 2 Liaison, 1 Physique | Ethernet, Wi-Fi, ARP | Adresses MAC, VLAN |

Quand vous lisez « pare-feu de couche 7 », il s'agit d'un pare-feu qui inspecte le contenu applicatif (par exemple les catégories d'URL), et pas seulement les adresses IP et les ports.

## L'essentiel de l'adressage

### IPv4 et CIDR

Une adresse IPv4 compte 32 bits, écrits en quatre octets : `192.168.10.25`. La notation CIDR ajoute la longueur du préfixe réseau : `192.168.10.0/24` signifie que les 24 premiers bits identifient le réseau, laissant 8 bits (256 adresses, 254 hôtes utilisables) pour les hôtes.

Plages privées (RFC 1918) que vous verrez constamment dans les journaux internes :

```text
10.0.0.0/8        (10.0.0.0 – 10.255.255.255)
172.16.0.0/12     (172.16.0.0 – 172.31.255.255)
192.168.0.0/16    (192.168.0.0 – 192.168.255.255)
```

Reconnaissez aussi `127.0.0.0/8` (boucle locale), `169.254.0.0/16` (lien local, souvent signe d'un échec DHCP) et les plages de documentation comme `198.51.100.0/24` et `203.0.113.0/24`, utilisées dans les exemples de ce cours.

:::tip Calcul rapide de sous-réseau
Hôtes par sous-réseau = 2^(32 − préfixe) − 2. Un `/26` donne 2^6 − 2 = 62 hôtes utilisables. Savoir si deux IP partagent un sous-réseau indique si le trafic entre elles traverse un routeur ou un pare-feu — et donc si un journal de pare-feu devrait exister.
:::

### IPv6

Les adresses IPv6 comptent 128 bits, écrits en groupes hexadécimaux (`2001:db8:85a3::8a2e:370:7334`). Beaucoup de réseaux font tourner IPv6 en parallèle d'IPv4 sans le surveiller autant — un angle mort classique. Vérifiez que votre SIEM analyse les champs IPv6 et que les détections ne sont pas écrites uniquement pour des formats IPv4.

## Transport : TCP et UDP

**UDP** fonctionne sans connexion : les paquets sont envoyés sans établissement ni garantie de livraison. DNS (généralement), DHCP, NTP et de nombreux protocoles de streaming utilisent UDP.

**TCP** est orienté connexion et fiable. Il établit une session par la **poignée de main en trois temps** :

:::diagram Connexion TCP : la poignée de main à trois temps, les données et la fermeture
```text
Client 10.0.5.20:51544                      Serveur 203.0.113.10:443
        │ ── SYN (seq=x) ───────────────────────► │
        │ ◄────────────── SYN-ACK (seq=y, ack=x+1)│
        │ ── ACK (ack=y+1) ──────────────────────► │
        │      ... données applicatives (TLS) ...   │
        │ ── FIN ─────────────────────────────────► │
        │ ◄──────────────────────────────── FIN/ACK │
        │ ── ACK ─────────────────────────────────► │
```
:::

Drapeaux TCP à reconnaître :

| Drapeau | Signification | Interprétation de l'analyste |
|---|---|---|
| SYN | Ouvrir une connexion | Nombreux SYN vers de nombreux ports sans aboutissement → balayage de ports |
| ACK | Accuser réception | Présent sur presque tous les paquets après la poignée de main |
| FIN | Fermeture propre | Fermeture normale |
| RST | Abandon / refus | RST en réponse à un SYN → port fermé |
| PSH | Transmettre à l'application | Transfert interactif de données |
| URG | Urgent | Rare ; des combinaisons inhabituelles peuvent signaler des paquets forgés |

Un **SYN suivi d'un RST/ACK** de la cible signifie que le port est fermé. Un **SYN sans réponse** indique souvent qu'un pare-feu l'a silencieusement rejeté. **SYN, SYN-ACK, puis RST du client** est la signature d'un balayage « semi-ouvert » (`nmap -sS`).

## Les protocoles que le SOC voit chaque jour

| Protocole | Port(s) | Remarques pour l'analyste |
|---|---|---|
| DNS | 53/UDP, 53/TCP ; 853 (DoT) ; 443 (DoH) | Chaque connexion commence par une résolution — précieux pour la détection |
| DHCP | 67/68 UDP | Associe IP et MAC/nom d'hôte à un instant donné — essentiel pour l'attribution |
| HTTP / HTTPS | 80 / 443 | La plupart du trafic C2 se cache dans HTTPS ; inspectez le SNI, JA3/JA4, le certificat |
| SMTP | 25, 587, 465 | Acheminement du courriel ; du 25 sortant depuis un poste est suspect |
| SSH | 22 | Administration distante Linux/équipements réseau ; surveillez la force brute et les sources inhabituelles |
| RDP | 3389 | Bureau à distance Windows ; un RDP exposé sur Internet est une porte d'entrée majeure des rançongiciels |
| SMB | 445 | Partage de fichiers Windows ; mouvement latéral et propagation de rançongiciels |
| LDAP / LDAPS | 389 / 636 | Requêtes d'annuaire ; des requêtes massives peuvent signaler une reconnaissance AD |
| Kerberos | 88 | Authentification Windows ; les anomalies signalent des attaques sur les tickets |

:::warning Les ports sont des conventions, pas des garanties
Rien n'empêche un attaquant de faire tourner SSH sur le port 443 ou HTTP sur le port 53. Considérez « port 443 = HTTPS » comme une hypothèse à vérifier par l'inspection du protocole, pas comme un fait.
:::

## Les mesures de sécurité réseau et leurs journaux

- Les **pare-feu** journalisent les connexions autorisées/refusées avec le quintuplet (IP source, port source, IP destination, port destination, protocole).
- Les **proxys** journalisent les URL, l'identité de l'utilisateur, les catégories et les octets transférés.
- Les **concentrateurs VPN** journalisent les sessions des utilisateurs distants — indispensables pour l'analyse de voyage impossible.
- La **segmentation** (VLAN, groupes de sécurité) limite les chemins ; le trafic interne refusé entre segments est souvent un signal à forte valeur.
- Les enregistrements **NetFlow/IPFIX** résument les conversations (qui a parlé à qui, combien, combien de temps) sans contenu.

## Analyse de paquets avec Wireshark et tcpdump

Les captures de paquets (PCAP) contiennent les paquets complets — la preuve réseau la plus détaillée. Ne capturez que là où vous y êtes autorisé : les paquets peuvent contenir des données personnelles et des identifiants.

Exemples de capture **tcpdump** utiles (sur un hôte que vous administrez) :

```bash
# Capturer le trafic DNS sur l'interface eth0 et l'écrire dans un fichier
sudo tcpdump -i eth0 -w dns.pcap 'port 53'

# Afficher uniquement les paquets SYN (tentatives de connexion), sans résolution de noms
sudo tcpdump -nn -i eth0 'tcp[tcpflags] & (tcp-syn) != 0 and tcp[tcpflags] & (tcp-ack) = 0'

# Lire un fichier de capture et afficher le trafic vers un hôte
tcpdump -nn -r capture.pcap 'host 203.0.113.50'
```

**Filtres d'affichage Wireshark** utiles :

```text
ip.addr == 10.0.5.20                         # tout le trafic d'un hôte
tcp.flags.syn == 1 && tcp.flags.ack == 0     # tentatives de connexion
tcp.flags.reset == 1                         # réinitialisations (ports fermés, sessions abandonnées)
dns.qry.name contains "update"               # requêtes DNS contenant une chaîne
http.request.method == "POST"                # téléversements et soumissions de formulaires
tls.handshake.extensions_server_name         # afficher le SNI (noms de serveurs TLS)
frame.len > 1400 && ip.dst == 203.0.113.50   # gros paquets vers une destination
```

Démarche d'investigation d'un PCAP :

1. **Statistiques → Conversations** : quels hôtes ont échangé le plus de données ?
2. **Statistiques → Hiérarchie des protocoles** : des protocoles inattendus ?
3. Filtrez le DNS : qu'est-ce qui a été résolu juste avant les connexions suspectes ?
4. **Suivre le flux TCP/HTTP** sur les sessions intéressantes pour voir le dialogue.
5. Exportez les objets (Fichier → Exporter les objets → HTTP) pour récupérer les fichiers transférés — puis traitez-les comme de potentiels logiciels malveillants, dans un environnement isolé.

## Reconnaître le trafic suspect

### Schéma 1 — Balayage de ports

Une source contacte **de nombreux ports** sur un hôte (balayage vertical) ou **un port sur de nombreux hôtes** (balayage horizontal), avec surtout des connexions échouées ou réinitialisées.

```text
src=10.0.9.77 dst=10.0.2.15 dport=21  action=deny
src=10.0.9.77 dst=10.0.2.15 dport=22  action=allow  bytes=60
src=10.0.9.77 dst=10.0.2.15 dport=23  action=deny
src=10.0.9.77 dst=10.0.2.15 dport=25  action=deny
... 950 ports de plus en 40 secondes ...
```

Un poste interne qui balaie un serveur est bien plus préoccupant que le bruit d'Internet sur votre périmètre.

### Schéma 2 — Beaconing de commande et contrôle

Les logiciels malveillants « appellent la maison » à intervalles réguliers. Indicateurs :

- Connexions vers la **même destination à intervalles quasi constants** (par exemple toutes les 60 s ± quelques secondes de gigue)
- **Tailles de requêtes faibles et similaires**, souvent avec de petites réponses
- Destination **récemment enregistrée**, rarement visitée par le reste de l'organisation, ou empreinte TLS inhabituelle
- Activité qui se poursuit **en dehors des heures de travail**

```text
08:00:03  10.0.5.20 → 198.51.100.77:443  bytes_out=412 bytes_in=1203
08:01:04  10.0.5.20 → 198.51.100.77:443  bytes_out=409 bytes_in=1198
08:02:02  10.0.5.20 → 198.51.100.77:443  bytes_out=415 bytes_in=1201
08:03:05  10.0.5.20 → 198.51.100.77:443  bytes_out=411 bytes_in=1203
```

Des logiciels légitimes (vérificateurs de mises à jour, agents de télémétrie) émettent aussi des balises — c'est pourquoi la **rareté** (combien d'hôtes internes contactent cette destination ?) est un enrichissement si puissant.

### Schéma 3 — Exfiltration de données

- **Gros volume sortant** vers une seule destination externe, surtout par rapport à la référence de l'hôte
- **Asymétrie** : octets sortants ≫ octets entrants (la navigation normale est l'inverse)
- Transferts vers du **stockage cloud personnel**, des sites de partage ou des domaines non catégorisés
- **Tunnel DNS** : nombreux sous-domaines longs et d'apparence aléatoire sous un même domaine (`a9f3k2...x7.exfil.example`)
- Activité à des **heures inhabituelles**

### Schéma 4 — Mouvement latéral

Les connexions de poste à poste en **SMB (445), RDP (3389), WinRM (5985/5986)** ou **SSH** sont rares dans un réseau bien géré. Un poste qui contacte de nombreux autres sur ces ports est un signal fort.

:::scenario Investigation guidée
À 2 h 14, une alerte proxy se déclenche : le poste `WS-FIN-07` (10.0.5.20) a téléversé 3,8 Go vers `files-transfer-cdn.example` (vu pour la première fois dans l'organisation ce jour-là).

1. **Référence :** sur les 30 derniers jours, le volume quotidien téléversé par WS-FIN-07 était en moyenne de 45 Mo. → forte anomalie.
2. **Rareté :** aucun autre hôte n'a contacté ce domaine. → suspect.
3. **DNS :** le domaine a été résolu pour la première fois à 2 h 09 ; il a été enregistré il y a 3 jours (enrichissement par le renseignement).
4. **Flux antérieurs :** depuis 1 h 30, beaconing toutes les ~60 s vers 198.51.100.77:443.
5. **Contexte utilisateur :** l'utilisateur attitré n'était pas connecté (aucune ouverture de session interactive depuis 18 h 05).

Conclusion : exfiltration probable pilotée par un C2 depuis un hôte compromis. Escalade au niveau 2, isolement de l'hôte via l'EDR, préservation des journaux proxy et pare-feu, et lancement de la réponse à incident.
:::

## Erreurs fréquentes

- **Se fier aux numéros de port** comme preuve du protocole.
- **Ignorer le DNS** — c'est la télémétrie réseau la moins coûteuse et la plus riche.
- **Oublier les fuseaux horaires** — pare-feu en UTC, proxy en heure locale : ne corrélez qu'après normalisation.
- **Négliger la NAT** — l'IP dans un journal de périmètre peut être l'adresse publique du pare-feu, pas l'hôte interne. Utilisez les journaux de traduction NAT ou le champ utilisateur du proxy pour attribuer.
- **Analyser sur son propre poste un PCAP** contenant des objets malveillants actifs, sans isolement.

## Implications de sécurité

- Collectez les journaux *allow* des pare-feu, pas seulement les *deny* ; la plupart des attaques réussissent par des chemins autorisés.
- Conservez les journaux DHCP et VPN assez longtemps pour attribuer des IP historiques à des équipements et des utilisateurs.
- Restreignez le trafic sortant : les postes ont rarement besoin de SMTP, de SMB ou de ports arbitraires en sortie directe.

## Vérification des connaissances

:::check Questions
1. Un client envoie un SYN ; le serveur répond RST/ACK. Qu'est-ce que cela indique ?
2. Quel filtre Wireshark affiche uniquement les tentatives de connexion TCP ?
3. Citez deux caractéristiques du beaconing C2.
4. Pourquoi les connexions SMB internes de poste à poste sont-elles suspectes ?
:::

:::solution Réponses
1. Le port est **fermé** sur le serveur (l'hôte est joignable mais rien n'écoute).
2. `tcp.flags.syn == 1 && tcp.flags.ack == 0`
3. Intervalles réguliers avec faible gigue ; tailles de charge faibles et constantes ; destinations rares ou récemment enregistrées ; activité hors des heures de travail.
4. Les postes ont rarement besoin de partager des fichiers entre eux ; ce trafic est typique du **mouvement latéral** et de la propagation de rançongiciels.
:::

## Exercice

Téléchargez l'exemple de journal de flux joint à cette leçon (`soc-network-flows.csv`) et répondez :

1. Quel hôte interne présente un schéma de balayage de ports vertical, et contre quelle cible ?
2. Quel hôte interne émet des balises vers une IP externe, et à quel intervalle approximatif ?
3. Quel hôte a transféré le plus gros volume sortant, et vers quelle destination ?

:::solution Corrigé
1. **10.0.9.77** balaie **10.0.2.15** sur des ports successifs en quelques secondes, avec surtout des actions `deny`.
2. **10.0.5.20** se connecte à **198.51.100.77:443** environ **toutes les 60 secondes** avec des volumes d'octets quasi identiques.
3. **10.0.5.20** envoie aussi le plus gros volume — environ **3,8 Go** vers **203.0.113.200:443** — ce qui correspond à une exfiltration après une activité C2.
:::

## Synthèse et prochaines étapes

- Associez les protocoles aux couches et connaissez les ports que vous verrez chaque jour — mais vérifiez les protocoles plutôt que de vous fier aux ports.
- La poignée de main et les drapeaux TCP expliquent l'apparence des balayages et des connexions refusées.
- Le beaconing, l'exfiltration, le balayage et le mouvement latéral ont chacun une forme de trafic distinctive et détectable.

Ensuite, le module **Systèmes d'exploitation et journaux** passe du réseau au poste de travail, en commençant par les journaux d'événements Windows.

===assignment===
Avec Wireshark, sur un trafic que vous êtes autorisé à capturer (par exemple votre propre VM de lab qui consulte un site web), capturez une session et documentez : la requête et la réponse DNS, les trois paquets de la poignée de main TCP avec leurs drapeaux, le Server Name Indication TLS et la fermeture de la connexion. Ajoutez des captures d'écran et expliquez chaque étape avec vos propres mots.

===criteria===
- Terminer la lecture, la vérification des connaissances et l'exercice sur le journal de flux.
- Marquer la leçon comme terminée. Le quiz du module réseau et l'examen final du cours évaluent ce contenu.

===references===
- RFC 9293, *Transmission Control Protocol (TCP)* — https://www.rfc-editor.org/rfc/rfc9293
- RFC 1918, *Address Allocation for Private Internets* — https://www.rfc-editor.org/rfc/rfc1918
- Guide utilisateur Wireshark, filtres d'affichage — https://www.wireshark.org/docs/wsug_html_chunked/ChWorkBuildDisplayFilterSection.html
- Manuel de tcpdump — https://www.tcpdump.org/manpages/tcpdump.1.html
- MITRE ATT&CK T1071, Application Layer Protocol — https://attack.mitre.org/techniques/T1071/
- MITRE ATT&CK T1048, Exfiltration Over Alternative Protocol — https://attack.mitre.org/techniques/T1048/
