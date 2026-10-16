---
title: Sécurité d'Active Directory et chemins d'attaque
chapter: Comprendre et durcir Active Directory
summary: Comprendre comment Active Directory est structuré, pourquoi il devient si souvent le chemin d'un attaquant vers le contrôle de tout le domaine, et — de façon conceptuelle et défensive — les classes de mauvaise configuration qui créent des chemins d'attaque et comment les durcir et les surveiller.
duration: 55
level: intermediate
preview: true
status: published
objectives:
- Décrire les composants fondamentaux d'Active Directory et pourquoi c'est une cible de grande valeur
- Expliquer, conceptuellement, comment fonctionne l'authentification (Kerberos/NTLM) dans AD
- Reconnaître les classes de mauvaise configuration qui créent des chemins d'attaque
- Expliquer la pensée en chemins d'attaque et pourquoi un maillon faible peut atteindre l'admin de domaine
- Appliquer le tiering, le moindre privilège et la surveillance pour durcir et détecter
prerequisites:
- Linux et fondamentaux techniques pour testeurs
---

## Introduction

**Active Directory (AD)** est la colonne vertébrale de l'identité et des accès de la plupart des environnements Windows d'entreprise — il décide qui peut se connecter, à quoi, et avec quels privilèges sur des milliers de machines. Cela en fait le système le plus lourd de conséquences qu'un attaquant puisse viser : compromettre AD, c'est souvent compromettre toute l'organisation. C'est précisément pour cela que comprendre AD est essentiel pour l'évaluer comme pour le défendre.

Cette leçon est conceptuelle et défensive. Elle explique comment AD fonctionne, pourquoi il devient un chemin d'attaque, et les *classes* de mauvaise configuration qui créent ces chemins — pour que vous puissiez les **trouver et les corriger**. Elle ne fournit délibérément ni outillage d'attaque, ni commandes, ni exploitation pas à pas ; tout test AD pratique relève uniquement d'un lab autorisé et isolé, dans les règles d'engagement du premier module. Le but ici est la compréhension dont un défenseur et un évaluateur ont tous deux besoin.

## Ce qu'est Active Directory

AD est un **service d'annuaire** : une base de données centrale des identités et ressources d'une organisation, avec les règles régissant l'accès. Ses objets et sa structure fondamentaux :

| Concept | Ce que c'est |
|---|---|
| **Domaine** | Une frontière d'administration et une base d'identités partagée |
| **Contrôleur de domaine (DC)** | Un serveur exécutant AD ; détient l'annuaire et authentifie les utilisateurs — le joyau de la couronne |
| **Utilisateurs / ordinateurs / groupes** | Les principaux (qui) et les objets sur lesquels ils agissent (quoi) |
| **Unités d'organisation (OU)** | Conteneurs pour organiser et déléguer la gestion |
| **Stratégie de groupe (GPO)** | Configuration centralisée poussée vers les utilisateurs et machines |
| **Groupes privilégiés** | p. ex. Admins du domaine — l'appartenance contrôle de fait le domaine |
| **Relations d'approbation (trusts)** | Relations laissant les utilisateurs d'un domaine accéder à un autre |

Parce que le **contrôleur de domaine** authentifie tout le monde et détient le vérificateur de chaque identifiant, le contrôle d'un DC est le contrôle du domaine. Une grande partie de la sécurité AD revient en définitive à protéger les DC et le petit nombre de comptes et de chemins qui peuvent les atteindre.

## Comment fonctionne l'authentification (conceptuellement)

AD utilise principalement **Kerberos** (avec le **NTLM** hérité encore présent). Vous n'avez pas besoin du détail cryptographique pour comprendre le modèle de sécurité :

- Quand un utilisateur se connecte, il obtient du DC un **ticket d'octroi de tickets (TGT)** qui prouve qui il est.
- Pour utiliser un service, il présente le TGT et obtient un **ticket de service** pour ce service précis.
- Les services et comptes sont identifiés par des **noms principaux de service (SPN)**.

Les conséquences pertinentes pour la sécurité — celles qui importent à un défenseur — sont :

- **Les identifiants et tickets vivent en mémoire** sur les machines où les utilisateurs se connectent. Une machine qu'un attaquant contrôle peut exposer les secrets de quiconque s'y est connecté — d'où l'importance énorme de *là où les comptes privilégiés se connectent*.
- **Le NTLM et les configurations Kerberos faibles** sont des mécanismes plus anciens et plus faibles ; leur présence est une lacune de durcissement.
- **Les comptes de service avec des SPN** peuvent voir leurs tickets de service demandés par n'importe quel utilisateur, de sorte qu'un mot de passe de compte de service faible est exposé à la devinette hors ligne — une classe de faiblesse bien connue que des mots de passe forts et longs (ou des comptes de service gérés par groupe) défendent.

