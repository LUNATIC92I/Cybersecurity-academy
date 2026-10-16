---
title: Tri des alertes et escalade
chapter: Réponse à incident
summary: Maîtriser la compétence quotidienne centrale de l'analyste SOC — transformer un flot d'alertes en décisions : trier vrais et faux positifs, enrichir et fixer la gravité, escalader avec une passation claire, et réinjecter ce que l'on apprend dans de meilleures détections.
duration: 50
level: beginner
preview: true
status: published
objectives:
- Traiter une file d'alertes avec un entonnoir de tri reproductible
- Distinguer vrai positif, faux positif, bénin et doublon
- Enrichir une alerte et attribuer une gravité à partir de l'impact et de la confiance
- Rédiger une note d'escalade et de passation qu'un autre analyste peut exploiter sans vous
- Boucler la boucle pour que le tri améliore les détections et réduise les faux positifs
prerequisites:
- Le cycle de vie de la réponse à incident
---

## Introduction

La leçon sur le cycle de vie a montré la forme d'un incident entier. Mais la plupart de la journée d'un analyste SOC se passe *avant* qu'un incident soit confirmé : à traiter une file d'alertes, en majorité du bruit, pour trouver les rares qui comptent. Cette compétence — le **tri (triage)** — est ce qui distingue un SOC qui attrape tôt les intrusions d'un SOC qui se noie dans les alertes et manque la vraie.

Cette leçon est l'artisanat quotidien du niveau 1/2 : une manière reproductible de traiter la file, de décider chaque alerte, de fixer la gravité, et de passer le relais proprement quand quelque chose est réel — en utilisant la lecture de preuves bâtie dans les modules journaux et détection.

## L'entonnoir de tri

Les alertes arrivent plus vite qu'on ne peut les investiguer en profondeur ; le tri est donc un entonnoir : quelques secondes sur la plupart, quelques minutes sur certaines, et l'escalade des rares qui le méritent.

```text
        FILE D'ALERTES  (des centaines/jour)
              │  coup d'œil : est-ce manifestement du bruit ?
              ▼
        TRI INITIAL  ── faux positif / bénin / doublon ──► clore avec raison
              │  pas manifestement du bruit
              ▼
        ENRICHIR & ÉVALUER  ── bénin après inspection ──────► clore avec raison
              │  suspect et plausible
              ▼
        GRAVITÉ + ESCALADE  ──► passer à l'investigation approfondie / IR
```

Le but n'est pas d'investiguer pleinement chaque alerte — c'est impossible — mais de prendre une **décision défendable** sur chacune, vite, et de router les vraies vers l'investigation approfondie (les phases identification→confinement du cycle de vie) sans délai.

## Les quatre issues de tri

Chaque alerte que vous touchez se termine par l'une de quatre dispositions. Les nommer — et en donner la *raison* — est ce qui rend le tri auditable et réglable.

| Issue | Signification | Ce que vous consignez |
|---|---|---|
| **Vrai positif** | Activité réellement malveillante ou contraire à la politique | Ce qui a déclenché, la preuve, pourquoi c'est réel → escalader |
| **Faux positif** | La règle a déclenché mais l'activité n'est pas ce qu'elle détecte | Pourquoi c'est erroné → réinjecter pour régler la règle |
| **Vrai positif bénin** | L'activité est réelle mais autorisée/attendue | L'explication légitime (p. ex. une tâche admin connue) |
| **Doublon** | Même activité déjà couverte par une autre alerte/un autre cas | Lien vers l'original |

:::tip Consignez toujours la raison, pas seulement le verdict
« Clos – faux positif » sans raison n'apprend rien à personne et ne règle rien. « Faux positif : la rafale 4625 était le service de sauvegarde au mot de passe expiré, confirmé avec le propriétaire du serveur ; recommander d'exclure svc-backup de cette règle » transforme une décision de tri en réduction de bruit permanente. La raison est la valeur.
:::

## Enrichissement : transformer une alerte en contexte

Une alerte brute est un fragment. L'**enrichissement** ajoute le contexte qui permet de décider, en puisant exactement aux sources des modules précédents :

