---
title: ISO/IEC 27001 — Le SMSI et la déclaration d'applicabilité
chapter: ISO/IEC 27001
summary: Comprendre ce qu'est un système de management de la sécurité de l'information, comment le cycle Planifier-Déployer-Contrôler-Agir le fait vivre, et comment l'appréciation des risques conduit à sélectionner les mesures de l'Annexe A et à les documenter — de façon défendable — dans une déclaration d'applicabilité.
duration: 55
level: intermediate
preview: true
status: published
objectives:
- Expliquer ce qu'est un SMSI et en quoi l'ISO/IEC 27001 diffère d'un référentiel comme le CSF
- Décrire le cycle Planifier-Déployer-Contrôler-Agir et la structure en articles 4 à 10 de la norme
- Relier l'appréciation et le traitement des risques à la sélection des mesures de l'Annexe A
- Expliquer la finalité de la déclaration d'applicabilité et ce qui la rend défendable
- Décrire l'audit interne, la revue de direction et l'amélioration continue
prerequisites:
- Appréciation des risques et registre des risques
- Le NIST Cybersecurity Framework 2.0 — Fonctions et profils
---

## Introduction

La leçon précédente utilisait le NIST Cybersecurity Framework pour *structurer* un programme autour de résultats. L'**ISO/IEC 27001** va un pas plus loin : elle définit un **système de management** complet et auditable pour la sécurité de l'information — et, contrairement au CSF, vous pouvez être **certifié** à son égard par un organisme accrédité. Ce certificat est ce que de nombreux clients et régulateurs demandent comme preuve que la sécurité est pilotée, et non improvisée.

Cette leçon explique le système de management, le cycle qui le maintient vivant, et le document que les auditeurs examinent le plus : la **déclaration d'applicabilité**.

## Du référentiel au système de management

Un **système de management de la sécurité de l'information (SMSI)** est l'ensemble des politiques, processus, rôles et enregistrements par lesquels une organisation pilote la sécurité de l'information *comme un système continu*, et non comme un projet ponctuel. L'ISO/IEC 27001:2022 spécifie les exigences pour en construire un, l'exploiter et l'améliorer.

| | NIST CSF 2.0 | ISO/IEC 27001 |
|---|---|---|
| Nature | Référentiel de résultats | Norme de système de management |
| Certifiable ? | Non | **Oui**, par un organisme accrédité |
| Objet | *À quoi* ressemble le bon | *Comment* piloter la sécurité comme un système |
| Mesures | Références informatives | Sélectionnées par le risque, l'Annexe A servant d'ensemble de référence |
| Livrable | Profils, feuille de route | Un SMSI en exploitation + un certificat |

Ils sont complémentaires : le CSF aide à *voir et prioriser* ; la 27001 donne le *système et les preuves*. Les deux partent du même moteur que vous connaissez déjà — l'appréciation des risques.

:::note « Certification » désigne le système, pas le produit
Être certifié ISO/IEC 27001 signifie qu'un auditeur accrédité a examiné votre *système de management* et l'a jugé conforme à la norme et opérant. Cela ne certifie **pas** qu'un produit est sûr, et ce n'est pas une accréditation gouvernementale d'individus. Décrivez-le précisément : « notre SMSI est certifié ISO/IEC 27001 », jamais « nous sommes certifiés sûrs par l'État ».
:::

## Le cycle Planifier-Déployer-Contrôler-Agir

Un SMSI repose sur l'amélioration continue, exprimée par **Planifier-Déployer-Contrôler-Agir (PDCA)** :

```text
      ┌────────► PLANIFIER ───────┐
      │  contexte, périmètre,     │
      │  risques, objectifs,      ▼
     AGIR                      DÉPLOYER
  améliorer,                 mettre en
  actions                    œuvre mesures
  correctives                & processus
      ▲                          │
      │   CONTRÔLER ◄─────────────┘
      └── surveiller, auditer,
          revue de direction
```

- **Planifier** — comprendre le contexte, fixer le périmètre, apprécier le risque, choisir les traitements et les objectifs.
- **Déployer** — mettre en œuvre les mesures et exploiter les processus.
- **Contrôler** — surveiller, mesurer, auditer en interne et réaliser une revue au niveau de la direction.
- **Agir** — corriger ce qui ne fonctionne pas et améliorer.

Le cycle ne s'arrête jamais ; c'est ce qui fait de la sécurité une fonction *pilotée* plutôt qu'une course annuelle.

