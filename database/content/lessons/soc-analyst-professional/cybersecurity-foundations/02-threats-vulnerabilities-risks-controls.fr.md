---
title: Menaces, vulnérabilités, risques et mesures
chapter: Principes de sécurité
summary: Distinguez les quatre notions qui sous-tendent toute discussion de sécurité, reconnaissez les vecteurs d'attaque courants et comprenez la place du SOC dans le dispositif de contrôle d'une organisation.
duration: 45
level: beginner
objectives:
- Définir précisément menace, vulnérabilité, risque et mesure et les employer correctement
- Décrire les vecteurs d'accès initial les plus courants et les traces que chacun laisse
- Classer les mesures par fonction (préventive, détective, corrective) et par type (technique, organisationnelle, physique)
- Expliquer les responsabilités d'un SOC et de ses niveaux au sein des opérations de sécurité de l'entreprise
prerequisites:
- La triade CIA et les principes fondamentaux de la sécurité
---

## Introduction

« Nous avons une vulnérabilité critique. » « C'est un risque élevé. » « L'attaquant a utilisé l'hameçonnage. » Ces phrases sont prononcées chaque jour — et souvent confondues. Un analyste SOC qui emploie ces termes avec précision rédige des tickets plus clairs, escalade de façon plus convaincante et gagne la confiance des équipes risques et audit. Cette leçon vous donne cette précision, puis montre la place du SOC dans l'organisation.

## Quatre termes, quatre réalités différentes

| Terme | Définition | Exemple |
|---|---|---|
| **Actif** | Tout ce qui a de la valeur pour l'organisation | Base de données clients, processus de paie, contrôleur de domaine |
| **Menace** | Cause potentielle d'un incident indésirable | Un groupe de rançongiciel ; un employé négligent ; une inondation |
| **Vulnérabilité** | Faiblesse qu'une menace peut exploiter | Appliance VPN non corrigée ; messagerie sans MFA ; politique de mots de passe faible |
| **Risque** | Combinaison de la vraisemblance qu'une menace exploite une vulnérabilité et de l'impact sur un actif | « Compromission probable de comptes de messagerie menant à une fraude au virement » |
| **Mesure** | Disposition qui modifie le risque | MFA, correctifs, sauvegardes, sensibilisation, séparation des tâches |

Une formule utile, répandue en introduction à la gestion des risques :

```text
Risque = Vraisemblance × Impact
```

La vraisemblance dépend de la **menace** (capacité, motivation) et de la **vulnérabilité** (exposition et facilité d'exploitation). L'impact dépend de l'**actif** (sa valeur et la propriété CIA atteinte). Les mesures réduisent la vraisemblance, l'impact, ou les deux.

:::tip Dites ce que vous voulez dire dans les tickets
Évitez « cette vulnérabilité est à risque élevé » si vous n'avez aucune information d'impact. Préférez : « La CVE-2024-3400 (CVSS 10.0) touche notre pare-feu exposé sur Internet ; une exploitation active est observée ; l'impact serait un accès complet au réseau. Correctif d'urgence recommandé. » Cela distingue la *gravité de la vulnérabilité* (CVSS) du *risque pour l'organisation*.
:::

### Les acteurs de la menace et leurs motivations

| Acteur | Motivation typique | Techniques courantes |
|---|---|---|
| Groupes cybercriminels | Gain financier | Hameçonnage, rançongiciel, compromission de messagerie professionnelle |
| Groupes soutenus par des États | Espionnage, perturbation | Compromission de la chaîne d'approvisionnement, zero-days, furtivité à long terme |
| Hacktivistes | Idéologie, visibilité | DDoS, défiguration, fuites de données |
| Initiés malveillants | Vengeance, profit | Vol de données via des accès légitimes |
| Initiés négligents | Aucune — des erreurs | Mauvaise configuration, courriel mal adressé, appareils perdus |

Comprendre la motivation aide à prévoir le comportement : un acteur motivé par l'argent ira vite vers les données et les sauvegardes ; un acteur d'espionnage privilégiera la furtivité et la persistance.

## Vecteurs d'attaque courants

Un **vecteur d'attaque** est le chemin qu'emprunte un attaquant pour obtenir un accès initial. Les vecteurs suivants représentent la majorité des intrusions réelles.

### 1. Hameçonnage et ingénierie sociale

Courriels, messages ou appels qui poussent un utilisateur à ouvrir un fichier malveillant, à saisir ses identifiants ou à approuver une demande MFA.
*Traces :* journaux de la passerelle de messagerie, journaux de clics sur les URL, nouvelles règles de boîte de réception, connexions depuis une infrastructure inconnue peu après un clic.

### 2. Abus d'identifiants

Mots de passe volés, devinés ou réutilisés contre le VPN, la messagerie web ou les portails cloud. Inclut la force brute et la pulvérisation de mots de passe (*password spraying*, MITRE ATT&CK **T1110**).
*Traces :* rafales d'échecs de connexion (Windows **4625**, échecs de connexion Entra ID avec des codes d'erreur tels que 50126), suivies d'un succès.

