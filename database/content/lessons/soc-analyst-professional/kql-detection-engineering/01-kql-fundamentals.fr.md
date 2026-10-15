---
title: Fondamentaux de KQL pour les analystes sécurité
chapter: Bases des requêtes
summary: Découvrez la structure du Kusto Query Language et ses opérateurs essentiels — where, project, extend, summarize, bin, join et let — à travers des exemples orientés sécurité sur les tables de Microsoft Sentinel.
duration: 75
level: intermediate
objectives:
- Expliquer le modèle tabulaire et à base de pipes de KQL et lire une requête de haut en bas
- Filtrer, projeter et calculer des colonnes avec where, project et extend
- Agréger avec summarize, count, dcount, make_set et bin
- Combiner des données avec join et union, et structurer les requêtes avec let
- Écrire des requêtes efficaces en filtrant tôt sur le temps et les colonnes indexées
prerequisites:
- Les journaux d'événements Windows pour l'investigation
---

## Introduction

Le Kusto Query Language (KQL) est le langage de requête de Microsoft Sentinel, de la chasse avancée de Microsoft Defender XDR, d'Azure Monitor Log Analytics et d'Azure Data Explorer. Pour de nombreux analystes SOC, c'est l'outil principal du métier : chaque investigation, chaque chasse et chaque règle analytique est une requête KQL.

KQL est **en lecture seule** et **à base de pipes** : les données partent d'une table et traversent une suite d'opérateurs, chacun transformant le résultat du précédent. Une fois ce modèle et une douzaine d'opérateurs maîtrisés, vous pouvez exprimer la plupart des questions de sécurité.

:::note Environnements d'entraînement
Microsoft propose gratuitement un espace de travail Log Analytics de démonstration avec des données d'exemple, et Azure Data Explorer offre un cluster gratuit avec la base `help`. Vous pouvez pratiquer toutes les requêtes de cette leçon sans accès à un tenant de production. N'exécutez jamais de requêtes exploratoires sur des données de production auxquelles vous n'êtes pas autorisé à accéder.
:::

## Structure d'une requête

```kusto
SecurityEvent                                   // 1. partir d'une table
| where TimeGenerated > ago(1d)                 // 2. filtrer les lignes (le temps d'abord !)
| where EventID == 4625                         // 3. filtrer davantage
| project TimeGenerated, Account, IpAddress, Computer   // 4. choisir les colonnes
| sort by TimeGenerated desc                    // 5. trier
| take 50                                       // 6. limiter
```

Règles à retenir :

- Chaque ligne après la table commence par `|` (le pipe).
- Les commentaires commencent par `//`.
- Les comparaisons de chaînes avec `==` sont **sensibles à la casse** ; `=~` ne l'est pas.
- Les instructions sont séparées par `;` lorsque vous utilisez `let`.

## Tables Sentinel courantes

| Table | Contenu |
|---|---|
| `SecurityEvent` | Événements Windows Security transmis par l'agent Azure Monitor |
| `SigninLogs` | Connexions interactives Microsoft Entra ID |
| `AADNonInteractiveUserSignInLogs` | Renouvellements de jetons et connexions non interactives |
| `AuditLogs` | Modifications de l'annuaire Entra ID (utilisateurs, groupes, applications) |
| `DeviceProcessEvents` | Créations de processus Defender for Endpoint |
| `DeviceNetworkEvents` | Connexions réseau Defender for Endpoint |
| `CommonSecurityLog` | Journaux CEF des pare-feu et proxys |
| `Syslog` | Syslog Linux et des équipements réseau |
| `OfficeActivity` | Activité des services Microsoft 365 |

## Filtrer avec `where`

```kusto
SecurityEvent
| where TimeGenerated between (datetime(2026-03-14 00:00) .. datetime(2026-03-14 06:00))
| where EventID in (4624, 4625)
| where Account !endswith "$"            // exclure les comptes machine
| where IpAddress !in ("-", "127.0.0.1", "::1")
```

Opérateurs de chaînes utiles :

| Opérateur | Signification | Remarques |
|---|---|---|
| `==`, `!=` | Égal / différent (sensible à la casse) | Le plus rapide |
| `=~`, `!~` | Égal (insensible à la casse) | |
| `has` | Contient un terme entier | Utilise l'index des termes — **à préférer à `contains`** |
| `contains` | Contient une sous-chaîne | Plus lent ; parcourt le texte |
| `startswith`, `endswith` | Préfixe / suffixe | |
| `in`, `!in`, `in~` | Appartenance à une liste | |
| `matches regex` | Expression régulière | Puissant mais le plus lent |

