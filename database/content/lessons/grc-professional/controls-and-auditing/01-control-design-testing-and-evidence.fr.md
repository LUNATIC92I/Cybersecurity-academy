---
title: Mesures de sécurité — Conception, tests et preuves
chapter: Mesures de sécurité et audit
summary: Transformer les mesures nommées dans votre déclaration d'applicabilité en mesures qui fonctionnent de façon démontrable — bien les concevoir, leur attribuer des propriétaires, tester leur conception et leur efficacité opérationnelle, suivre les constats jusqu'à leur clôture et les surveiller en continu.
duration: 55
level: intermediate
preview: true
status: published
objectives:
- Décrire ce qui fait une mesure bien conçue et nommer les responsabilités de son propriétaire
- Distinguer l'efficacité de conception de l'efficacité opérationnelle et tester chacune
- Collecter des preuves d'audit et juger de leur qualité
- Rédiger un constat clair et suivre la remédiation jusqu'à la clôture
- Expliquer la surveillance continue des mesures et le rôle des dossiers de travail d'audit
prerequisites:
- ISO/IEC 27001 — Le SMSI et la déclaration d'applicabilité
---

## Introduction

Votre déclaration d'applicabilité énumère les mesures sur lesquelles vous vous appuyez. Mais une mesure écrite dans un document est une *affirmation* ; un auditeur, un client et un attaquant se soucient tous de la même chose — **fonctionne-t-elle** réellement, et pouvez-vous le **prouver** ? Cette leçon prend les mesures que vous avez sélectionnées et les rend démontrables : conçues pour réduire le risque, détenues par un responsable comptable, testées en conception et en opération, et étayées par des preuves.

C'est ici que l'honnêteté inhérent/résiduel du module risque et la règle « la DdA doit correspondre à la réalité » du module 27001 deviennent une pratique quotidienne.

## Ce qu'est une mesure — et ce qui en fait une bonne

Une **mesure** est un dispositif qui modifie le risque. Du parcours SOC, vous connaissez les mesures par **fonction** (préventive, détective, corrective) et par **type** (technique, administrative, physique). L'audit ajoute une question plus tranchante : *cette mesure est-elle bien conçue, et fonctionne-t-elle ?*

Une mesure bien conçue est :

- **Reliée au risque** — elle remonte à un risque précis qu'elle doit réduire (comme l'exige la DdA).
- **Précise et mesurable** — « les accès sont revus chaque trimestre par le propriétaire du système » vaut mieux que « les accès sont gérés ».
- **Détenue** — un unique **propriétaire de la mesure** comptable (module 1 : la responsabilité comptable ne se délègue pas).
- **Productrice de preuves** — elle génère un enregistrement comme sous-produit, de sorte que la prouver coûte peu.
- **Proportionnée** — son coût est adapté au risque qu'elle réduit.

:::note Objectif de mesure et mesure
Gardez deux idées distinctes. Un **objectif de mesure** est le résultat recherché (« seuls les utilisateurs autorisés peuvent accéder aux données clients »). Une **mesure** est le dispositif précis qui l'atteint (« l'accès exige la MFA et est revu chaque trimestre »). Plusieurs mesures servent souvent un seul objectif ; les auditeurs testent les mesures mais jugent si l'objectif est atteint.
:::

### Le propriétaire de la mesure

Chaque mesure a besoin d'un unique propriétaire comptable — généralement le responsable de la fonction où elle se situe, non l'équipe sécurité ou audit. Le propriétaire de la mesure est responsable que la mesure soit **conçue** correctement, **fonctionne** comme prévu, **produise des preuves** et soit **corrigée** en cas de défaillance. L'audit teste la mesure ; le propriétaire l'exploite. Confondre ces deux rôles brise l'indépendance qu'exige le modèle des trois lignes.

## Deux formes d'efficacité

Auditer une mesure pose deux questions distinctes, dans l'ordre :

```text
  1. EFFICACITÉ DE CONCEPTION      2. EFFICACITÉ OPÉRATIONNELLE
     Si elle fonctionnait comme       A-t-elle réellement
     prévu, réduirait-elle le         fonctionné ainsi sur
     risque ?                         la période ?
          │                                │
     (inspecter la conception)       (tester des cas réels/échantillons)
```

- **Efficacité de conception** — la mesure *réduirait*-elle le risque si elle fonctionnait comme décrit ? Une revue d'accès trimestrielle qui exclut les administrateurs est mal conçue — elle ne détecterait pas le plus grand risque même parfaitement exécutée.
- **Efficacité opérationnelle** — la mesure *a-t-elle* fonctionné comme conçu tout au long de la période ? Une revue trimestrielle bien conçue mais omise deux trimestres ne fonctionne pas efficacement.