Vous apprenez cela non pour le réaliser mais pour reconnaître les faiblesses qu'il implique et les fermer.

## Classes de mauvaise configuration qui créent des chemins d'attaque

AD est rarement brisé par un exploit logiciel ; il l'est généralement par **mauvaise configuration et privilège excessif** accumulés au fil des ans. Les classes récurrentes (décrites comme des risques à trouver et corriger, non comme des procédures) :

- **Privilège excessif** — bien trop de comptes dans Admins du domaine ou d'autres groupes à privilèges élevés ; des admins utilisant des comptes privilégiés pour le travail quotidien ; des comptes de service dotés d'admin de domaine « pour que ça marche ».
- **Exposition d'identifiants** — des comptes privilégiés se connectant sur des postes ordinaires, laissant leurs identifiants en mémoire sur des machines qu'une compromission d'utilisateur normal peut atteindre.
- **Mots de passe de comptes de service faibles** — comptes porteurs de SPN avec des mots de passe courts, anciens ou devinables, exposés au cassage hors ligne.
- **Délégation dangereuse** — comptes ou ordinateurs configurés avec une délégation Kerberos leur permettant d'usurper d'autres identités, souvent bien plus largement que prévu.
- **ACL / permissions non sûres** — un objet pouvant en modifier un autre (réinitialiser un mot de passe, ajouter un membre, éditer une GPO) en chaînes qui finissent par atteindre un objet privilégié.
- **Protocoles hérités et DC non corrigés** — NTLM, SMBv1, ou correctifs critiques manquants sur les contrôleurs de domaine.
- **Faiblesses de GPO** — une stratégie de groupe largement liée qu'un compte à privilège moindre peut éditer devient un chemin vers une compromission de masse.

Chacune est quelque chose qu'un **évaluateur inventorie et qu'un défenseur corrige** — et chacune correspond à une action de durcissement concrète ci-dessous.

## La pensée en chemins d'attaque

L'idée déterminante de la sécurité AD est le **chemin d'attaque** : AD est un graphe de *qui peut contrôler qui*, et un attaquant n'a besoin que d'une chaîne de petits privilèges, individuellement banals, depuis son point d'ancrage jusqu'à un objet privilégié.

```text
 Utilisateur normal ──(peut réinitialiser le mdp de)──►  Compte de service
      │                                                       │
      │                                            (est admin local sur)
      ▼                                                       ▼
 Poste de travail ◄──(un Admin du domaine s'y est connecté)── Serveur
      │                                                       │
      └──────────► identifiant en mémoire ────────────────────┘
                              │
                              ▼
                     Admin du domaine  ──►  Contrôleur de domaine
```

Aucun maillon isolé n'est spectaculaire ; c'est la *chaîne* qui l'est. Voilà pourquoi « nous n'avons aucune vulnérabilité AD critique » peut être faux alors que l'environnement est trivialement prenable — la faiblesse est le **graphe accumulé de privilèges**, non une CVE. Défenseurs et évaluateurs analysent de plus en plus AD exactement comme ce graphe : trouver les chemins les plus courts de n'importe quel utilisateur ordinaire vers Admin du domaine et les couper. L'objectif défensif est de rendre ces chemins longs, rares et surveillés.

:::tip Pensez en chemins, pas seulement en permissions
Une permission trop large isolée paraît inoffensive. Son danger est de savoir si elle *se trouve sur un chemin* vers quelque chose de privilégié. Le travail de sécurité AD le plus précieux est de cartographier et raccourcir ces chemins — retirer les maillons (appartenances excessives, ACL risquées, connexions privilégiées au mauvais endroit) qui relient un point d'ancrage ordinaire au contrôle du domaine. C'est la forme propre à AD du principe de moindre privilège de la leçon fondamentaux Linux.
:::

## Durcissement et surveillance

Chaque classe de mauvaise configuration a une réponse défensive. Les mesures de durcissement fondamentales :