## Comment la norme est organisée

L'ISO/IEC 27001 comporte des **articles numérotés 4 à 10** (les exigences certifiables) plus l'**Annexe A** (une liste de référence de mesures).

| Article | Thème | PDCA |
|---|---|---|
| 4 Contexte de l'organisation | Enjeux internes/externes, parties intéressées, **périmètre du SMSI** | Planifier |
| 5 Leadership | Engagement de la direction, politique, rôles | Planifier |
| 6 Planification | **Appréciation et traitement des risques**, objectifs, la DdA | Planifier |
| 7 Support | Ressources, compétences, sensibilisation, informations documentées | Déployer |
| 8 Fonctionnement | Exploiter les processus ; mener l'appréciation/traitement des risques | Déployer |
| 9 Évaluation des performances | Surveillance, **audit interne**, **revue de direction** | Contrôler |
| 10 Amélioration | Non-conformité, action corrective, amélioration continue | Agir |

Deux articles méritent d'être soulignés dès maintenant.

**Article 4 — périmètre.** Vous devez définir exactement ce que couvre le SMSI : quelles parties de l'organisation, quels sites, quels systèmes et services. Le périmètre est une décision délibérée — trop étroit, le certificat n'a aucun sens pour les clients ; trop large, il est ingérable. Tout le reste en dépend.

**Article 5 — leadership.** La direction doit s'approprier le SMSI de façon démontrable : approuver la politique, attribuer les rôles, fournir les ressources et donner la direction. C'est la gouvernance du module 1 rendue obligatoire — la norme n'acceptera pas une sécurité que la direction se contente de tolérer.

## Appréciation des risques → sélection des mesures → la DdA

L'article 6 est l'endroit où le moteur du risque, vu au module de gestion des risques, pilote tout :

```text
 APPRÉCIATION ──► TRAITEMENT ──► SÉLECTION DES ──► DÉCLARATION
 DES RISQUES      DES RISQUES    MESURES            D'APPLICABILITÉ
 (identifier,     (choisir       (ce qui réduit
  coter)           l'option)      le risque)
```