- **Qui / quoi :** l'utilisateur, l'hôte, le processus, et s'ils sont de grande valeur (un admin de domaine, un serveur de données réglementées).
- **D'où :** l'IP source — interne ou externe, connue-fiable ou signalée par le renseignement sur les menaces, géolocalisation vs là où l'utilisateur se trouve réellement.
- **Quand / à quelle fréquence :** vu pour la première fois, ou un schéma récurrent ? Pendant les heures ouvrées pour cet utilisateur ?
- **Corroboration :** une autre source de journal concorde-t-elle ? Une rafale d'échecs de connexion (Windows 4625 / `auth.log` Linux) *suivie d'une réussite* est bien plus forte que l'une ou l'autre seule — la corrélation pratiquée dans les leçons journaux.

L'enrichissement est là où une « connexion suspecte » devient soit « le directeur financier se connectant depuis sa ville habituelle, bénin », soit « une réussite depuis un nouveau pays juste après 40 échecs, sans MFA — escalader ».

## Gravité : impact × confiance

Tout vrai positif n'est pas une urgence. Fixez la gravité à partir de deux axes pour que les alertes les plus dangereuses et les plus certaines remontent d'abord :

- **Impact** — quelle gravité si c'est réel ? (Un contrôleur de domaine ou un hôte de données réglementées pèse bien plus qu'une VM de test — la classification des actifs du travail GRC/fondamentaux.)
- **Confiance** — à quel point êtes-vous sûr que c'est réel, au vu de la preuve et de la corroboration ?

```text
 Impact
 élevé │  moyen     ÉLEVÉ     CRITIQUE
       │  faible    moyen     ÉLEVÉ
 faible│  faible    faible    moyen
       └───────────────────────────────
          faible    moy      élevée   Confiance
```

Impact élevé *et* confiance élevée est l'endroit où l'on lâche le reste. Faible impact, faible confiance peut attendre ou se clore. La gravité commande l'horloge : une critique se traite maintenant et s'escalade aussitôt ; une faible peut se grouper. C'est la même logique de priorisation que le registre des risques, appliquée minute par minute.

## Escalade et note de passation

Quand une alerte mérite l'escalade, vous la passez à l'investigation approfondie ou à la réponse à incident. La passation réussit ou échoue sur **un seul artefact : une note que l'analyste suivant peut exploiter sans vous parler.** Une bonne note énonce :

1. **Ce qui a déclenché et quand** — l'alerte, l'heure, la règle.
2. **Pourquoi c'est réel / suspect** — la preuve trouvée, avec les lignes de journal ou identifiants précis.
3. **Le périmètre à ce stade** — quels utilisateurs, hôtes et IP sont concernés.
4. **La gravité et pourquoi** — votre appréciation impact × confiance.
5. **Les actions déjà prises** — ce que vous avez vérifié, et ce qui est déjà fait (p. ex. « compte désactivé en attente de revue »).
6. **La prochaine étape recommandée** — ce qui devrait se passer maintenant selon vous.

> **Faible :** « Connexion suspecte, merci d'investiguer. »
> **Forte :** « 02:19 UTC, règle *Brute force SSH réussi* : 60 connexions SSH échouées pour plusieurs utilisateurs depuis 198.51.100.23, puis `Accepted password for deploy`, puis `sudo ... /bin/bash`. Périmètre : hôte web-03, compte `deploy`, source 198.51.100.23. Gravité Élevée (hôte web de prod, confiance élevée — chaîne claire brute force puis élévation). J'ai extrait l'auth.log (joint) et n'ai PAS encore confiné. Recommande d'isoler web-03 et de désactiver `deploy`. »

La note forte permet d'agir en quelques secondes. Bien la rédiger est autant une compétence SOC que lire les journaux.

:::warning Connaissez votre voie d'escalade avant d'en avoir besoin
Le milieu d'un incident est le mauvais moment pour chercher qui appeler. Une **matrice d'escalade** — qui traite quelle gravité, qui réveiller à 3 h pour une critique, quand impliquer l'IR, le juridique ou la direction — appartient à vos runbooks (leçon cycle de vie) et doit être connue avant que l'alerte ne déclenche. La rapidité du tri est gâchée si l'escalade cale.
:::

