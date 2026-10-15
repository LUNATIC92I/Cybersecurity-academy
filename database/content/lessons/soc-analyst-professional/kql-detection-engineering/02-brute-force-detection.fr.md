---
title: Détecter la force brute et la pulvérisation de mots de passe
chapter: Détections d'identité
summary: Construisez, ajustez et validez des détections KQL contre la force brute, la pulvérisation de mots de passe et le schéma critique « échecs suivis d'un succès » dans les journaux Windows et Microsoft Entra ID.
duration: 70
level: intermediate
objectives:
- Distinguer force brute, pulvérisation de mots de passe et bourrage d'identifiants par leur signature dans les journaux
- Écrire des détections KQL pour chaque schéma à l'aide de seuils, de fenêtres temporelles et de comptages distincts
- Détecter une ouverture de session réussie qui suit une rafale d'échecs depuis la même source
- Ajuster les détections pour réduire les faux positifs et les aligner sur MITRE ATT&CK T1110
- Documenter une règle analytique avec gravité, correspondance d'entités et playbook de tri
prerequisites:
- Fondamentaux de KQL pour les analystes sécurité
- Les journaux d'événements Windows pour l'investigation
---

## Introduction

Les attaques sur les identifiants figurent parmi les techniques d'accès initial les plus fréquentes, car elles sont peu coûteuses et réussissent souvent contre les comptes sans MFA. Elles produisent aussi une télémétrie abondante et reconnaissable. Cette leçon transforme cette télémétrie en détections fiables et — tout aussi important — montre comment éviter que ces détections noient le SOC sous le bruit.

## Trois schémas d'attaque

Tous relèvent de MITRE ATT&CK **T1110 — Brute Force** :

| Sous-technique | Schéma | Signature dans les journaux |
|---|---|---|
| T1110.001 Password Guessing | De nombreux mots de passe essayés contre **un** compte | Beaucoup d'échecs pour un compte, souvent depuis une seule source |
| T1110.003 Password Spraying | **Un ou quelques** mots de passe courants essayés contre **de nombreux** comptes | Peu d'échecs par compte, nombreux comptes distincts par source |
| T1110.004 Credential Stuffing | Des **couples** identifiant/mot de passe divulgués sont essayés | Nombreux comptes, échecs mêlés à des succès occasionnels, souvent depuis de nombreuses IP |

La pulvérisation est conçue pour rester sous les seuils de verrouillage par compte : c'est pourquoi les détections par compte la manquent et les comptages de comptes distincts par source la repèrent.

## Les données

**Windows (SecurityEvent)** — événement 4625 (échec) et 4624 (succès). Champs utiles : `Account`, `TargetUserName`, `IpAddress`, `LogonType`, `SubStatus`, `Computer`.

**Microsoft Entra ID (SigninLogs)** — `ResultType` contient le code d'erreur (« 0 » signifie succès). Codes fréquents lors d'attaques sur les identifiants :

| ResultType | Signification |
|---|---|
| 50126 | Nom d'utilisateur ou mot de passe invalide |
| 50053 | Compte verrouillé (verrouillage intelligent) ou connexion depuis une IP malveillante bloquée |
| 50055 | Mot de passe expiré |
| 50057 | Compte désactivé |
| 50074 / 50076 | Authentification forte (MFA) requise |
| 500121 | MFA échouée ou refusée |

:::note Pourquoi les codes MFA comptent
Un mot de passe correct suivi de `50074` ou `500121` signifie que le **mot de passe est compromis**, même si la connexion n'a pas abouti. Traitez cela comme une compromission d'identifiants confirmée que la MFA a heureusement stoppée.
:::

## Détection 1 — Force brute contre un compte unique

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

Choix à justifier :

- **Fenêtre et seuil** : 20 échecs en 10 minutes dépasse largement l'erreur humaine habituelle (un utilisateur échoue rarement plus de 3 à 5 fois) tout en restant assez bas pour repérer des attaques lentes. Calibrez avec votre propre référence.
- **Mettre le compte en minuscules** évite de séparer `ADMIN` et `admin` en deux groupes.
- **Exclure les comptes machine** (`$`) supprime le bruit Kerberos/NTLM.

## Détection 2 — Pulvérisation depuis une source

```kusto
let window = 30m;
let minAccounts = 10;
SecurityEvent
| where TimeGenerated > ago(1d)
| where EventID == 4625 and SubStatus =~ "0xc000006a"      // utilisateur valide, mauvais mot de passe
| summarize DistinctAccounts = dcount(TargetUserName),
            Attempts = count(),
            Accounts = make_set(TargetUserName, 50)
          by IpAddress, bin(TimeGenerated, window)
| where DistinctAccounts >= minAccounts
| extend AttemptsPerAccount = round(todouble(Attempts) / DistinctAccounts, 1)
| where AttemptsPerAccount <= 3                               // la forme de la pulvérisation
```