1. **Apprécier le risque** selon votre méthode définie (vraisemblance × impact, comme vous l'avez appris).
2. **Décider du traitement** de chaque risque (atténuer, transférer, éviter, accepter).
3. Pour les risques que vous atténuez, **sélectionner les mesures** qui les réduisent réellement.
4. Comparer vos mesures sélectionnées à l'**Annexe A** — un catalogue de 93 mesures dans l'édition 2022, regroupées en quatre thèmes : **organisationnelles, liées aux personnes, physiques, technologiques**.
5. Consigner le résultat dans la **déclaration d'applicabilité**.

L'Annexe A est une *liste de comparaison*, non un menu à adopter en bloc. La norme est claire : les mesures sont choisies parce que votre **appréciation des risques** les appelle — l'Annexe A garantit simplement que vous n'avez pas oublié une catégorie.

### La déclaration d'applicabilité (DdA / SoA)

La **DdA** est le document pivot d'un SMSI 27001. Pour **chaque** mesure de l'Annexe A, elle consigne :

- si la mesure est **applicable** (incluse) ou non ;
- la **justification** de son inclusion ou de son exclusion ;
- son **état de mise en œuvre** (et où elle est définie).

```text
 Mesure          Applicable ?  Justification                           État
 A.5.15 Contrôle  Oui          Risque R-002 (compromission de compte)  Mise en œuvre — politique
   d'accès                                                             d'accès + MFA
 A.8.24           Oui          Protège les données clients au repos    Mise en œuvre — chiffrement
   Cryptographie               (R-001)                                 BD + sauvegardes
 A.7.4 Surveill.  Non          Aucun local possédé ; 100 % cloud       Exclue avec justification
   physique
```

Ce qui rend une DdA **défendable** :

1. **Chaque inclusion remonte à un risque.** Un auditeur doit pouvoir relier une mesure au risque de votre registre qui la justifie.
2. **Chaque exclusion a une raison réelle et documentée.** « Non applicable car nous n'avons aucun local physique » est défendable ; exclure une mesure simplement parce qu'elle est gênante est un constat.
3. **Elle correspond à la réalité.** Une mesure marquée « mise en œuvre » doit réellement fonctionner, avec des preuves — l'honnêteté inhérent/résiduel du module risque s'applique directement ici.

:::warning La DdA est l'endroit où l'audit se gagne ou se perd
Les auditeurs utilisent la DdA comme carte : ils y choisissent des mesures et en demandent les preuves. Une DdA qui revendique des mesures que vous ne pouvez prouver, ou qui exclut des mesures sans justification réelle, se transforme aussitôt en non-conformités. Gardez-la honnête et à jour — mettez-la à jour dès que les risques ou les mesures changent, pas une fois par an.
:::

## Contrôler et Agir : audit, revue, amélioration

**Audit interne (article 9.2).** L'organisation audite son propre SMSI selon un programme planifié pour confirmer qu'il est conforme à la norme *et* à ses propres exigences, et qu'il est efficace. Comme l'exige le modèle des trois lignes du module 1, l'audit interne doit être **indépendant** des activités qu'il audite — un auditeur ne contrôle pas son propre travail.

**Revue de direction (article 9.3).** La direction passe formellement le SMSI en revue à intervalles planifiés : résultats d'audit, état des risques et des objectifs, incidents et possibilités d'amélioration. Cela boucle la gouvernance — la direction voit si le système fonctionne et oriente le changement.

**Amélioration (article 10).** Lorsqu'un élément n'est pas conforme — une mesure défaillante, un objectif manqué, un incident — vous ouvrez une **non-conformité**, prenez une **action corrective** (corriger le problème *et* sa cause racine), et l'enregistrez. L'amélioration continue n'est pas facultative ; la norme exige que le SMSI continue de progresser.

:::scenario Un fil, de bout en bout
Le risque `R-001` (base de données clients, pas de chiffrement au repos, inhérent 20) est traité par *atténuation*. Vous sélectionnez la mesure A.8.24 de l'Annexe A **(utilisation de la cryptographie)**, la marquez applicable dans la DdA avec R-001 comme justification, mettez en œuvre le chiffrement de la base et des sauvegardes, et consignez le risque résiduel à 6. Au prochain audit interne, l'auditeur ouvre la DdA, voit A.8.24 et en demande la preuve ; vous montrez la configuration du chiffrement et une procédure de gestion des clés. La revue de direction note que le risque est désormais dans l'appétence. Ce seul fil — risque → traitement → mesure → DdA → preuve → audit → revue — *est* le SMSI qui fonctionne.
:::

## Erreurs courantes

- **Adopter toute l'Annexe A sans tenir compte du risque.** Les mesures doivent être guidées par le risque ; une adoption en bloc gaspille l'effort et échoue tout de même si elles ne fonctionnent pas.
- **Une DdA qui ne correspond pas à la réalité.** Revendiquer des mesures que vous ne pouvez prouver est la voie la plus rapide vers une non-conformité majeure.
- **Un périmètre vague ou absent.** Un périmètre non défini rend tout le SMSI — et tout certificat — non fiable.
- **Un leadership de façade.** Sans appropriation réelle de la direction, l'article 5 n'est pas satisfait et le système se dégrade.
- **Une fois pour toutes.** Traiter la certification comme une ligne d'arrivée au lieu d'un cycle ; la norme exige l'amélioration continue.

## Implications pour la sécurité

- Laissez le risque guider la sélection des mesures, puis utilisez l'Annexe A pour vérifier qu'aucune catégorie n'a été oubliée.
- Gardez la DdA honnête et à jour ; c'est la carte de l'audit et la preuve d'un système piloté.
- Préservez l'indépendance de l'audit et tenez de vraies revues de direction — la gouvernance doit voir les preuves.
- Traitez les non-conformités comme un carburant d'amélioration : corrigez la cause racine, pas seulement le symptôme.

## Vérification des connaissances

:::check Questions
1. Quelle est la différence clé entre le NIST CSF et l'ISO/IEC 27001 en matière de certification ?
2. Dans le cycle PDCA, quelles activités relèvent de « Contrôler » ?
3. Quelles deux choses la déclaration d'applicabilité doit-elle consigner pour chaque mesure de l'Annexe A ?
4. Pourquoi l'audit interne doit-il être indépendant de l'activité auditée ?
:::

:::solution Réponses
1. Le CSF est un **référentiel** de résultats et n'est **pas certifiable** ; l'ISO/IEC 27001 spécifie un **système de management** à l'égard duquel une organisation **peut être certifiée** par un organisme accrédité.
2. **Surveillance et mesure, audit interne et revue de direction** (article 9, évaluation des performances).
3. Si la mesure est **applicable (incluse ou exclue)** et la **justification** de cette décision — et, en pratique, son état de mise en œuvre. (Applicabilité + justification sont les deux éléments essentiels.)
4. Pour que l'audit soit crédible : si les auditeurs contrôlaient leur propre travail, « nous nous sommes contrôlés et tout va bien » serait la seule réponse possible. L'indépendance (le modèle des trois lignes) est ce qui permet à l'audit interne de donner à la direction une évaluation honnête.
:::

## Exercice

En utilisant l'exemple de registre des risques du module de gestion des risques :

1. Choisissez **trois** risques que vous avez décidé d'*atténuer*.
2. Pour chacun, nommez un **thème de l'Annexe A** plausible (organisationnel, personnes, physique ou technologique) et une mesure qui le réduirait.
3. Rédigez les **lignes de DdA** correspondantes : mesure, applicable (oui/non), justification (référencez l'ID du risque) et état de mise en œuvre.
4. Ajoutez **une** mesure que vous marqueriez *non applicable*, avec une justification réelle.

:::solution Réponse type (abrégée)
1–3.
- R-001 (BD clients, pas de chiffrement) → **technologique**, *utilisation de la cryptographie (A.8.24)* → DdA : *Applicable ; justifiée par R-001 ; Mise en œuvre — chiffrement de la base et des sauvegardes avec gestion des clés documentée.*
- R-002 (pas de MFA sur la messagerie web) → **technologique/organisationnel**, *contrôle d'accès & gestion des identités* → DdA : *Applicable ; justifiée par R-002 ; Mise en œuvre — politique de contrôle d'accès et MFA imposée.*
- Un risque d'hameçonnage/personnes → **personnes**, *sensibilisation, formation et éducation à la sécurité de l'information* → DdA : *Applicable ; justifiée par le risque d'hameçonnage ; Mise en œuvre — formation à l'accueil et sensibilisation annuelle avec enregistrements de réalisation.*
4. *Non applicable :* une mesure physique telle que *la surveillance de la sécurité physique* → *Exclue ; l'organisation ne possède aucun local et est entièrement hébergée dans le cloud, le risque est donc traité par les mesures de responsabilité partagée du fournisseur cloud.*
:::

## Résumé et étapes suivantes

- L'ISO/IEC 27001 définit un **SMSI certifiable** — un système de pilotage de la sécurité, animé par le PDCA.
- Les articles 4 à 10 sont les exigences ; l'article 4 fixe le **périmètre** et l'article 5 impose le **leadership**.
- L'article 6 transforme l'**appréciation des risques** en **sélection de mesures**, comparées à l'**Annexe A** et consignées dans la **déclaration d'applicabilité**.
- Une DdA défendable relie chaque mesure incluse à un risque, justifie chaque exclusion et correspond à la réalité.
- **Audit interne, revue de direction et action corrective** gardent le SMSI honnête et en progrès.

Les modules suivants abordent la conception et le test des mesures elles-mêmes, la preuve de leur fonctionnement, et la gestion de la vie privée, de la continuité et des risques tiers — autant d'éléments qui alimentent la DdA que vous venez d'apprendre à construire.

===assignment===
Pour une organisation de votre choix, rédigez un **extrait de DdA** d'au moins huit mesures de l'Annexe A couvrant les quatre thèmes (organisationnel, personnes, physique, technologique). Pour chacune : l'applicabilité, une justification qui référence un risque (utilisez ou étendez votre registre des risques) et un état de mise en œuvre. Incluez au moins deux exclusions justifiées. Ajoutez un court paragraphe définissant le **périmètre du SMSI** retenu et expliquant comment le périmètre a influé sur les mesures applicables.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Chaque mesure incluse doit remonter à un risque, chaque exclusion doit avoir une raison réelle et documentée, et les états doivent être réalistes (aucune mesure revendiquée que vous ne pourriez prouver).
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- ISO/IEC 27001:2022, *Sécurité de l'information, cybersécurité et protection de la vie privée — Systèmes de management de la sécurité de l'information — Exigences* — https://www.iso.org/standard/27001
- ISO/IEC 27002:2022, *Mesures de sécurité de l'information* (recommandations sur les mesures de l'Annexe A) — https://www.iso.org/standard/75652.html
- ISO/IEC 27005:2022, *Préconisations pour la gestion des risques liés à la sécurité de l'information* — https://www.iso.org/standard/80585.html
- ISO, *ISO/IEC 27001 et normes associées — management de la sécurité de l'information* — https://www.iso.org/isoiec-27001-information-security.html