Une mesure doit réussir les **deux**. Une belle conception que personne n'exécute échoue ; une mesure diligemment exécutée mais mal conçue échoue. Testez d'abord la conception — inutile d'échantillonner l'opération d'une mesure qui ne pourrait jamais fonctionner.

## Les tests : les quatre techniques

Les auditeurs (et les propriétaires en auto-évaluation) recueillent des preuves avec quatre techniques classiques, de force à peu près croissante :

| Technique | Ce que c'est | Exemple | Force |
|---|---|---|---|
| **Entretien (inquiry)** | Demander comment cela fonctionne | « Expliquez-moi le processus de revue d'accès » | La plus faible — seule, jamais suffisante |
| **Observation** | Regarder faire | Observer un analyste trier une alerte | Ponctuelle seulement |
| **Inspection** | Examiner les enregistrements/preuves | Revoir les enregistrements signés de revue d'accès sur 4 trimestres | Forte, reproductible |
| **Ré-exécution** | Refaire la mesure de façon indépendante | Relancer la requête d'accès et comparer à ce qu'a approuvé le propriétaire | La plus forte |

:::tip L'entretien est un point de départ, jamais une conclusion
« On m'a dit qu'ils le font » n'est pas la preuve qu'une mesure fonctionne. Corroborez l'entretien par l'inspection ou la ré-exécution. Un échec d'audit fréquent est de se fier à un entretien assuré sans jamais demander à *voir* les enregistrements.
:::

### Échantillonnage

Vous testez rarement chaque occurrence. Si une revue d'accès a lieu chaque trimestre, vous inspectez les quatre ; si une mesure s'exécute des milliers de fois (p. ex. chaque déploiement de code exige une approbation), vous prenez un **échantillon représentatif** et le testez. L'échantillonnage doit être défendable : assez grand et choisi sans biais, afin qu'un échantillon propre appuie réellement une conclusion sur toute la période.

## Les preuves et leur qualité

Une **preuve** est l'enregistrement qui permet à quelqu'un *autre que la personne ayant exécuté la mesure* de conclure qu'elle a fonctionné. Une bonne preuve est :

- **Suffisante** — en quantité assez grande pour appuyer la conclusion.
- **Pertinente** — elle se rapporte réellement à l'objectif de la mesure.
- **Fiable** — issue d'une source digne de confiance ; les journaux générés par le système valent mieux qu'un résumé saisi à la main.
- **Opportune** — elle couvre la période examinée, avec des dates.

```text
 Preuve faible :  « Les accès sont revus régulièrement. »  (une affirmation, sans preuve)
 Preuve forte :  une liste d'accès exportée par trimestre, chacune annotée de
                 l'approbateur, de la date et des retraits effectués — quatre
                 trimestres, générée par le système, validée par le propriétaire.
```

La « mesure productrice de preuves » évoquée plus haut paie ici : si la mesure émet un enregistrement daté et généré par le système à chaque exécution, la collecte de preuves est quasi gratuite. Si prouver une mesure exige une course aux captures d'écran une fois par an, la mesure est mal conçue pour l'audit.

## Constats et remédiation

Lorsqu'une mesure est absente, mal conçue ou ne fonctionne pas, vous ouvrez un **constat**. Un constat utile a une structure constante :