Le dernier filtre distingue la pulvérisation d'un service mal configuré qui martèle un compte : **beaucoup de comptes, peu de tentatives chacun**.

L'équivalent pour Entra ID :

```kusto
SigninLogs
| where TimeGenerated > ago(1d)
| where ResultType == "50126"
| summarize DistinctUsers = dcount(UserPrincipalName), Attempts = count(),
            Users = make_set(UserPrincipalName, 50), Apps = make_set(AppDisplayName, 10)
          by IPAddress, bin(TimeGenerated, 1h)
| where DistinctUsers >= 10
```

## Détection 3 — Échecs suivis d'un succès (la plus précieuse)

Une rafale d'échecs qui se termine par un succès depuis la même source est le moment où une attaque devient un incident.

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

La condition temporelle garantit que le succès survient **après** les échecs et à proximité, ce qui élimine les correspondances fortuites, comme un utilisateur qui a échoué le matin puis s'est connecté normalement l'après-midi.

```text
Chronologie détectée par cette règle

 01:52 ─ 4625 ─ 4625 ─ 4625 ─ ... (38 échecs) ... ─ 4625 01:56:20
                                                     │
 01:56:41 ───────────────────────────────────── 4624  ← ALERTE (succès 21 s après le dernier échec)
```

## Ajustement : du bruit à la confiance

Attendez-vous à des faux positifs provenant :

- Des **scanners de vulnérabilités et outils de supervision** aux identifiants périmés → excluez par IP connue *et* par compte, jamais par IP seule.
- Des **comptes de service au mot de passe expiré** qui réessaient toutes les quelques secondes → ils montrent le *même* compte et la *même* source pendant des heures ; investiguez une fois, corrigez l'identifiant, puis excluez temporairement avec une date d'expiration.
- Des **adresses de sortie NAT et VPN** partagées par de nombreux utilisateurs → la logique de pulvérisation peut se déclencher sur la passerelle d'un bureau très actif. Ajoutez ces IP à une watchlist et exigez un seuil plus élevé pour elles.
- Des **clients de messagerie mobiles** qui réessaient un ancien mot de passe après un changement.

Techniques d'ajustement :

```kusto
let scanners = _GetWatchlist('KnownScanners') | project IP = tostring(SearchKey);
SecurityEvent
| where TimeGenerated > ago(1h) and EventID == 4625
| where IpAddress !in (scanners)
// ...
```