- **Tiering (modèle d'accès privilégié)** — séparer l'administration en niveaux pour que les identifiants DC/admin de domaine ne soient *utilisés que sur* des postes d'administration sécurisés et jamais sur des points de terminaison ordinaires. Cela brise directement le maillon « identifiant en mémoire sur un poste normal ».
- **Moindre privilège** — réduire Admins du domaine aux rares qui en ont vraiment besoin ; ne donner aux comptes de service que les droits requis ; supprimer le privilège permanent là où un accès juste-à-temps peut le remplacer.
- **Protéger les comptes de service** — mots de passe longs et aléatoires ou comptes de service gérés par groupe (pour qu'il n'y ait pas de mot de passe faible à casser), et privilèges minimaux.
- **Revoir les ACL et la délégation** — auditer qui peut modifier les objets privilégiés et les GPO ; supprimer la délégation risquée ; traiter la capacité d'éditer une GPO largement liée comme privilégiée.
- **Retirer les protocoles hérités** — désactiver NTLM et SMBv1 lorsque possible, imposer des réglages Kerberos forts, et garder les DC corrigés.
- **Surveiller** — alimenter les événements de sécurité AD dans le SIEM (le territoire du parcours SOC) : changements de groupes privilégiés, demandes de tickets inhabituelles, anomalies d'authentification, schémas de connexion aux DC, et changements d'objets critiques. Beaucoup d'étapes d'attaque AD sont bruyantes dans les journaux si vous surveillez.

:::warning Protégez et surveillez les contrôleurs de domaine avant tout
Parce qu'un DC détient le vérificateur de chaque identifiant et authentifie le domaine, la compromission d'un DC est de fait la fin de la partie. Durcissez les DC le plus fort (correctifs, logiciels minimaux, accès admin en tiering uniquement), restreignez qui peut s'y connecter, et surveillez-les le plus étroitement. La question défensive AD la plus importante est : *qui peut atteindre un DC ou un Admin du domaine, et par quel chemin ?*
:::

## Erreurs courantes

- **Traiter la sécurité AD comme de l'application de correctifs.** La plupart des compromissions AD sont de la mauvaise configuration et du privilège excessif, non un correctif manquant ; durcissez la configuration et le graphe de privilèges.
- **Penser permission par permission.** Le risque d'une permission tient à sa présence sur un chemin vers le privilège ; analysez les chemins, non les octrois isolés.
- **Des connexions privilégiées partout.** Des Admins du domaine se connectant sur des postes ordinaires dispersent leurs identifiants là où les attaquants peuvent les atteindre — le tiering existe pour l'empêcher.
- **Des comptes de service « pour que ça marche ».** Donner l'admin de domaine à un compte de service est un chemin permanent vers la compromission totale ; donnez le moindre privilège et des identifiants forts.
- **Ne pas surveiller AD.** Beaucoup d'étapes de chemin d'attaque sont visibles dans les journaux de sécurité ; alimenter la télémétrie AD vers le SOC est essentiel.

## Implications pour la sécurité

- Le contrôleur de domaine et les comptes privilégiés sont les joyaux de la couronne ; protéger les chemins qui y mènent est le cœur de la sécurité AD.
- Analysez AD comme un graphe de privilèges et raccourcissez/coupez les chemins des utilisateurs ordinaires vers Admin du domaine.
- Appliquez le tiering et le moindre privilège pour que les identifiants privilégiés n'atterrissent jamais là où une compromission ordinaire peut les atteindre.
- Protégez les comptes de service, revoyez les ACL/délégations, retirez les protocoles hérités, et surveillez les événements AD dans le SOC.

## Vérification des connaissances

:::check Questions
1. Pourquoi un contrôleur de domaine est-il le système le plus lourd de conséquences à protéger dans un environnement AD ?
2. Qu'est-ce qu'un « chemin d'attaque » dans Active Directory, et pourquoi un environnement sans CVE critique peut-il quand même être facilement compromis ?
3. Nommez trois classes de mauvaise configuration qui créent des chemins d'attaque AD.
4. Comment le modèle d'accès privilégié en tiering brise-t-il un chemin d'attaque courant ?
:::

:::solution Réponses
1. Parce que le DC exécute AD, authentifie chaque principal, et détient le vérificateur de chaque identifiant — de sorte que contrôler un DC signifie contrôler tout le domaine (compromission totale de fait). Protéger les DC et les chemins qui y mènent est donc central.
2. Un **chemin d'attaque** est une chaîne de petits privilèges individuels (qui peut contrôler qui) menant du point d'ancrage de l'attaquant à un objet privilégié comme Admin du domaine. Un environnement peut n'avoir aucune vulnérabilité logicielle critique et pourtant être compromis, car la faiblesse est le **graphe accumulé de privilège excessif et de mauvaise configuration**, non une CVE.
3. Trois parmi : privilège excessif (groupes admin surchargés, admin pour un usage quotidien) ; exposition d'identifiants privilégiés (connexions privilégiées sur des machines ordinaires) ; mots de passe de comptes de service faibles (comptes SPN) ; délégation Kerberos dangereuse ; chaînes d'ACL/permissions non sûres ; protocoles hérités/DC non corrigés ; GPO largement liées éditables.
4. Le **tiering** confine les identifiants privilégiés (DC/Admin du domaine) à des postes d'administration sécurisés et interdit leur usage sur les points de terminaison ordinaires — de sorte qu'un attaquant qui compromet un poste normal n'y trouve jamais d'identifiants privilégiés en mémoire, brisant le maillon « identifiant en mémoire → admin de domaine ».
:::

## Exercice

Pour un domaine Windows que vous êtes autorisé à revoir (conceptuellement — aucun test en direct) :

1. Listez **quatre classes de mauvaise configuration** que vous rechercheriez et, pour chacune, pourquoi elle crée un chemin d'attaque.
2. Esquissez un **chemin d'attaque** plausible (en chaîne de qui-peut-contrôler-qui) d'un utilisateur ordinaire vers Admin du domaine, en étiquetant chaque maillon par la mauvaise configuration qui le permet.
3. Pour chaque maillon de votre chemin, donnez la **mesure de durcissement** qui le couperait.
4. Nommez **trois événements AD** que vous transféreriez au SOC et ce que chacun aiderait à détecter.

:::solution Réponse type (abrégée)
1. Appartenance excessive à Admins du domaine (trop de chemins permanents vers le contrôle total) ; connexions privilégiées sur des postes ordinaires (exposition d'identifiants en mémoire) ; mots de passe de comptes de service SPN faibles (exposition au cassage hors ligne) ; droits d'édition de GPO risqués (compromission de configuration de masse).
2. *Chemin :* utilisateur ordinaire → peut éditer une GPO largement liée (faiblesse de GPO) → la GPO configure un serveur → un Admin du domaine se connecte à ce serveur (exposition d'identifiant privilégié) → son identifiant est en mémoire → Admin du domaine → DC. Chaque flèche est une mauvaise configuration.
3. Restreindre les droits d'édition de GPO à des admins privilégiés et audités ; imposer le tiering pour que les Admins du domaine ne se connectent jamais à des serveurs hors tier-0 ; réduire Admins du domaine et utiliser un accès juste-à-temps ; durcir/surveiller le DC.
4. Changements d'appartenance aux groupes privilégiés (élévation de privilèges/persistance) ; demandes de tickets de service Kerberos anormales (attaques de comptes de service) ; connexion aux DC et changements d'objets critiques (compromission DC/tier-0).
:::

## Résumé et étapes suivantes

- Active Directory est la colonne vertébrale de l'identité et la cible de plus grande valeur ; le **contrôleur de domaine** et les comptes privilégiés sont les joyaux de la couronne.
- AD est compromis surtout par **mauvaise configuration et privilège excessif**, mieux compris comme un **graphe de chemins d'attaque** — une chaîne de petits privilèges atteignant le contrôle du domaine.
- Défendez par le **tiering** et le **moindre privilège** (garder les identifiants privilégiés hors des machines ordinaires), en protégeant les comptes de service, en revoyant les ACL/délégations, en retirant les protocoles hérités, et en **surveillant** AD dans le SOC.
- Le test AD pratique ne se fait que dans des labs autorisés et isolés, dans les règles d'engagement ; cette leçon donne la compréhension pour évaluer et, surtout, défendre.

Les modules Pentest restants (énumération réseau, techniques d'exploitation et d'élévation de privilèges) sont opérationnels et ne se pratiquent que dans des labs isolés sous autorisation explicite ; cette leçon vous outille pour comprendre et durcir l'environnement qu'ils concernent.

===assignment===
Produisez une **note de durcissement et de surveillance Active Directory** d'une page pour une organisation de votre choix : les classes de mauvaise configuration que vous auditeriez, un graphe de chemin d'attaque esquissé (utilisateur ordinaire → Admin du domaine) avec, pour chaque maillon, la mauvaise configuration qui le permet et la mesure de durcissement qui le coupe, une courte section appliquant le tiering et le moindre privilège, et une liste d'événements de sécurité AD que vous transféreriez au SOC avec ce que chacun détecte. Gardez-la entièrement défensive — aucun outillage ni commande d'attaque.

===criteria===
- Terminez la lecture et la vérification des connaissances, et produisez la note.
- La note doit analyser AD comme des chemins d'attaque (non des permissions isolées), associer chaque mauvaise configuration à une mesure de durcissement concrète, appliquer le tiering/moindre privilège, et inclure la surveillance SOC ; le contenu reste conceptuel et défensif (aucune étape ni outillage d'exploitation).
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- Microsoft, *Securing privileged access / modèle d'accès d'entreprise (tiering)* — https://learn.microsoft.com/security/privileged-access-workstations/overview
- Microsoft, *Bonnes pratiques de sécurité Active Directory* — https://learn.microsoft.com/windows-server/identity/ad-ds/plan/security-best-practices/best-practices-for-securing-active-directory
- MITRE ATT&CK, tactiques Credential Access et Lateral Movement — https://attack.mitre.org/tactics/TA0006/
- CISA, orientations sur le durcissement de l'identité et d'Active Directory — https://www.cisa.gov/
- NIST SP 800-63 / concepts de gestion des accès privilégiés — https://pages.nist.gov/800-63-3/