| Élément | Finalité |
|---|---|
| **Condition** | Ce que vous avez trouvé (le fait) |
| **Critère** | Ce qui devrait être vrai (politique, norme, l'objectif de la mesure) |
| **Cause** | Pourquoi l'écart existe (cause racine, pas seulement le symptôme) |
| **Conséquence / risque** | Ce qui pourrait en résulter |
| **Recommandation** | Quoi faire à ce sujet |

> **Constat (exemple) :** *Condition* — les comptes administrateurs ont été exclus des revues d'accès des T2 et T3. *Critère* — la norme de contrôle d'accès exige la revue trimestrielle de tous les comptes privilégiés. *Cause* — le script de revue filtre le groupe admin. *Conséquence* — un accès admin non autorisé ou obsolète pourrait persister sans détection, risque élevé pour les données clients (R-001). *Recommandation* — inclure tous les comptes privilégiés et refaire la revue T2–T3 sous 30 jours.

Les constats sont ensuite suivis jusqu'à la **clôture** : chacun reçoit une gravité, un propriétaire, une échéance et un statut, et la remédiation consiste à corriger la **cause racine**, pas seulement l'occurrence. C'est la boucle d'action corrective (article 10 de la 27001) sous forme opérationnelle. Un constat n'est pas clos tant que la correction n'est pas en place *et prouvée* — re-testée, non simplement promise.

:::warning Ne clôturez pas un constat sur une promesse
« Nous allons corriger » est un plan, pas une clôture. Un constat ne se clôt que lorsque vous pouvez prouver la mesure corrigée en fonctionnement — sinon l'audit de l'an prochain rouvrira le même écart. Suivez les constats ouverts sur un registre de remédiation, avec échéances et propriétaires, exactement comme un registre des risques.
:::

## Surveillance continue des mesures

Un test annuel laisse de longues zones aveugles. La **surveillance continue des mesures (CCM)** utilise l'automatisation pour vérifier les mesures bien plus souvent — idéalement en continu :

- Une tâche quotidienne qui signale tout compte privilégié non couvert par la dernière revue.
- Une alerte lorsque la MFA est désactivée sur un compte.
- Un tableau de bord du pourcentage de systèmes corrigés dans le délai (SLA).

La CCM transforme le test ponctuel en signal continu et renvoie aux **indicateurs clés de risque** du module risque : un ICR qui évolue dans le mauvais sens est souvent le premier signe qu'une mesure a discrètement cessé de fonctionner. Les programmes matures testent en continu les mesures à haut risque et réservent l'effort d'audit manuel aux domaines exigeant du jugement.

### Dossiers de travail

Quoi que vous testiez, les **dossiers de travail d'audit** le consignent : ce qui a été testé, la technique employée, l'échantillon, les preuves examinées et la conclusion. De bons dossiers de travail permettent à un relecteur indépendant d'aboutir à la même conclusion à partir des mêmes preuves — l'équivalent d'un résultat reproductible en audit. Ce sont aussi ceux qu'un auditeur de certification ou l'évaluateur d'un client demandera à voir.

## Erreurs courantes

- **Tester l'opération avant la conception.** Échantillonner une mesure qui ne pourrait jamais atteindre son objectif gaspille l'effort — vérifiez d'abord la conception.
- **Des « tests » par entretien seul.** Croire un entretien sans inspecter les enregistrements.
- **Une preuve qui est une affirmation, non un enregistrement.** « Nous le faisons régulièrement » ne prouve rien.
- **Clôturer des constats sans re-test.** Une correction promise n'est pas une mesure corrigée.
- **Le propriétaire de la mesure auditant sa propre mesure.** Brise l'indépendance ; l'auto-évaluation est acceptable, mais l'assurance doit être indépendante.

## Implications pour la sécurité

- Concevez les mesures pour qu'elles soient précises, détenues et productrices de preuves avant de vous soucier de les tester.
- Établissez toujours l'efficacité de conception d'abord, puis l'efficacité opérationnelle sur la période.
- Privilégiez des preuves fiables, générées par le système et datées ; corroborez chaque entretien.
- Menez les constats jusqu'à une clôture prouvée à la cause racine, et surveillez en continu les mesures à haut risque.

## Vérification des connaissances

:::check Questions
1. Quelle est la différence entre efficacité de conception et efficacité opérationnelle, et laquelle teste-t-on d'abord ?
2. Classez de la plus faible à la plus forte comme preuve : ré-exécution, entretien, inspection.
3. Dans un constat, quelle est la différence entre la « cause » et la « conséquence » ?
4. Pourquoi le propriétaire d'une mesure ne peut-il pas fournir une assurance indépendante sur sa propre mesure ?
:::

:::solution Réponses
1. L'**efficacité de conception** demande si la mesure *réduirait* le risque si elle fonctionnait comme décrit ; l'**efficacité opérationnelle** demande si elle *a* fonctionné ainsi sur la période. Testez **la conception d'abord** — aucun intérêt à échantillonner l'opération d'une mesure qui ne pourrait jamais atteindre son objectif.
2. De la plus faible à la plus forte : **entretien → inspection → ré-exécution.**
3. La **cause** est la raison racine de l'écart (p. ex. le script de revue filtre les admins) ; la **conséquence** est ce qui pourrait en résulter (p. ex. un accès admin obsolète persiste, risquant les données clients). La cause explique le *pourquoi* ; la conséquence explique le *et alors*.
4. Parce que l'assurance doit être indépendante (le modèle des trois lignes) : si la personne qui exécute une mesure atteste aussi qu'elle fonctionne, « j'ai contrôlé mon propre travail et tout va bien » est la seule réponse possible. Le propriétaire peut s'auto-évaluer, mais l'assurance indépendante doit venir d'un tiers (l'audit interne).
:::

## Exercice

Prenez une mesure de votre déclaration d'applicabilité (p. ex. « revue trimestrielle des accès privilégiés ») :

1. Énoncez son **objectif de mesure** et le **risque** qu'elle réduit.
2. Décrivez comment vous testeriez sa **conception** puis son **efficacité opérationnelle**, en nommant la **technique** et les **preuves** que vous collecteriez pour chacune.
3. Inventez un écart plausible et rédigez un **constat** complet (condition, critère, cause, conséquence, recommandation).
4. Décrivez une vérification de **surveillance continue** qui détecterait la défaillance de cette mesure avant le prochain audit annuel.

:::solution Réponse type (abrégée)
1. *Objectif :* seul le personnel autorisé détient des accès privilégiés. *Risque :* accès admin obsolète/non autorisé menant à une compromission des données (p. ex. R-001).
2. *Conception :* **inspecter** la procédure de revue — couvre-t-elle *tous* les comptes privilégiés et exige-t-elle le retrait des accès inutiles ? *Opération :* **inspecter/ré-exécuter** — obtenir les quatre revues trimestrielles, confirmer que chacune a été réalisée, signée et datée, et relancer la requête d'accès pour un trimestre afin de confirmer que la liste correspond à ce qui a été approuvé.
3. *Constat :* Condition — les comptes de service ont été omis des quatre revues. Critère — la norme exige la revue trimestrielle de chaque compte privilégié. Cause — le périmètre de revue a été défini comme « utilisateurs nommés » seulement. Conséquence — des comptes de service surprivilégiés pourraient être détournés sans détection (risque élevé pour les données clients). Recommandation — redéfinir le périmètre pour inclure les comptes de service et réaliser une revue de rattrapage sous 30 jours.
4. *Vérification continue :* une tâche automatisée quotidienne qui liste chaque compte des groupes privilégiés et signale ceux absents de la dernière revue achevée — en alertant immédiatement le propriétaire plutôt qu'en fin d'année.
:::

## Résumé et étapes suivantes

- Une mesure dans la DdA est une affirmation ; l'audit prouve qu'elle est bien conçue, qu'elle fonctionne et qu'elle est étayée par des preuves.
- Testez **la conception d'abord**, puis **l'efficacité opérationnelle** sur la période.
- Utilisez entretien, observation, inspection et ré-exécution — jamais l'entretien seul — et collectez des preuves suffisantes, pertinentes, fiables et opportunes.
- Rédigez des constats structurés (condition, critère, cause, conséquence, recommandation) et menez-les jusqu'à une clôture **prouvée** à la cause racine.
- La **surveillance continue des mesures** et de bons dossiers de travail gardent l'assurance vivante et reproductible.

Les modules suivants appliquent cette discipline à des domaines précis — vie privée et protection des données, continuité d'activité et risques tiers — chacun ajoutant des mesures que votre DdA consigne et que les méthodes de cette leçon vérifient.

===assignment===
Choisissez quatre mesures de votre déclaration d'applicabilité couvrant au moins deux fonctions de mesure (préventive, détective, corrective). Pour chacune, produisez un bref **plan de test** : l'objectif de la mesure et le risque, comment vous testeriez l'efficacité de conception et opérationnelle, la ou les techniques et les preuves, et si vous l'inclurez dans la surveillance continue. Rédigez ensuite **deux constats complets** (selon la structure en cinq éléments) pour des écarts plausibles, chacun avec une gravité, un propriétaire et une cible de remédiation à 30/60/90 jours.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Les plans de test doivent séparer l'efficacité de conception de l'efficacité opérationnelle et préciser des preuves fiables ; les constats doivent identifier une véritable cause racine et mener à une clôture prouvée.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- ISO/IEC 27002:2022, *Mesures de sécurité de l'information* — https://www.iso.org/standard/75652.html
- ISO/IEC 27001:2022, articles 9 (évaluation des performances) et 10 (amélioration) — https://www.iso.org/standard/27001
- ISACA, *Information Systems Auditing: Tools and Techniques* (preuves et tests d'audit) — https://www.isaca.org/resources
- AICPA, *SOC 2 Trust Services Criteria* (efficacité de conception et opérationnelle des mesures) — https://www.aicpa-cima.com/topic/audit-assurance/audit-and-assurance-greater-than-soc-2
- NIST SP 800-53A Rev. 5, *Assessing Security and Privacy Controls* — https://csrc.nist.gov/pubs/sp/800/53/a/r5/final