### 3. Exploitation d'applications exposées

VPN, pare-feu, applications web et serveurs de transfert de fichiers non corrigés (ATT&CK **T1190**).
*Traces :* requêtes inhabituelles dans les journaux web/proxy, nouveaux processus lancés par des comptes de serveur web, connexions sortantes depuis des hôtes en DMZ.

### 4. Chaîne d'approvisionnement et tiers

Mises à jour logicielles compromises, accès de prestataires d'infogérance, bibliothèques vulnérables.
*Traces :* logiciel de confiance au comportement inhabituel ; outils de télémaintenance se connectant à des heures inhabituelles.

### 5. Supports amovibles et accès physique

Périphériques USB, postes laissés sans surveillance, équipements illégitimes sur le réseau.
*Traces :* événements d'installation de périphériques, nouvelles adresses MAC, événement Windows **6416** (nouveau périphérique externe reconnu).

### 6. Mauvaise configuration

Espaces de stockage publics, interfaces d'administration exposées, identifiants par défaut.
*Traces :* constats de configuration cloud, accès depuis des IP inconnues à des ressources censées être privées.

```text
Chaîne d'intrusion typique (simplifiée)

Accès initial ──► Exécution ──► Persistance ──► Élévation de privilèges
      │                                                 │
 (hameçonnage,                                          ▼
  exploitation)        Mouvement latéral ◄── Accès aux identifiants
                              │
                              ▼
              Collecte ──► Exfiltration ──► Impact (chiffrement)
```

Chaque flèche est une occasion de détection. Les SOC matures cherchent à détecter les premières étapes pour que l'impact n'arrive jamais.

## Les mesures : fonction et type

Les mesures se classent de deux façons.

**Par fonction**

