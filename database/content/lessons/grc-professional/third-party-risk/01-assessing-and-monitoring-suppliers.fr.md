---
title: Gestion des risques tiers — Évaluer et surveiller les fournisseurs
chapter: Gestion des risques tiers
summary: Gérer le risque qui transite par les fournisseurs dont vous dépendez — les inventorier, les hiérarchiser par criticité, mener une diligence raisonnable, intégrer la sécurité aux contrats et les surveiller sur toute la relation, de l'intégration à la sortie.
duration: 55
level: intermediate
preview: true
status: published
objectives:
- Expliquer pourquoi les tiers concentrent le risque et pourquoi la responsabilité vous reste
- Inventorier et hiérarchiser les fournisseurs par criticité et accès aux données
- Mener une diligence raisonnable proportionnée à l'aide de questionnaires de sécurité et de preuves
- Intégrer des exigences de sécurité, de vie privée et de continuité dans les contrats
- Surveiller les fournisseurs tout au long du cycle de vie et les sortir en toute sécurité
prerequisites:
- Continuité d'activité et résilience — BIA, RTO et RPO
---

## Introduction

Presque rien de ce que fait une organisation moderne n'est entièrement interne. Paie, messagerie, hébergement, paiements, analyses, outils de support — chacun est un **tiers** qui traite vos données ou exploite une partie de votre activité. Chacun est aussi une voie de risque : une violation chez un fournisseur est aussi votre violation, et une panne chez un fournisseur peut arrêter votre fonction critique aussi sûrement que la défaillance de vos propres serveurs.

Cette leçon traite les fournisseurs comme des risques à gouverner, avec le même moteur que le reste du parcours — identifier, évaluer, traiter, surveiller — adapté à des relations que vous influencez par contrat plutôt que de contrôler directement. Elle relie des fils déjà rencontrés : la distinction responsable/sous-traitant de la vie privée, les dépendances de continuité du module précédent, et le registre des risques tout du long.

## Pourquoi les tiers concentrent le risque

- **Vous déléguez le travail, pas la responsabilité.** Comme l'a établi le module vie privée, un responsable du traitement reste comptable des données personnelles même lorsqu'un sous-traitant les manipule. Externaliser une fonction n'externalise pas la responsabilité du risque.
- **Leur faiblesse devient votre exposition.** Un attaquant qui ne peut vous atteindre directement peut compromettre un fournisseur ayant accès à vous — le vecteur d'attaque de la chaîne d'approvisionnement du parcours SOC.
- **La concentration démultiplie l'impact.** Lorsque de nombreuses organisations dépendent du même prestataire cloud, de paiement ou logiciel, un seul incident fournisseur devient l'incident de toutes à la fois.
- **Vous avez moins de visibilité.** Vous ne pouvez auditer l'intérieur d'un fournisseur comme vous auditez le vôtre ; vous devez vous appuyer sur des preuves, des contrats et de la surveillance plutôt que sur un contrôle direct.

:::note Le problème du quatrième tiers
Vos fournisseurs ont des fournisseurs. Le prestataire cloud que vous avez évalué peut lui-même dépendre d'un sous-traitant ultérieur que vous n'avez jamais évalué — un *quatrième tiers*. Vous ne pouvez évaluer tout le monde, mais les fournisseurs critiques devraient divulguer leurs sous-traitants clés et être contractuellement tenus de leur imposer des standards équivalents, afin que la chaîne de responsabilité ne disparaisse pas simplement un maillon plus loin.
:::

## Inventaire et hiérarchisation

Vous ne pouvez gérer des fournisseurs que vous n'avez pas listés. Commencez par un **inventaire des fournisseurs** : qui ils sont, quel service ils fournissent, **à quelles données ils accèdent**, et **quelles de vos fonctions en dépendent**. Puis **hiérarchisez-les** par criticité — car vous ne pouvez et ne devez pas évaluer un fournisseur de fournitures de bureau aussi durement que votre prestataire de paiement.

| Niveau | Caractéristiques typiques | Traitement |
|---|---|---|
| **Critique** | Accède à des données sensibles/personnelles, ou dépendance de continuité d'une fonction critique | Diligence approfondie, clauses contractuelles fortes, surveillance continue |
| **Important** | Un certain accès aux données ou une dépendance opérationnelle modérée | Questionnaire et contrat standard, revue périodique |
| **Faible** | Pas de données sensibles, facilement remplaçable, peu d'impact opérationnel | Léger ; contrôles de base |

