---
title: Fondements de la gouvernance, du risque et de la conformité
chapter: Fondamentaux de la GRC
summary: Comprendre ce que recouvrent la gouvernance, le risque et la conformité, comment ces trois disciplines se renforcent mutuellement, et quels documents, rôles et structures de responsabilité transforment la sécurité, d'un ensemble d'outils, en une fonction métier pilotée.
duration: 50
level: beginner
preview: true
status: published
objectives:
- Définir la gouvernance, le risque et la conformité et expliquer comment les trois disciplines s'articulent
- Distinguer politiques, normes, procédures et lignes directrices et savoir quand employer chacune
- Expliquer l'appétence et la tolérance au risque et pourquoi la direction doit les fixer
- Décrire le modèle des trois lignes et comment il sépare la propriété, la supervision et l'assurance
- Identifier les principaux rôles de gouvernance de la sécurité et ce dont chacun répond
prerequisites:
- Aucun — il s'agit de la première leçon du parcours GRC
---

## Introduction

La plupart des organisations peuvent acheter des pare-feu, une protection des postes et un SIEM. Beaucoup moins savent répondre à une question plus simple : *qui a décidé que c'étaient les bons investissements, qui répond des défaillances, et comment prouver à un client ou à un régulateur que ces dispositifs fonctionnent ?* Répondre à cette question, c'est le rôle de la **gouvernance, du risque et de la conformité** — la GRC.

La GRC n'est ni un produit, ni un service que l'on ajoute après coup. C'est la manière dont une organisation dirige sa sécurité, décide quelle part de risque elle accepte de porter, et démontre — preuves à l'appui — qu'elle fait ce qu'elle annonce. Cette leçon donne le vocabulaire et le modèle mental sur lesquels repose tout le reste du parcours.

## Les trois disciplines

| Discipline | Question à laquelle elle répond | Exemple de livrable |
|---|---|---|
| **Gouvernance** | Qui décide, qui répond, et quelles sont les règles ? | Une politique de sécurité approuvée par le conseil ; des rôles définis |
| **Risque** | Qu'est-ce qui peut mal tourner, avec quelle gravité, et qu'en faisons-nous ? | Un registre des risques avec plans de traitement |
| **Conformité** | Respectons-nous nos obligations, et pouvons-nous le prouver ? | Des preuves que les mesures fonctionnent ; des rapports d'audit |

Elles s'imbriquent volontairement :

```text
        GOUVERNANCE
   fixe la direction, les rôles,
     l'appétence au risque
          │
          ▼
        RISQUE  ─────────► identifie et traite ce qui compte
          │
          ▼
      CONFORMITÉ ─────────► prouve que les obligations et mesures sont respectées
          │
          └──── rétroaction ────► éclaire les décisions de gouvernance
```

La gouvernance fixe la direction et la part de risque acceptable. La gestion des risques travaille dans ce cadre pour repérer et traiter ce qui compte. La conformité démontre que les mesures et obligations légales qui en découlent sont réellement respectées, et renvoie les résultats pour que la gouvernance s'ajuste. Retirez-en une seule et les autres s'affaiblissent : un travail sur le risque sans gouvernance n'a aucun mandat ; une conformité sans travail sur le risque devient du remplissage de cases.

:::note La GRC est une fonction métier, pas seulement informatique
Une erreur fréquente est de voir la GRC comme « la paperasse de l'équipe sécurité ». Dans les organisations matures, les décisions de GRC sont prises par les responsables métier qui portent le risque, éclairés par les experts en sécurité. La sécurité conseille ; le métier décide et en répond.
:::

## Gouvernance : direction et responsabilité

La gouvernance est le système par lequel une organisation est dirigée et maîtrisée. Pour la sécurité de l'information, elle établit :

- **La direction** — une stratégie de sécurité alignée sur les objectifs métier.
- **Les rôles et la responsabilité** — qui est propriétaire de quoi, et qui répond des défaillances.
- **Les règles** — les politiques et normes que chacun doit suivre.
- **La supervision** — la façon dont la direction vérifie que la sécurité fonctionne.

### La hiérarchie documentaire

La gouvernance s'écrit selon une hiérarchie délibérée. Confondre ces quatre types de documents est l'une des erreurs de débutant les plus courantes.