:::tip Règle empirique de performance
Filtrez d'abord sur le **temps**, puis sur des **colonnes exactes ou indexées** (`EventID`, `has`), et seulement ensuite sur des opérations coûteuses (`contains`, `matches regex`, `parse`). Une requête qui commence par une expression régulière large sur 30 jours est lente, coûteuse et peut expirer.
:::

## Sélectionner et calculer des colonnes

```kusto
SecurityEvent
| where TimeGenerated > ago(1h) and EventID == 4688
| project TimeGenerated, Computer, Account, NewProcessName, CommandLine, ParentProcessName
| extend ProcessFile = tostring(split(NewProcessName, "\\")[-1])
| extend IsEncoded = CommandLine has "-enc" or CommandLine has "-EncodedCommand"
```

- `project` ne conserve (et renomme éventuellement) que les colonnes listées : `project User = Account`.
- `project-away` supprime des colonnes.
- `extend` ajoute des colonnes calculées sans supprimer les autres.

## Agréger avec `summarize`

`summarize` regroupe des lignes et calcule des agrégats — le cœur de la logique de détection.

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

Fonctions d'agrégation courantes : `count()`, `dcount()` (comptage distinct approximatif), `countif()`, `sum()`, `avg()`, `min()`, `max()`, `make_set()`, `make_list()`, `arg_max()` (ligne ayant la valeur maximale), `percentile()`.

### Fenêtres temporelles avec `bin`

```kusto
SecurityEvent
| where TimeGenerated > ago(1d) and EventID == 4625
| summarize Failures = count() by IpAddress, bin(TimeGenerated, 5m)
| where Failures > 20
```

`bin(TimeGenerated, 5m)` arrondit chaque horodatage au début de sa fenêtre de 5 minutes, ce qui permet de compter les événements **par source et par fenêtre** — la base des détections fondées sur un débit.

### Visualiser

```kusto
SigninLogs
| where TimeGenerated > ago(7d)
| summarize SignIns = count() by bin(TimeGenerated, 1h), ResultType
| render timechart
```

## Combiner des données : `join` et `union`

`union` empile les lignes de plusieurs tables :

```kusto
union SigninLogs, AADNonInteractiveUserSignInLogs
| where TimeGenerated > ago(1d) and UserPrincipalName =~ "a.martin@contoso.example"
```

`join` associe les lignes de deux tables sur une clé :

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