- **Préventive** — empêche un incident de se produire (MFA, règles de pare-feu, correctifs).
- **Détective** — identifie qu'un incident est en cours ou a eu lieu (règles SIEM, IDS, revue des journaux). *Le SOC est fondamentalement une mesure détective.*
- **Corrective** — limite les dégâts et rétablit (sauvegardes, réponse à incident, réinstallation).
- Certains référentiels ajoutent des mesures **dissuasives** (bannières d'avertissement, mentions légales) et **compensatoires** (une alternative lorsque la mesure souhaitée est impossible).

**Par type**

- **Technique** (logique) — mise en œuvre dans la technologie : chiffrement, EDR, contrôle d'accès.
- **Organisationnelle** (administrative) — politiques, procédures, formation, vérifications des antécédents.
- **Physique** — serrures, badges, vidéosurveillance, contrôles environnementaux.

| Mesure | Fonction | Type |
|---|---|---|
| Authentification multifacteur | Préventive | Technique |
| Sensibilisation à la sécurité | Préventive | Organisationnelle |
| Règle de corrélation SIEM | Détective | Technique |
| Enregistrement vidéo | Détective | Physique |
| Sauvegardes immuables | Corrective | Technique |
| Plan de réponse à incident | Corrective | Organisationnelle |

## L'architecture de sécurité en bref

Une architecture de sécurité organise les mesures de façon cohérente. Notions que vous retrouverez tout au long du cours :

- **Segmentation réseau** — séparer les réseaux utilisateurs, serveurs, administration et OT pour qu'une compromission dans une zone n'expose pas toutes les autres.
- **DMZ** — réseau tampon pour les services exposés sur Internet.
- **L'identité comme périmètre** — dans le cloud, les fournisseurs d'identité (comme Microsoft Entra ID) deviennent le principal point d'application.
- **Architecture de journalisation** — collecteurs, transmetteurs et SIEM central pour que les preuves survivent à la compromission d'un hôte.

## Le SOC au sein des opérations de sécurité

Un **Security Operations Center** est l'ensemble de l'équipe, des processus et des technologies qui surveillent, détectent, investiguent et répondent en continu aux événements de sécurité. Il fait partie de la « Blue Team », le versant défensif de la sécurité.

Responsabilités typiques d'un SOC :

1. **Surveillance et tri** des alertes issues du SIEM, de l'EDR, de la sécurité de la messagerie, du cloud et de l'identité
2. **Investigation** pour déterminer le périmètre, la cause racine et l'impact
3. **Coordination de la réponse à incident** avec l'informatique, le juridique, la communication et la direction
4. **Ingénierie de détection** — création et ajustement des règles
5. **Chasse aux menaces** — recherche proactive d'activités non détectées
6. **Renseignement sur les menaces** — exploitation et partage
7. **Rapports** d'indicateurs à la direction

Beaucoup de SOC adoptent un modèle à niveaux :

| Niveau | Rôle |
|---|---|
| Niveau 1 — Tri | Examiner les alertes, enrichir, clôturer les faux positifs, escalader les vrais positifs selon les playbooks |
| Niveau 2 — Investigation / réponse | Analyse approfondie, délimitation, décisions de confinement |
| Niveau 3 — Chasse / ingénierie | Chasse aux menaces, ingénierie de détection, analyse de logiciels malveillants, outillage |
| Responsable SOC | Processus, effectifs, indicateurs, communication avec les parties prenantes |

Les SOC modernes estompent de plus en plus ces niveaux grâce à l'automatisation (SOAR) et en donnant à tous les analystes du temps pour l'ingénierie.

### Politiques et procédures qui encadrent le travail du SOC

- **Politique de réponse à incident** — autorité, définitions et niveaux de gravité.
- **Playbooks / runbooks** — traitement pas à pas pour des types d'alertes précis (hameçonnage, logiciel malveillant, voyage impossible).
- **Matrice d'escalade** — qui appeler, quand et comment.
- **Procédure de gestion des preuves** — comment préserver et documenter les preuves.
- **Charte d'utilisation** — ce que les employés peuvent faire, ce qui définit ce qui constitue un abus.

## Erreurs fréquentes

- **Confondre la gravité d'une vulnérabilité et le risque.** Un CVSS 9,8 sur un hôte de lab isolé peut représenter un risque moindre qu'un CVSS 6,5 sur la passerelle de paiement.
- **Considérer le SOC comme la seule mesure.** La détection ne compense pas l'absence des fondamentaux comme la MFA et les correctifs.
- **Escalader sans contexte.** « Connexion suspecte » n'est pas exploitable ; « connexion réussie du directeur financier depuis un nouveau pays, 10 minutes après 40 échecs, sans défi MFA enregistré » l'est.

## Implications de sécurité

- Inventoriez les actifs ; on ne peut pas évaluer le risque sur des actifs dont on ignore l'existence.
- Privilégiez des mesures superposées et indépendantes pour les risques à fort impact.
- Assurez-vous que chaque playbook précise quand et à qui escalader — l'ambiguïté coûte du temps pendant un incident.

## Vérification des connaissances

:::check Questions
1. « Un serveur Exchange non corrigé » est-il une menace, une vulnérabilité ou un risque ?
2. Classez « alerte SIEM sur une suppression massive de fichiers » par fonction et par type.
3. Quel vecteur d'attaque est le plus associé aux rafales d'événements Windows 4625 ?
4. Qu'est-ce qui distingue les responsabilités d'un analyste de niveau 1 de celles d'un niveau 2 ?
:::

:::solution Réponses
1. Une **vulnérabilité**. Le risque est la vraisemblance qu'une menace l'exploite, combinée à l'impact sur l'activité.
2. **Détective, technique.**
3. **L'abus d'identifiants** — force brute ou pulvérisation de mots de passe.
4. Le niveau 1 trie et escalade selon les playbooks ; le niveau 2 mène l'investigation approfondie, la délimitation et les décisions de confinement.
:::

## Exercice

La messagerie web publique de votre organisation n'impose pas la MFA. Le renseignement sur les menaces signale des campagnes actives de pulvérisation de mots de passe contre votre secteur.

1. Identifiez l'actif, la menace, la vulnérabilité et le risque qui en résulte.
2. Proposez une mesure préventive, une détective et une corrective.
3. Rédigez une note d'escalade de deux phrases pour votre responsable SOC.

:::solution Corrigé
1. **Actif :** les boîtes aux lettres, les données et la confiance qu'elles portent. **Menace :** groupes criminels pratiquant la pulvérisation de mots de passe. **Vulnérabilité :** absence de MFA sur la messagerie web. **Risque :** compromission probable de comptes menant à une exposition de données et à une fraude par compromission de messagerie professionnelle.
2. **Préventive :** imposer la MFA et bloquer l'authentification héritée. **Détective :** règle SIEM détectant de nombreux comptes en échec depuis une même source, suivis d'un succès. **Corrective :** réinitialisation forcée du mot de passe, révocation des sessions et revue des règles de boîte aux lettres des comptes touchés.
3. « La messagerie web n'a pas de MFA alors qu'une pulvérisation de mots de passe vise activement notre secteur ; une seule réussite donnerait à un attaquant l'accès à une boîte aux lettres et permettrait une fraude au virement. Je recommande d'imposer la MFA cette semaine et j'ai activé entre-temps une règle de détection de pulvérisation. »
:::

## Synthèse et prochaines étapes

- Menace, vulnérabilité, risque et mesure sont des notions distinctes ; employez-les avec précision.
- La plupart des intrusions commencent par l'hameçonnage, l'abus d'identifiants ou l'exploitation de services exposés — chacun laisse des traces reconnaissables.
- Le SOC est une capacité détective et corrective au sein d'un dispositif de contrôle plus large.

Le module suivant, **Réseaux pour analystes SOC**, vous donne les bases réseau nécessaires pour lire directement ces traces.

===assignment===
Complétez la liste d'actifs du devoir précédent. Pour chacun de vos trois actifs, décrivez **un scénario de menace réaliste** avec le vocabulaire de cette leçon (menace → vulnérabilité → impact), indiquez le vecteur d'attaque et listez les **sources de journaux** dont un SOC aurait besoin pour le détecter.

===criteria===
- Terminer la lecture, la vérification des connaissances et l'exercice.
- Marquer la leçon comme terminée. Le quiz du module Fondamentaux puise ses questions dans cette leçon et la précédente.

===references===
- ISO/IEC 27000:2018, *Vue d'ensemble et vocabulaire* (définitions du risque et de la mesure) — https://www.iso.org/standard/73906.html
- Matrice MITRE ATT&CK Enterprise — https://attack.mitre.org/matrices/enterprise/
- MITRE ATT&CK T1110, Brute Force — https://attack.mitre.org/techniques/T1110/
- MITRE ATT&CK T1190, Exploit Public-Facing Application — https://attack.mitre.org/techniques/T1190/
- CISA, *Known Exploited Vulnerabilities Catalog* — https://www.cisa.gov/known-exploited-vulnerabilities-catalog