| Document | Ce que c'est | Obligatoire ? | Exemple |
|---|---|---|---|
| **Politique** | Énoncé de haut niveau de l'intention et des règles, approuvé par la direction | Oui | « Tout accès aux systèmes de l'entreprise requiert une authentification multifacteur. » |
| **Norme** | Exigence précise et mesurable qui appuie une politique | Oui | « La MFA doit utiliser une méthode résistante à l'hameçonnage ; le SMS n'est pas autorisé. » |
| **Procédure** | Instructions pas à pas pour réaliser une tâche | Oui (pour la tâche) | « Comment inscrire un nouvel employé à la MFA » |
| **Ligne directrice** | Bonne pratique recommandée, non obligatoire | Non | « Envisager une clé matérielle pour les administrateurs. » |

Pour retenir la différence : une **politique** dit *quoi et pourquoi*, une **norme** dit *à quel niveau / dans quelle mesure*, une **procédure** dit *exactement comment, étape par étape*, et une **ligne directrice** dit *ce que nous suggérons*.

```text
POLITIQUE    (pourquoi + quoi, obligatoire, approuvée par la direction)
   └── NORME       (exigence mesurable, obligatoire)
          └── PROCÉDURE  (pas à pas, obligatoire pour la tâche)

LIGNE DIRECTRICE  (recommandée, facultative — appuie sans contraindre)
```

:::tip Rédigez des politiques durables
Les bonnes politiques sont neutres sur le plan technologique afin de survivre aux changements d'outils. « L'accès exige une authentification forte » est une politique durable ; « Chacun doit utiliser l'application Acme Authenticator v3 » relève d'une norme ou d'une procédure, que l'on peut mettre à jour sans l'aval du conseil.
:::

## Appétence et tolérance au risque

La direction ne peut pas éliminer tout risque — ce serait infiniment coûteux et paralyserait l'activité. Elle décide plutôt **quelle part de risque est acceptable**. Deux termes liés expriment cela :

- **L'appétence au risque** — la quantité et le type de risque que l'organisation est *disposée à rechercher ou à accepter* pour atteindre ses objectifs. Elle est stratégique et fixée au niveau du conseil. Exemple : *« Nous avons une faible appétence pour les risques pesant sur les données clients et une appétence modérée pour les risques qui ralentissent la livraison produit. »*
- **La tolérance au risque** — la *variation acceptable* autour d'un objectif ou d'un risque donné ; elle est plus concrète et mesurable. Exemple : *« Aucune vulnérabilité critique non résolue sur un système exposé à Internet ne peut rester ouverte plus de 7 jours. »*

L'appétence est la philosophie ; la tolérance est la limite mesurable. Les deux doivent venir de la direction, car elle seule répond des conséquences de l'acceptation d'un risque. Le rôle de l'équipe risque est de rendre les risques visibles et d'aider les dirigeants à décider — non de décider à leur place.

:::scenario L'appétence en pratique
Une société de paiement affiche une **très faible appétence** pour le risque de confidentialité sur les données de porteurs de carte, et une **appétence plus élevée** pour le risque de disponibilité de son wiki interne. Cette seule phrase indique à l'équipe sécurité où investir en priorité : des mesures fortes et redondantes autour des données de carte ; des mesures « suffisantes » sur le wiki. Sans appétence déclarée, les équipes soit surprotègent tout (trop coûteux), soit protègent tout de manière égale (les mauvais actifs reçoivent la même attention que les joyaux de la couronne).
:::

## Responsabilité : le modèle des trois lignes