Types de jointure à connaître : `inner` (correspondances des deux côtés), `leftouter` (toutes les lignes de gauche), `leftanti` (lignes de gauche **sans** correspondance — idéal pour « vu aujourd'hui mais jamais avant »), `innerunique` (valeur par défaut, qui déduplique le côté gauche — soyez-en conscient).

## Structurer avec `let`

`let` nomme une valeur, une liste ou une sous-requête, ce qui rend les requêtes lisibles et réutilisables :

```kusto
let lookback = 14d;
let threshold = 25;
let excludedIPs = dynamic(["10.0.0.5", "10.0.0.6"]);   // scanners de vulnérabilités
SecurityEvent
| where TimeGenerated > ago(lookback) and EventID == 4625
| where IpAddress !in (excludedIPs)
| summarize Failures = count() by IpAddress
| where Failures > threshold
```

## Manipuler des données dynamiques et imbriquées

De nombreux journaux contiennent du JSON. Le type `dynamic` de KQL et ses fonctions permettent de le traiter :

```kusto
SigninLogs
| where TimeGenerated > ago(1d)
| extend City    = tostring(LocationDetails.city),
         Country = tostring(LocationDetails.countryOrRegion),
         OS      = tostring(DeviceDetail.operatingSystem)
| project TimeGenerated, UserPrincipalName, IPAddress, City, Country, OS, ResultType
```

- La notation pointée ou entre crochets accède aux propriétés : `LocationDetails.city` ou `LocationDetails["city"]`.
- Convertissez toujours avec `tostring()`, `toint()`, `todatetime()` avant de comparer ou de regrouper.
- `mv-expand` transforme un tableau en plusieurs lignes ; `parse_json()` convertit une chaîne JSON en `dynamic`.
- `parse` extrait des champs d'un texte libre : `| parse CommandLine with * "-enc " Encoded " " *`.

## Une requête d'investigation complète

Question : *quels comptes se sont connectés avec succès depuis plus d'un pays au cours des dernières 24 heures ?*

```kusto
SigninLogs
| where TimeGenerated > ago(1d)
| where ResultType == "0"                                  // succès
| extend Country = tostring(LocationDetails.countryOrRegion)
| where isnotempty(Country)
| summarize Countries = make_set(Country), CountryCount = dcount(Country),
            IPs = make_set(IPAddress, 10), First = min(TimeGenerated), Last = max(TimeGenerated)
          by UserPrincipalName
| where CountryCount > 1
| sort by CountryCount desc
```

C'est le point de départ d'une investigation de **voyage impossible** ; vous vérifieriez ensuite la chronologie, les IP de sortie VPN et les détails des appareils avant de conclure quoi que ce soit.

## Erreurs fréquentes

- **Oublier le filtre temporel** — toute la période de rétention est parcourue.
- **Utiliser `contains` partout** — préférez `has` pour des termes entiers.
- **Les erreurs de casse** — `Account == "ADMIN"` ne correspondra pas à `admin` ; utilisez `=~`.
- **Comparer des valeurs dynamiques sans conversion** — `LocationDetails.city == "Paris"` peut échouer silencieusement ; utilisez `tostring()`.
- **Supposer que `join` est un inner par défaut** — la valeur par défaut est `innerunique` ; précisez explicitement `kind=`.
- **Ignorer le délai d'ingestion** — une règle planifiée qui regarde exactement 5 minutes en arrière peut manquer des événements tardifs ; faites légèrement chevaucher les fenêtres.

## Implications de sécurité

- Les requêtes peuvent exposer des données personnelles ; respectez les politiques d'accès aux données de votre organisation et évitez les exports inutiles.
- Les requêtes enregistrées et les règles analytiques sont du code : relisez-les, versionnez-les et testez-les.

## Vérification des connaissances

:::check Questions
1. Quelle est la différence entre `has` et `contains` ?
2. Que fait `bin(TimeGenerated, 10m)` dans un `summarize` ?
3. Quel type de jointure renvoie les lignes de gauche sans correspondance à droite ?
4. Pourquoi le filtre temporel doit-il venir en premier ?
:::

:::solution Réponses
1. `has` correspond à des termes entiers indexés et est rapide ; `contains` correspond à n'importe quelle sous-chaîne et doit parcourir le texte.
2. Il regroupe les horodatages en **fenêtres de 10 minutes** pour que les agrégats soient calculés par fenêtre.
3. `leftanti`.
4. Il réduit la quantité de données parcourue par chaque opérateur suivant, ce qui améliore la rapidité et le coût.
:::

## Exercice

Écrivez une requête KQL pour chaque question (en supposant les tables Sentinel standard) :

1. Les 10 ordinateurs ayant le plus de créations de processus (événement 4688) au cours des dernières 24 heures.
2. Tous les processus PowerShell (depuis `SecurityEvent` 4688) dont la ligne de commande contient une commande encodée au cours des 7 derniers jours, avec l'heure, l'ordinateur, le compte et la ligne de commande.
3. Les comptes ajoutés à un groupe de sécurité (événements 4728, 4732, 4756) au cours des dernières 24 heures, avec l'auteur de la modification.

:::solution Corrigés
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

## Synthèse et prochaines étapes

- KQL est un pipeline : table → filtrage → mise en forme → agrégation → sortie.
- `where`, `project`, `extend`, `summarize`, `bin`, `join`, `union` et `let` couvrent l'essentiel des besoins.
- Filtrez tôt sur le temps et les colonnes indexées ; convertissez explicitement les valeurs dynamiques.

Vous allez ensuite appliquer ces opérateurs à une détection complète : **les attaques par force brute et pulvérisation de mots de passe**.

===assignment===
Dans l'espace de travail de démonstration Microsoft ou dans le cluster `help` d'Azure Data Explorer, écrivez trois requêtes de votre choix : une utilisant `summarize ... by bin()`, une utilisant `join kind=leftanti` pour trouver quelque chose de « nouveau », et une qui analyse une colonne dynamique. Enregistrez chacune avec un commentaire expliquant la question à laquelle elle répond et ce que vous avez trouvé.

===criteria===
- Terminer la lecture, la vérification des connaissances et l'exercice de requêtes.
- Marquer la leçon comme terminée ; les tâches de requêtes KQL du lab de force brute nécessitent ces opérateurs.

===references===
- Microsoft Learn, *Vue d'ensemble du Kusto Query Language* — https://learn.microsoft.com/kusto/query/
- Microsoft Learn, *Opérateur summarize* — https://learn.microsoft.com/kusto/query/summarize-operator
- Microsoft Learn, *Opérateur join* — https://learn.microsoft.com/kusto/query/join-operator
- Microsoft Learn, *Bonnes pratiques pour les requêtes KQL* — https://learn.microsoft.com/kusto/query/best-practices
- Référence des données Microsoft Sentinel (SecurityEvent, SigninLogs) — https://learn.microsoft.com/azure/azure-monitor/reference/tables/securityevent