## Boucler la boucle : le tri améliore la détection

Le tri n'est pas que de l'écoulement ; c'est la rétroaction qui améliore les détections dans le temps.

- **Faux positifs** → régler la règle (ajouter une exclusion, resserrer la condition) pour que le bruit ne revienne pas. La règle de brute force bâtie au module KQL s'affine chaque fois qu'une rafale bénigne est expliquée.
- **Vrais positifs bénins répétés** → documenter le schéma connu-fiable pour que les futurs analystes le closent en secondes (ou le supprimer à la source).
- **Vrais positifs que les règles ont failli manquer** → proposer une détection nouvelle ou plus large.

Un SOC qui boucle cette boucle voit son taux de faux positifs baisser et son taux de vraies détections monter. Celui qui clôt des alertes sans raisons reste aussi bruyant le mois suivant — la métrique à suivre n'est pas « alertes closes » mais « bruit supprimé définitivement et menaces réelles attrapées plus tôt ».

## Erreurs courantes

- **Investiguer pleinement chaque alerte.** La file gagne ; le tri, c'est des décisions rapides et défendables, le travail approfondi réservé aux rares qui le méritent.
- **Clore sans raison.** N'apprend rien et ne règle rien ; la raison est la valeur.
- **Un verdict sans enrichissement.** « Connexion suspecte » n'est pas actionnable ; qui, d'où, et corroboré par quoi l'est.
- **La gravité au feeling.** Utilisez impact × confiance pour que le dangereux-et-certain remonte d'abord.
- **Une passation vague.** Si l'analyste suivant doit vous appeler pour agir, la note a échoué.
- **Pas de boucle de rétroaction.** Clore des faux positifs sans régler laisse le même bruit à jamais.

## Implications pour la sécurité

- Traitez la file comme un entonnoir : une disposition rapide et raisonnée sur chaque alerte, l'investigation approfondie seulement pour les rares qui le méritent.
- Enrichissez avant de juger, et fixez la gravité à partir d'impact × confiance, non à l'instinct.
- Faites de l'escalade une passation propre et autonome, et connaissez la matrice d'escalade à l'avance.
- Réinjectez chaque faux positif dans le réglage pour que la file se calme et que les détections arrivent plus tôt.

## Vérification des connaissances

:::check Questions
1. Nommez les quatre issues de tri possibles d'une alerte.
2. Quels deux axes fixent la gravité d'une alerte, et quelle combinaison signifie « tout lâcher » ?
3. Donnez deux éléments que doit contenir une bonne note d'escalade/passation.
4. Comment le tri améliore-t-il les détections futures ?
:::