Comment éviter que la gouvernance ne devienne soit un free-for-all, soit un nœud bureaucratique ? Le **modèle des trois lignes** (de l'Institute of Internal Auditors) sépare trois types distincts de responsabilité pour qu'ils n'entrent pas en conflit.

| Ligne | Qui | Rôle |
|---|---|---|
| **Première ligne** | L'encadrement opérationnel et le personnel qui portent et exécutent le risque (p. ex. informatique, développeurs) | Porter et gérer le risque au quotidien ; mettre en œuvre les mesures |
| **Deuxième ligne** | Les fonctions risque, conformité et sécurité | Définir la politique, conseiller, challenger et surveiller la première ligne |
| **Troisième ligne** | L'audit interne | Fournir une assurance indépendante au conseil que les deux premières lignes fonctionnent |

Le principe clé est **l'indépendance de l'assurance**. Les personnes qui exploitent une mesure (première ligne) ne doivent pas être celles qui vérifient indépendamment qu'elle fonctionne (troisième ligne) — sinon « nous avons corrigé notre propre copie et tout va bien » devient la seule réponse possible. La deuxième ligne fixe les règles et surveille ; la troisième, indépendante des deux, dit la vérité à l'organe de gouvernance.

:::note Responsabilité (accountability) et responsabilité opérationnelle (responsibility)
Le français emploie souvent un seul mot pour deux idées. La **responsabilité opérationnelle** peut se partager et se déléguer — beaucoup de personnes sont responsables des correctifs. La **responsabilité de rendre compte** ne se délègue pas — une personne nommée répond de la bonne application des correctifs. Une bonne gouvernance attribue un unique responsable comptable à chaque résultat important.
:::

## Rôles clés de gouvernance de la sécurité

Les intitulés varient d'une organisation à l'autre, mais les responsabilités sont constantes :

- **Conseil / direction générale** — comptable en dernier ressort de la sécurité ; approuve la stratégie, la politique et l'appétence au risque ; alloue le financement.
- **RSSI (Responsable de la sécurité des systèmes d'information)** — pilote le programme de sécurité ; traduit le risque métier en stratégie de sécurité ; rend compte du risque à la direction.
- **Propriétaire du risque** — le responsable métier comptable d'un risque précis et de la décision sur son traitement.
- **Propriétaire de la mesure** — comptable de la conception et du bon fonctionnement d'une mesure donnée.
- **Propriétaire des données** — comptable d'un ensemble de données : sa classification, qui peut y accéder, et sa durée de conservation.
- **Dépositaire / gestionnaire des données** — met en œuvre dans les systèmes les décisions du propriétaire des données.
- **L'ensemble du personnel** — responsable du respect de la politique et du signalement des incidents.

Un thème récurrent : **la personne comptable d'un risque est un responsable métier, pas l'équipe sécurité.** La sécurité favorise de bonnes décisions ; elle n'absorbe pas la responsabilité des risques que le métier choisit de courir.

## La place de la conformité

La conformité consiste à respecter des obligations — et à pouvoir le **prouver**. Les obligations viennent de trois sources :

1. **Légales et réglementaires** — lois et règlements qui s'appliquent à vous (droit de la protection des données, règles sectorielles).
2. **Contractuelles** — engagements envers les clients et partenaires (clauses de sécurité, niveaux de service).
3. **Internes** — vos propres politiques et normes.

La conformité repose sur la preuve. « Nous exigeons la MFA » est une affirmation ; les journaux de connexion, l'export de configuration et une revue des accès signée par le propriétaire de la mesure sont les **preuves** qui transforment l'affirmation en quelque chose auquel un auditeur — ou un client — peut se fier. Une grande partie des modules suivants consiste à produire et organiser ces preuves.

:::warning Conforme n'est pas synonyme de sécurisé
Respecter une norme est un plancher, pas un plafond. Une organisation peut réussir un audit et tout de même être compromise, car les normes retardent sur les menaces et les auditeurs échantillonnent au lieu de tout tester. Considérez la conformité comme la preuve que des mesures de base existent — puis utilisez la gestion des risques pour décider où aller plus loin.
:::

## Erreurs courantes

- **La prolifération des politiques.** Des dizaines de documents redondants que personne ne lit. Moins de documents, plus clairs et tenus à jour, valent mieux qu'une vaste bibliothèque jamais ouverte.
- **Laisser la sécurité « porter » le risque.** Les risques appartiennent aux responsables métier qui peuvent les accepter ou les financer ; les confier à l'équipe sécurité supprime toute responsabilité réelle.
- **Confondre les types de documents.** Mettre des instructions pas à pas dans une politique de niveau conseil oblige à faire valider chaque petite modification par la direction.
- **Le théâtre de la conformité.** Collecter des preuves que des mesures existent sur le papier alors qu'elles ne fonctionnent pas en pratique.

## Implications pour la sécurité

- Chaque résultat de sécurité important devrait avoir un unique propriétaire nommé et comptable.
- L'appétence au risque doit être énoncée par la direction, sinon les équipes la devineront — et de façon incohérente.
- Gardez l'assurance indépendante : ne laissez pas l'équipe qui exploite une mesure en être le seul contrôleur.
- Concevez les mesures pour qu'elles *produisent des preuves* comme sous-produit, et non lors d'une course annuelle.

## Vérification des connaissances

:::check Questions
1. Quel type de document est obligatoire, mesurable et appuie une politique — une norme, une procédure ou une ligne directrice ?
2. Qui devrait fixer l'appétence au risque d'une organisation, et pourquoi ?
3. Dans le modèle des trois lignes, quelle ligne fournit une assurance *indépendante* au conseil ?
4. Donnez une raison pour laquelle une organisation peut être pleinement conforme et pourtant non sécurisée.
:::

:::solution Réponses
1. Une **norme** — c'est une exigence obligatoire et mesurable qui appuie une politique. (Une procédure est pas à pas ; une ligne directrice est facultative.)
2. **La direction / le conseil**, car elle seule répond des conséquences de l'acceptation d'un risque et peut allouer le financement pour le traiter.
3. La **troisième ligne — l'audit interne**, indépendante à la fois des équipes qui exploitent les mesures (première ligne) et des fonctions qui définissent la politique et surveillent (deuxième ligne).
4. Les normes sont un socle et retardent sur les menaces ; les auditeurs échantillonnent plutôt que de tout tester ; et une mesure peut exister sur le papier sans fonctionner réellement. (Une seule raison suffit.)
:::

## Exercice

Une startup en forte croissance n'a aucune gouvernance de sécurité écrite. Les fondateurs vous demandent de proposer les *quatre premiers* artefacts de gouvernance à créer.

1. Nommez les quatre documents ou décisions que vous créeriez en premier et indiquez quelle discipline de GRC (gouvernance, risque ou conformité) chacun sert principalement.
2. Pour l'énoncé d'appétence au risque, rédigez une phrase adaptée à une entreprise dont le principal actif est les données clients.
3. Expliquez, en deux phrases, qui devrait approuver la politique de sécurité de plus haut niveau et pourquoi.

:::solution Réponse type
1. (a) Une brève **politique de sécurité de l'information** approuvée par les fondateurs — *gouvernance* ; (b) un **énoncé d'appétence au risque** — *gouvernance/risque* ; (c) un **registre des risques** léger pour capter les risques majeurs — *risque* ; (d) une liste des **obligations** (lois, clauses clés de contrats clients) à respecter — *conformité*. Un autre premier ensemble raisonnable est acceptable s'il est justifié.
2. « Nous avons une très faible appétence pour les risques susceptibles d'exposer ou de perdre des données clients, et nous privilégierons l'investissement dans la protection de leur confidentialité et de leur intégrité par rapport aux fonctionnalités qui accroissent cette exposition. »
3. Les **fondateurs / le conseil** devraient l'approuver, car ils sont comptables en dernier ressort de la sécurité et la politique fixe le mandat et l'appétence au risque dont tout le reste découle ; l'approbation par la direction est ce qui donne à la politique son autorité dans toute l'organisation.
:::

## Résumé et étapes suivantes

- La GRC transforme la sécurité en une fonction pilotée : la gouvernance dirige, la gestion des risques priorise, la conformité prouve.
- Politiques, normes, procédures et lignes directrices forment une hiérarchie — utilisez la bonne pour le bon usage.
- La direction fixe l'appétence et la tolérance au risque ; c'est le métier, non la sécurité, qui porte le risque.
- Le modèle des trois lignes garde l'assurance indépendante, et chaque résultat clé a besoin d'un unique responsable comptable.

Le module suivant, **Gestion des risques de sécurité de l'information**, reprend la discipline du risque introduite ici et en fait un processus reproductible : identifier, analyser, évaluer et traiter le risque, et le consigner dans un registre des risques défendable.

===assignment===
Choisissez une organisation réelle ou imaginaire (par exemple, un détaillant en ligne de 30 personnes). Produisez un **kit de démarrage de gouvernance** d'une page contenant : (1) un énoncé de politique de sécurité en trois phrases, (2) un énoncé d'appétence au risque d'une ou deux phrases, (3) un tableau attribuant un **propriétaire comptable** à quatre résultats importants (p. ex. contrôle d'accès, sauvegardes, réponse aux incidents, sécurité des fournisseurs), et (4) une brève note rattachant chacun de vos quatre éléments à la gouvernance, au risque ou à la conformité.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Votre devoir doit clairement distinguer un propriétaire comptable des simples responsables opérationnels, et énoncer une appétence au risque en termes métier.
- Marquez la leçon comme terminée. Le quiz du module Fondamentaux de la GRC s'appuie sur cette leçon.

===references===
- ISO/IEC 27000:2018, *Systèmes de management de la sécurité de l'information — Vue d'ensemble et vocabulaire* — https://www.iso.org/standard/73906.html
- ISO/IEC 27001:2022, *Systèmes de management de la sécurité de l'information — Exigences* — https://www.iso.org/standard/27001
- The Institute of Internal Auditors, *The IIA's Three Lines Model (2020)* — https://www.theiia.org/en/content/position-papers/2020/the-iias-three-lines-model-an-update-of-the-three-lines-of-defense/
- NIST, *Cybersecurity Framework 2.0* (la fonction Govern) — https://www.nist.gov/cyberframework
- ISACA, *Risk appetite and risk tolerance* (glossaire et orientations) — https://www.isaca.org/resources/glossary