La hiérarchisation est de nouveau une priorisation par le risque : elle est guidée par la **sensibilité des données** (de la classification du module vie privée) et la **dépendance opérationnelle** (du BIA). Un fournisseur qui détient à la fois des données personnelles *et* soutient une fonction critique est sans ambiguïté critique.

## Diligence raisonnable

Avant l'intégration — et périodiquement ensuite — évaluez le fournisseur **proportionnellement à son niveau**. Instruments courants :

- **Questionnaires de sécurité.** Ensembles de questions normalisés (p. ex. un questionnaire de type SIG) couvrant les mesures du fournisseur : contrôle d'accès, chiffrement, correctifs, réponse à incident, continuité d'activité, sous-traitants.
- **Assurance indépendante.** Plutôt que de croire les réponses auto-déclarées, demandez des *preuves* : un certificat ISO/IEC 27001 (et sa **déclaration d'applicabilité / son périmètre** — le périmètre couvre-t-il réellement le service que vous achetez ?), un rapport SOC 2, des synthèses de tests d'intrusion récents.
- **Vérifications vie privée et juridiques.** Lorsque le fournisseur traite des données personnelles : base légale, localisation des données, sous-traitants, et clauses de protection des données (en lien avec les transferts internationaux du module vie privée).
- **Viabilité financière et opérationnelle.** Un fournisseur qui risque de cesser son activité est un risque de continuité, quelle que soit sa sécurité.

:::tip Un certificat est une affirmation tant que vous n'en avez pas lu le périmètre
« Nous sommes certifiés ISO 27001 » ne signifie pas grand-chose tant que vous ne vérifiez pas *ce qui* est dans le périmètre. Un certificat ne couvrant que le siège social n'assure pas le service cloud que vous achetez. Traitez les attestations fournisseurs exactement comme vos propres mesures (module des mesures) : une affirmation à vérifier par la preuve, non à accepter de confiance. Demandez le certificat, le périmètre et la déclaration d'applicabilité.
:::

Consignez le résultat comme un **score de risque** pour le fournisseur et alimentez les constats importants dans votre registre des risques avec un propriétaire et un traitement — une réponse faible est un risque à atténuer (exiger une correction), transférer (responsabilité contractuelle), éviter (choisir un autre fournisseur) ou accepter sciemment.

## Contrats : là où les mesures prennent force

Avec un tiers, vous ne pouvez imposer les mesures directement ; le **contrat** est donc votre mesure principale. Clauses pertinentes pour la sécurité à rechercher, adaptées au niveau :

- **Exigences de sécurité** — les mesures que le fournisseur doit maintenir (p. ex. chiffrement, MFA, délais de correctifs).
- **Notification de violation** — le fournisseur doit vous informer d'une violation de sécurité ou de données personnelles dans un délai défini et court, pour que vous puissiez tenir *votre* horloge réglementaire (module vie privée).
- **Droit d'audit / d'évaluation** — la capacité de demander des preuves, des évaluations ou (pour les fournisseurs critiques) un audit.
- **Maîtrise des sous-traitants** — divulgation des sous-traitants et obligation de les lier à des clauses équivalentes.
- **Traitement et retour/suppression des données** — où les données peuvent être stockées, et leur retour sécurisé ou leur destruction à la fin (sortie, ci-dessous).
- **Continuité et sortie** — engagements de disponibilité (SLA) et appui à une sortie ordonnée.

Une clause contractuelle n'aide que si quelqu'un vérifie qu'elle est respectée — c'est le rôle de la surveillance.

## Surveillance sur tout le cycle de vie

Le risque tiers n'est pas une barrière ponctuelle à l'intégration ; il court sur toute la relation.

```text
 INTÉGRER ──► EXPLOITER / SURVEILLER ──────────► SORTIR
    │                  │                            │
 diligence       ré-évaluation périodique,    retourner/supprimer les
 + contrat       veille des incidents,        données, révoquer les accès,
 + niveau        suivi SLA & ICR              confirmer la destruction
```

- **Intégrer** — hiérarchiser, évaluer, contractualiser, et n'accorder que l'accès dont le service a besoin (le moindre privilège s'applique aussi aux fournisseurs).
- **Exploiter / surveiller** — ré-évaluer à une cadence adaptée au niveau ; suivre les **indicateurs clés de risque** du fournisseur (SLA manqués, incidents divulgués, certificats expirés, nouvelles d'une violation) ; revérifier quand le service ou le périmètre de données change.
- **Sortir** — l'étape la plus oubliée. À la sortie, **révoquez les accès**, assurez-vous que les données sont retournées ou détruites de façon sécurisée (avec confirmation), et retirez le fournisseur des systèmes qui lui font encore confiance. Un fournisseur résilié dont la clé d'API fonctionne encore est un risque actif.

:::warning La sortie est une mesure de sécurité, pas une pensée après coup
Des violations remontent régulièrement à un ancien fournisseur dont les identifiants, l'accès VPN ou les clés d'API n'ont jamais été révoqués. Traitez la sortie avec la même rigueur que l'intégration : une liste de contrôle qui révoque chaque accès, confirme la destruction des données par écrit et boucle la boucle. « Nous avons cessé de les utiliser » n'est pas la même chose que « ils ne peuvent plus atteindre nos données ».
:::

## Erreurs courantes

- **Pas d'inventaire.** On ne peut gérer ou hiérarchiser des fournisseurs non listés — et les fournisseurs fantômes souscrits par des équipes isolées sont ceux qui mordent.
- **Évaluer tout le monde pareil.** Soit on épuise l'équipe sur des fournisseurs triviaux, soit on sous-évalue les critiques. Hiérarchisez d'abord.
- **Se fier au badge, pas au périmètre.** Une attestation ISO/SOC dont le périmètre exclut votre service n'assure rien.
- **Une sécurité qui ne vit que dans le questionnaire.** Si les exigences ne sont pas au contrat, vous ne pouvez les imposer.
- **Une fois pour toutes.** Un fournisseur évalué seulement à l'intégration peut se dégrader des années sans être remarqué.
- **Oublier la sortie.** Des accès persistants et des données non détruites font d'un ancien fournisseur un risque permanent.

## Implications pour la sécurité

- Tenez un inventaire vivant des fournisseurs et hiérarchisez-le par sensibilité des données et dépendance opérationnelle.
- Rendez la diligence proportionnée ; vérifiez les attestations en lisant le périmètre et les preuves, pas le badge.
- Mettez les exigences de sécurité, de notification de violation, d'audit, de sous-traitance et de sortie dans le contrat — c'est ainsi que les mesures prennent force.
- Surveillez sur tout le cycle de vie et sortez délibérément : révoquez les accès et confirmez la destruction des données.

## Vérification des connaissances

:::check Questions
1. Si vous externalisez une fonction à un fournisseur, qu'advient-il de votre responsabilité comptable du risque associé ?
2. Quels deux facteurs guident le plus la façon dont vous hiérarchisez un fournisseur ?
3. Pourquoi lire le périmètre d'un certificat est-il plus important que de constater que le fournisseur « est certifié » ?
4. Nommez deux actions de sortie qui réduisent le risque tiers lorsqu'une relation fournisseur prend fin.
:::

:::solution Réponses
1. Elle **vous reste**. Vous pouvez déléguer le travail et une partie de la responsabilité opérationnelle, mais la responsabilité comptable du risque (et, pour les données personnelles, la responsabilité du responsable du traitement) demeure la vôtre.
2. La **sensibilité des données** (quelles données, et leur sensibilité, le fournisseur consulte) et la **dépendance opérationnelle** (la criticité de la fonction qu'il soutient, issue du BIA).
3. Parce qu'un certificat n'assure que ce qui est **dans le périmètre** ; un certificat ne couvrant, disons, que le siège n'assure pas le service précis que vous achetez. Le périmètre (et la déclaration d'applicabilité) indique si l'attestation est pertinente.
4. Deux parmi : **révoquer tous les accès** (comptes, VPN, clés d'API) ; **s'assurer que les données sont retournées ou détruites de façon sécurisée, avec confirmation écrite** ; **retirer le fournisseur des systèmes qui lui font encore confiance** ; mettre à jour l'inventaire/le registre des risques.
:::

## Exercice

Pour une organisation utilisant un prestataire d'hébergement cloud, un SaaS de paie et un outil d'analyse marketing :

1. **Hiérarchisez** chaque fournisseur (critique / important / faible) et justifiez à partir de l'accès aux données et de la dépendance opérationnelle.
2. Pour le fournisseur **critique**, listez quatre éléments de diligence que vous exigeriez, dont une preuve indépendante.
3. Rédigez trois **clauses contractuelles** sur lesquelles vous insisteriez pour ce fournisseur.
4. Décrivez les étapes de **sortie** si vous le remplaciez plus tard.

:::solution Réponse type (abrégée)
1. Hébergement cloud → **critique** (héberge systèmes et données ; dépendance de continuité). SaaS de paie → **critique/important** (détient des données personnelles sensibles, mais usage périodique). Analyse marketing → **important ou faible** selon qu'il reçoit ou non des données personnelles ; si seulement agrégées/pseudonymes, faible.
2. Pour l'hébergement cloud : un questionnaire de sécurité renseigné ; le **certificat ISO 27001 avec son périmètre et sa déclaration d'applicabilité** (preuve indépendante) ou un rapport SOC 2 ; la divulgation de la localisation des données et des sous-traitants ; et la preuve d'une capacité de continuité/reprise testée.
3. (a) Maintenir des mesures de sécurité déclarées (chiffrement, MFA, délais de correctifs) ; (b) nous notifier toute violation dans un délai court défini ; (c) soutenir le retour/la suppression sécurisés des données et une sortie ordonnée, avec des SLA de disponibilité.
4. **Sortie :** migrer les données ; révoquer tous les comptes, clés et accès réseau ; obtenir une confirmation écrite que nos données ont été détruites de façon sécurisée ; retirer le prestataire de tout système qui lui faisait confiance ; et mettre à jour l'inventaire et le registre des risques.
:::

## Résumé et étapes suivantes

- Les tiers concentrent le risque car vous déléguez le travail mais gardez la responsabilité, et leurs faiblesses deviennent votre exposition.
- Inventoriez les fournisseurs et **hiérarchisez-les** par sensibilité des données et dépendance opérationnelle ; évaluez chacun proportionnellement.
- Vérifiez les attestations par le périmètre et la preuve, et mettez les clauses de sécurité, de notification de violation, d'audit, de sous-traitance et de sortie dans le **contrat**.
- Gérez tout le cycle de vie — intégrer, surveiller, et **sortir** en révoquant les accès et en confirmant la destruction des données.

Les modules restants — **Outils et documentation GRC** et le **Projet de synthèse GRC** — rassemblent tout le parcours : les registres, matrices et formulaires qui rendent tout cela auditable, et une évaluation organisationnelle simulée de bout en bout.

===assignment===
Produisez une **fiche de risque tiers** d'une page pour une organisation de votre choix : un inventaire d'au moins quatre fournisseurs avec service, données consultées, fonction dépendante et niveau attribué ; une liste de diligence proportionnée pour votre fournisseur le plus critique (incluant au moins une preuve indépendante et une note sur la vérification de son périmètre) ; trois clauses contractuelles que vous exigeriez ; et une liste de contrôle de sortie. Rattachez au moins un fournisseur à un risque de votre registre des risques, avec un propriétaire et un traitement.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- La hiérarchisation doit être justifiée par la sensibilité des données et la dépendance opérationnelle ; la diligence doit vérifier la preuve et le périmètre, non se contenter d'un badge ; et la liste de sortie doit révoquer les accès et confirmer la destruction des données.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- ISO/IEC 27036 (série), *Sécurité de l'information pour la relation avec le fournisseur* — https://www.iso.org/standard/59648.html
- NIST SP 800-161 Rev. 1, *Cybersecurity Supply Chain Risk Management Practices* — https://csrc.nist.gov/pubs/sp/800/161/r1/final
- NIST Cybersecurity Framework 2.0, *Gouverner — Gestion des risques de la chaîne d'approvisionnement en cybersécurité (GV.SC)* — https://www.nist.gov/cyberframework
- Shared Assessments, *Questionnaire SIG (Standardized Information Gathering)* — https://sharedassessments.org/sig/