- Utilisez des **watchlists** pour les exclusions afin qu'elles soient revues et auditables, et non codées en dur dans les règles.
- Ajoutez des **colonnes de contexte** (service du compte, criticité de l'actif) plutôt que de supprimer.
- Relevez la gravité lorsque la cible est privilégiée (membre de groupes d'administration) ou que la source est externe.

## Valider une détection

Une détection n'est pas terminée tant que vous n'avez pas montré qu'elle se déclenche sur l'attaque et reste silencieuse sinon.

1. **Test positif** — rejouez ou générez l'attaque en lab (par ex. des mots de passe erronés répétés contre un compte de test) et confirmez l'alerte.
2. **Test négatif** — exécutez la requête sur deux semaines de données historiques et examinez chaque résultat : combien auraient été des faux positifs ?
3. **Test aux limites** — que se passe-t-il au seuil − 1 ? Avec un rythme plus lent (une tentative par minute) ?
4. **Documentation** — description, correspondance ATT&CK, sources de données, seuils et justification, faux positifs connus, étapes de tri.

## De la requête à la règle analytique

Dans Microsoft Sentinel, une règle analytique planifiée associe la requête à :

- Une **planification** : exécution toutes les 10 minutes, regard d'1 heure en arrière (le chevauchement compense le délai d'ingestion).
- Un **seuil d'alerte** : générer une alerte si résultats > 0.
- Une **correspondance d'entités** : associer `TargetAccount` à *Account* et `IpAddress` à *IP* pour corréler les incidents entre règles.
- Une **gravité** : Moyenne pour une pulvérisation sans succès ; **Élevée** pour des échecs suivis d'un succès.
- Un **regroupement / suppression d'événements** : regrouper tous les résultats en une seule alerte par exécution pour éviter les avalanches.
- Des **tactiques** : Credential Access ; technique T1110.

## Playbook de tri (niveau 1)

1. Confirmer le schéma : nombre d'échecs, comptes, durée, IP source.
2. Enrichir la source : interne ou externe ? Scanner connu ? Géolocalisation et réputation ? Autres alertes associées ?
3. Rechercher un succès : un 4624 / ResultType 0 depuis la même source pour les comptes visés ?
4. **Sans succès** : bloquer la source si elle est externe et malveillante, prévenir l'équipe identité, clôturer avec des notes.
5. **Avec succès** : traiter comme une compromission — escalader au niveau 2, désactiver ou réinitialiser le compte, révoquer les sessions, examiner l'activité après le succès (règles de boîte aux lettres, nouvelles méthodes MFA, exécution de processus, mouvement latéral).

## Erreurs fréquentes

- **Uniquement des seuils par compte** — la pulvérisation passe en dessous.
- **Exclure par IP seule** — un attaquant installé sur un hôte de scan compromis devient invisible.
- **Pas d'ordre temporel** dans la jointure avec les succès — produit des alertes trompeuses.
- **Ignorer les connexions non interactives** dans Entra ID — des attaquants utilisant des protocoles hérités peuvent n'apparaître que là.

## Implications de sécurité

- La meilleure parade est une **MFA résistante à l'hameçonnage** et le blocage de l'authentification héritée ; les détections sont le filet de sécurité.
- Le verrouillage intelligent et l'accès conditionnel réduisent l'efficacité des attaques mais ne remplacent pas la surveillance.

## Vérification des connaissances

:::check Questions
1. Quelle caractéristique des données distingue la pulvérisation de mots de passe de la force brute ?
2. Pourquoi la détection 3 exige-t-elle `SuccessTime > LastFailure` ?
3. Le mot de passe d'un utilisateur est correct mais la connexion renvoie ResultType 500121. Que devez-vous conclure ?
4. Pourquoi utiliser une watchlist plutôt que des exclusions codées en dur ?
:::

:::solution Réponses
1. La pulvérisation montre **de nombreux comptes distincts avec peu de tentatives chacun** depuis une source ; la force brute montre **de nombreuses tentatives contre un seul compte**.
2. Pour garantir que le succès **suit** les échecs, en éliminant les succès fortuits survenus auparavant.
3. Le **mot de passe est compromis** ; la MFA a bloqué la connexion. Réinitialisez le mot de passe et investiguez.
4. Les watchlists sont gérées de façon centralisée, relues et auditables, et peuvent être mises à jour sans modifier chaque règle.
:::

## Exercice

Téléchargez `soc-auth-events.csv` (joint à cette leçon) et, avec l'outil de votre choix (tableur, `grep`, ou la visionneuse intégrée du lab associé) :

1. Identifiez l'IP source responsable de la rafale de force brute.
2. Comptez ses tentatives échouées.
3. Identifiez le compte finalement compromis et l'heure de l'ouverture de session réussie.
4. Écrivez la requête KQL que vous déploieriez en production pour détecter ce schéma.

:::solution Corrigé
1. **10.0.9.77**.
2. **38** tentatives échouées (événement 4625, SubStatus `0xC000006A`).
3. **svc_backup**, ouverture de session réseau réussie (type 3) le **2026-03-14 à 01:56:41 UTC**.
4. La détection 3 de cette leçon (échecs suivis d'un succès), avec `failThreshold = 10` et une fenêtre de succès de 30 minutes, entités associées à Account et IP, gravité Élevée.
:::

## Synthèse et prochaines étapes

- Force brute, pulvérisation et bourrage laissent des formes différentes dans les données ; détectez chacune délibérément.
- « Échecs puis succès » est la détection d'identité la plus précieuse.
- Une détection comprend des seuils justifiés, un ajustement par watchlists, des tests de validation et un playbook de tri.

Réalisez le **Lab SOC : investigation de force brute** pour appliquer cette leçon à un jeu de données complet, noté côté serveur.

===assignment===
Rédigez une **spécification de détection** d'une page pour la détection 3 selon ce modèle : nom ; description ; correspondance ATT&CK ; sources de données ; requête ; planification et période d'analyse ; seuils et justification ; correspondance d'entités ; logique de gravité ; faux positifs connus et ajustement ; étapes de tri ; preuves de validation (décrivez comment vous l'avez testée).

===criteria===
- Terminer la lecture, la vérification des connaissances et l'exercice sur le jeu de données.
- Marquer la leçon comme terminée et réussir le lab SOC de force brute.

===references===
- MITRE ATT&CK T1110, Brute Force (et sous-techniques) — https://attack.mitre.org/techniques/T1110/
- Microsoft Learn, *Codes d'erreur d'authentification et d'autorisation Microsoft Entra* — https://learn.microsoft.com/entra/identity-platform/reference-error-codes
- Microsoft Learn, *Créer des règles analytiques planifiées dans Microsoft Sentinel* — https://learn.microsoft.com/azure/sentinel/create-analytics-rules
- Microsoft Learn, *Watchlists dans Microsoft Sentinel* — https://learn.microsoft.com/azure/sentinel/watchlists
- CISA, *Fiche sur la MFA résistante à l'hameçonnage* — https://www.cisa.gov/resources-tools/resources/implementing-phishing-resistant-mfa