:::solution Réponses
1. **Vrai positif, faux positif, vrai positif bénin, et doublon** — chacun consigné avec sa raison.
2. **Impact** (quelle gravité si réel) et **confiance** (à quel point c'est réel). **Impact élevé + confiance élevée** est la combinaison critique où l'on lâche le reste et où l'on escalade aussitôt.
3. Deux parmi : **ce qui a déclenché et quand** ; **pourquoi c'est réel/suspect avec des preuves précises** ; **le périmètre (utilisateurs/hôtes/IP)** ; **la gravité et son raisonnement** ; **les actions déjà prises** ; **la prochaine étape recommandée** — assez pour que l'analyste suivant agisse sans vous contacter.
4. En réinjectant les issues : **les faux positifs pilotent le réglage des règles** (exclusions, conditions resserrées), **les schémas bénins répétés sont documentés ou supprimés**, et **les quasi-manqués suscitent de nouvelles détections ou plus larges** — abaissant le taux de faux positifs et attrapant les menaces réelles plus tôt.
:::

## Exercice

Vous prenez trois alertes en file :
- **A :** une connexion VPN échouée pour un utilisateur normal, en heures ouvrées, depuis sa ville habituelle.
- **B :** 50 connexions échouées pour `svc-sql` depuis un hôte interne en 2 minutes, puis une réussite, hors de toute fenêtre de changement.
- **C :** l'antivirus a mis en quarantaine un fichier de test EICAR connu sur une VM bac à sable utilisée pour la formation aux logiciels malveillants.

1. Donnez à chacune une **disposition** (vrai/faux/bénin/doublon) avec une raison d'une ligne.
2. Pour celle que vous escaladez, attribuez une **gravité** (impact × confiance) et justifiez-la.
3. Rédigez une brève **note de passation** pour cette alerte en utilisant les six éléments ci-dessus.

:::solution Réponse type
1. **A — bénin / faible :** une seule connexion échouée dans le schéma habituel est normale ; clore (noter la raison). **B — vrai positif :** rafale-d'échecs-puis-réussite sur un compte de service hors fenêtre de changement est une compromission par brute force probable ; escalader. **C — vrai positif bénin :** EICAR sur un bac à sable de formation connu est attendu ; clore, et envisager de supprimer ce chemin.
2. **B — gravité Élevée/Critique :** l'impact est élevé (un compte de service SQL peut atteindre des données réglementées) et la confiance est élevée (schéma clair rafale-puis-réussite, pas de fenêtre de changement) → lâcher le reste et escalader maintenant.
3. *Passation :* « 14:07 UTC, règle *Brute force compte de service réussi* : 50 connexions échouées pour `svc-sql` depuis l'hôte interne 10.0.4.9 en ~2 min, puis une connexion réussie, sans ticket de changement correspondant. Périmètre : compte `svc-sql`, source 10.0.4.9, hôte BD cible. Gravité Élevée (accès à des données réglementées, confiance élevée). J'ai confirmé la rafale dans les journaux d'authentification (extrait joint) et n'ai PAS confiné. Recommande de désactiver `svc-sql`, d'isoler 10.0.4.9, et de vérifier tout déplacement latéral depuis cet hôte. »
:::

## Résumé et étapes suivantes

- Le tri est la compétence quotidienne centrale du SOC : traiter la file comme un entonnoir et prendre une décision rapide, défendable et *raisonnée* sur chaque alerte.
- Enrichissez avant de juger ; fixez la gravité à partir d'impact × confiance pour que le dangereux-et-certain remonte d'abord.
- Escaladez avec une note de passation autonome, et connaissez votre matrice d'escalade avant que l'alerte ne déclenche.
- Bouclez la boucle — chaque faux positif réglé, chaque schéma documenté — pour que la file se calme et que les menaces réelles soient attrapées plus tôt.

Avec le tri et le cycle de vie réunis, vous savez mener une alerte de la file à un incident confiné et documenté — l'arc complet de l'analyse SOC. L'examen final porte sur tous les modules ; le réussir vous fait obtenir votre certificat **Analyste SOC Professionnel** (une reconnaissance de réussite sur cette plateforme, non une qualification accréditée).

===assignment===
Prenez cinq alertes d'exemple (inventez-les, ou réutilisez celles des labs précédents) et triez chacune : consignez une disposition avec une raison, enrichissez au moins les deux plus suspectes avec qui/où/quand/corroboration, attribuez une gravité via impact × confiance, et rédigez une note de passation complète pour l'unique alerte que vous escaladeriez. Terminez par une **recommandation de réglage** issue d'un faux positif de votre lot.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Chaque alerte doit avoir une disposition *et une raison* ; celle escaladée doit avoir une gravité impact × confiance et une note de passation contenant les six éléments ; et vous devez proposer un changement concret de réglage de détection.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon et sur la leçon du cycle de vie.

===references===
- NIST SP 800-61 Rev. 2, *Computer Security Incident Handling Guide* — https://csrc.nist.gov/pubs/sp/800/61/r2/final
- MITRE ATT&CK Enterprise Matrix (pour cartographier l'activité observée) — https://attack.mitre.org/matrices/enterprise/
- SANS, *Incident Handler's Handbook* — https://www.sans.org/white-papers/33901/
- FIRST, *Common Vulnerability Scoring System* (concepts d'impact/gravité) — https://www.first.org/cvss/
