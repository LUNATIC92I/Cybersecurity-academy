---
title: Renseignement sur les menaces et chasse aux menaces
chapter: Renseignement sur les menaces et chasse
summary: Apprendre comment un SOC utilise le renseignement sur les menaces pour affûter la détection et comment la chasse proactive trouve les attaques passées au travers des alertes — la pyramide de la douleur, IOC vs TTP, la chasse guidée par hypothèse, et la carte MITRE ATT&CK qui les relie.
duration: 50
level: intermediate
preview: true
status: published
objectives:
- Définir le renseignement sur les menaces et distinguer ses niveaux stratégique, opérationnel et tactique
- Expliquer IOC vs TTP et la pyramide de la douleur
- Décrire comment le renseignement alimente la détection, le tri et le blocage
- Expliquer la chasse aux menaces guidée par hypothèse et sa boucle
- Utiliser MITRE ATT&CK comme langage commun reliant renseignement, chasse et détection
prerequisites:
- Exploitation du SIEM — Le pipeline de journaux, la normalisation et l'alerte
---

## Introduction

Jusqu'ici, le SOC a été **réactif** : les journaux arrivent, les règles se déclenchent, les analystes trient. Deux disciplines le rendent **proactif**. Le **renseignement sur les menaces** indique quelles menaces comptent et à quoi elles ressemblent, pour que vos détections visent de vrais adversaires. La **chasse aux menaces** cherche activement les attaquants qui ont échappé entièrement aux alertes. Ensemble, elles transforment un SOC d'une équipe qui attend les alarmes en une équipe qui anticipe et recherche les menaces — en utilisant le même SIEM, les mêmes journaux et les mêmes détections que vous connaissez déjà.

Cette leçon est conceptuelle et défensive tout du long : elle porte sur la compréhension des adversaires pour les détecter et les stopper, non sur la conduite d'attaques.

## Ce qu'est le renseignement sur les menaces

Le **renseignement sur les cybermenaces (CTI)** est une connaissance fondée sur des preuves à propos des menaces — qui sont les adversaires, ce qu'ils ciblent, et comment ils opèrent — produite pour que les défenseurs prennent de meilleures décisions. On le décrit habituellement à trois niveaux :

| Niveau | Public | Répond à |
|---|---|---|
| **Stratégique** | Direction | Quels acteurs et tendances comptent pour *nous*, et dans quoi investir ? |
| **Opérationnel** | Management SOC/IR | Quelles campagnes et comportements d'adversaires sont actifs contre notre secteur ? |
| **Tactique** | Analystes & ingénieurs détection | Quels indicateurs et techniques précis détecter et bloquer ? |

Un bon renseignement est **actionnable et pertinent** : un flux de dix mille IP malveillantes aléatoires est du bruit ; savoir qu'un groupe ciblant votre secteur utilise une technique précise, que vous pouvez alors détecter, est du renseignement. Le test est toujours « qu'allons-nous *faire* différemment grâce à cela ? »

:::note Le renseignement doit vous être pertinent
L'échec de CTI le plus courant est de se noyer dans des indicateurs génériques qui ne s'appliquent pas à votre environnement. Priorisez le renseignement sur les menaces qui ciblent réellement votre secteur, vos technologies et vos données — le même recentrage par le risque que le parcours GRC. La pertinence l'emporte toujours sur le volume.
:::

## IOC, TTP et la pyramide de la douleur

Le renseignement décrit les adversaires à différentes profondeurs. Deux termes comptent :

- **Indicateurs de compromission (IOC)** — artefacts concrets d'une attaque : une IP ou un domaine malveillant, une empreinte de fichier, une URL. Faciles à partager et à mettre en correspondance, mais **fragiles** — un attaquant les change trivialement.
- **Tactiques, techniques et procédures (TTP)** — *comment* un adversaire se comporte : les méthodes qu'il emploie (p. ex. brute force puis usage de comptes valides, puis persistance par tâche planifiée). Plus difficiles à détecter, mais **durables** — le comportement est bien plus coûteux à changer qu'une IP.

La **pyramide de la douleur** de David Bianco classe les indicateurs selon la douleur infligée à l'adversaire quand on détecte à ce niveau :

```text
                 ▲  plus de douleur pour l'attaquant
        ┌────────────────────┐
        │       TTP          │   le plus dur à changer  →  détectez-les pour une défense durable
        ├────────────────────┤
        │  Outils            │
        ├────────────────────┤
        │  Artefacts         │   réseau/hôte
        ├────────────────────┤
        │  Noms de domaine   │
        ├────────────────────┤
        │  Adresses IP       │
        ├────────────────────┤
        │  Empreintes (hash) │   triviales à changer  →  les moins durables
        └────────────────────┘
                 ▼  moins de douleur
```

La leçon de la pyramide : **détecter les empreintes et les IP est peu coûteux mais facilement contourné ; détecter les TTP force l'attaquant à changer sa façon d'opérer, ce qui est coûteux.** Un SOC mature utilise les IOC pour des gains rapides et investit dans la détection fondée sur les TTP pour une défense durable — le *comportement* de brute force que vous avez détecté en KQL est une détection de niveau TTP, et c'est pourquoi elle continue de fonctionner même quand les IP sources changent.

## Comment le renseignement alimente le SOC

Le CTI n'a de valeur que lorsqu'il change ce que fait le SOC :

- **Ingénierie de détection** — transformer les TTP et IOC en règles SIEM (la détection-comme-du-code de la leçon SIEM), en priorisant les détections durables de TTP.
- **Enrichissement** — mettre les indicateurs observés en correspondance avec le renseignement à l'ingestion pour qu'une alerte arrive déjà signalée (l'enrichissement des leçons SIEM et tri).
- **Blocage** — pousser les indicateurs malveillants à haute confiance vers les pare-feu, proxies et EDR pour stopper automatiquement le connu-malveillant.
- **Priorisation** — un renseignement sur un acteur ciblant activement votre secteur élève la gravité des alertes associées (le jugement impact×confiance du tri).

Le flux est une boucle : le renseignement affûte la détection → les détections et chasses produisent de nouvelles observations → celles-ci reviennent comme renseignement frais (souvent partagé avec la communauté).

:::tip Partagez le renseignement en données structurées, pas en prose
Le renseignement s'échange dans des formats lisibles par machine (p. ex. STIX/TAXII) pour qu'il puisse alimenter directement la détection et le blocage plutôt que de rester dans un PDF. Le but est toujours de faire entrer un indicateur ou une technique dans le SIEM, la liste de blocage ou une chasse — non simplement d'en lire le récit.
:::

## Chasse aux menaces : la recherche proactive

La **chasse aux menaces** est la recherche proactive et guidée par hypothèse des menaces ayant échappé aux détections existantes. Elle suppose qu'une brèche peut déjà être présente et part la chercher, au lieu d'attendre une alerte. La distinction avec l'alerte :

```text
 DÉTECTION (réactive)        CHASSE (proactive)
 une règle se déclenche sur  un analyste forme une hypothèse et
 un schéma connu → alerte →  cherche dans les données des preuves
 tri                         → les trouve → en fait une nouvelle détection
```

Une chasse est **guidée par hypothèse**. Plutôt que « chercher tout ce qui est mauvais » (illimité, improductif), le chasseur part d'une idée précise et testable — généralement tirée du renseignement ou d'ATT&CK :

1. **Hypothèse** — « si un adversaire utilisait la persistance par tâche planifiée (un TTP connu des acteurs de notre secteur), nous verrions des cron/timers systemd ou des tâches planifiées Windows nouveaux ou modifiés créés hors des fenêtres de changement. »
2. **Chasse** — interroger le SIEM pour ces preuves sur tout le parc (en utilisant les données normalisées et la connaissance des journaux des leçons précédentes).
3. **Constats** — soit vous trouvez une activité suspecte (escalader comme incident), soit non (toujours utile : vous avez validé la couverture).
4. **Améliorer** — transformer une chasse réussie en une **nouvelle détection automatisée** pour que le SOC n'ait jamais à chasser la même chose manuellement à nouveau.

Cette dernière étape est l'essentiel : la chasse *élargit* continuellement la détection automatisée. Une bonne chasse attrape soit une intrusion, soit laisse derrière elle une nouvelle règle.

:::scenario Une chasse, du renseignement à la détection
Un renseignement opérationnel rapporte qu'un groupe actif contre votre secteur établit la persistance via des tâches planifiées et émet des balises vers des domaines rares toutes les quelques minutes. Le SOC forme deux hypothèses et chasse : (1) des tâches planifiées créées hors des fenêtres de changement — la chasse en trouve une sur un serveur que personne ne reconnaît ; (2) des connexions périodiques vers des domaines jamais vus — trouvées sur le même hôte (le schéma de beaconing de la leçon réseau). Ce qui a commencé comme du renseignement est devenu une chasse, la chasse est devenue un incident, et le SOC a ensuite écrit deux nouvelles détections pour que la prochaine occurrence alerte automatiquement. Renseignement → chasse → incident → détection durable.
:::

## MITRE ATT&CK : la carte commune

**MITRE ATT&CK** est une base de connaissances publique des **tactiques** d'adversaires (le *pourquoi* — p. ex. Persistance, Accès aux identifiants, Exfiltration) et des **techniques** (le *comment* — p. ex. Brute Force T1110, Tâche/Travail planifié T1053). C'est le langage commun qui relie ce parcours :

- Le **renseignement** décrit les acteurs par les techniques ATT&CK qu'ils utilisent.
- La **chasse** forme des hypothèses autour de techniques précises.
- La **détection** rattache chaque règle à la technique qu'elle couvre, révélant les **lacunes de couverture** — quelles techniques vous pouvez ou non détecter actuellement.
- La **réponse à incident** (de ce module) rattache l'activité observée à des techniques pour comprendre toute l'attaque.

Avec ATT&CK, un SOC peut répondre honnêtement à « quels comportements d'adversaires pouvons-nous détecter ? » et prioriser la fermeture des lacunes — la même carte que vous avez vue référencée dans les parcours SOC et Pentest.

## Erreurs courantes

- **Se noyer dans des IOC génériques.** Le volume n'est pas la valeur ; priorisez le renseignement pertinent pour votre secteur, votre tech et vos données.
- **Ne détecter que les indicateurs du bas de la pyramide.** Les empreintes et IP se changent trivialement ; investissez dans la détection durable fondée sur les TTP.
- **Un renseignement qui ne change rien.** Si un rapport ne devient pas une détection, un blocage ou une chasse, il n'était pas actionnable.
- **Une « chasse » illimitée.** « Chercher des trucs mauvais » n'est pas une chasse ; une chasse part d'une hypothèse précise et testable.
- **Des chasses qui n'améliorent pas la détection.** Une chasse réussie devrait laisser derrière elle une nouvelle règle automatisée pour ne pas être refaite à la main.
- **Pas de rattachement à ATT&CK.** Sans lui, vous ne pouvez voir vos lacunes de couverture de détection.

## Implications pour la sécurité

- Priorisez un renseignement pertinent et actionnable et faites-le entrer dans la détection, l'enrichissement et le blocage.
- Détectez au niveau des TTP pour une défense durable ; utilisez les IOC pour des gains rapides et peu coûteux.
- Chassez à partir d'hypothèses précises (souvent issues du renseignement ou d'ATT&CK), et convertissez chaque chasse réussie en une nouvelle détection.
- Rattachez renseignement, chasses et détections à MITRE ATT&CK pour mesurer et combler les lacunes de couverture.

## Vérification des connaissances

:::check Questions
1. Quels sont les trois niveaux de renseignement sur les menaces, et pour qui chacun ?
2. Quelle est la différence entre un IOC et un TTP, et lequel est plus durable à détecter ?
3. Qu'est-ce qui rend une chasse « guidée par hypothèse », et que devrait laisser derrière elle une chasse réussie ?
4. Comment MITRE ATT&CK relie-t-il renseignement, chasse et détection ?
:::

:::solution Réponses
1. **Stratégique** (pour la direction — quelles menaces/tendances comptent et dans quoi investir), **opérationnel** (pour le management SOC/IR — campagnes et comportements actifs contre le secteur), et **tactique** (pour les analystes/ingénieurs détection — indicateurs et techniques précis à détecter et bloquer).
2. Un **IOC** est un artefact concret (IP, domaine, empreinte) — facile à mettre en correspondance mais fragile, changé trivialement par l'attaquant. Un **TTP** est *comment* l'adversaire se comporte — plus difficile à détecter mais **bien plus durable**, car changer de comportement est bien plus coûteux que changer une IP. La détection de niveau TTP est plus durable.
3. Elle part d'une **hypothèse précise et testable** (généralement issue du renseignement ou d'ATT&CK) plutôt que d'un « chercher des trucs mauvais » illimité, de sorte que la chasse peut être exécutée et tranchée sur les données. Une chasse réussie devrait laisser derrière elle une **nouvelle détection automatisée** pour que le SOC n'ait jamais à rechasser la même chose à la main.
4. ATT&CK est un catalogue commun de **tactiques et techniques** d'adversaires : le renseignement décrit les acteurs par les techniques qu'ils utilisent, les chasses forment des hypothèses autour de techniques, les détections sont rattachées aux techniques qu'elles couvrent (exposant les lacunes de couverture), et l'IR rattache l'activité observée à des techniques — un seul langage commun pour les quatre.
:::

## Exercice

En utilisant un TTP pertinent pour un secteur de votre choix (p. ex. accès aux identifiants par brute force, ou persistance par tâche planifiée) :

1. Écrivez-le comme une **hypothèse de chasse** sous la forme « si l'adversaire a fait X, nous verrions Y dans la source de journal Z ».
2. Décrivez la **logique de requête SIEM** (en langage clair) que vous exécuteriez et quels champs normalisés/sources de journaux elle utilise (réutilisez les leçons précédentes).
3. Indiquez ce que vous feriez si la chasse **trouve** quelque chose et si elle ne trouve **rien**.
4. Nommez la **technique MITRE ATT&CK** à laquelle elle se rattache et la **nouvelle détection** que vous créeriez d'une chasse réussie.

:::solution Réponse type (abrégée)
1. *Hypothèse :* « Si un adversaire a établi la persistance via des tâches planifiées, nous verrions des tâches cron / timers systemd / tâches planifiées Windows nouvelles ou modifiées créées hors des fenêtres de changement approuvées, surtout sur des serveurs. »
2. Interroger le SIEM pour les événements de création ou de modification de tâche planifiée/cron/timer (des journaux Windows et Linux, normalisés vers un schéma commun `action`/`host`/`user`/`time`), filtrés aux horaires hors fenêtres de changement et corrélés au compte qui les a créés.
3. *Trouve quelque chose :* traiter comme incident potentiel — enrichir (qui/où/quand), évaluer la gravité, et escalader selon la leçon de tri. *Ne trouve rien :* consigner la chasse comme couverture validée pour cette technique, et créer tout de même la détection ci-dessous.
4. *ATT&CK :* Tâche/Travail planifié (**T1053**). *Nouvelle détection :* une règle automatisée alertant sur la création de tâche planifiée/cron/timer hors des fenêtres de changement, pour que les occurrences futures alertent sans chasse manuelle.
:::

## Résumé et étapes suivantes

- Le **renseignement sur les menaces** (stratégique/opérationnel/tactique) rend la détection pertinente en la visant sur de vrais adversaires — priorisez la pertinence et l'actionnabilité sur le volume.
- Détectez au niveau des **TTP** pour une défense durable (la pyramide de la douleur) ; utilisez les **IOC** pour des gains rapides et peu coûteux ; alimentez les deux dans la détection, l'enrichissement et le blocage.
- La **chasse aux menaces** est proactive et **guidée par hypothèse**, et chaque chasse réussie devrait devenir une nouvelle détection automatisée.
- **MITRE ATT&CK** est la carte commune qui relie renseignement, chasse, détection et réponse à incident et expose les lacunes de couverture.

Les modules SOC restants — investigation de logiciels malveillants et de postes, surveillance identité et cloud, et opérations SOC avancées — étendent ces capacités proactives à davantage de sources et techniques ; tous restent défensifs, dans le cadre d'usage responsable de la plateforme.

===assignment===
Produisez un **plan de renseignement et de chasse** d'une page pour une organisation de votre choix : identifiez deux menaces réellement pertinentes pour son secteur (avec les techniques ATT&CK qu'elles utilisent), décrivez comment vous opérationnaliseriez le renseignement à leur sujet (détection, enrichissement, blocage), et écrivez deux **hypothèses de chasse** avec les sources de journaux et la logique de requête de chacune. Pour chaque chasse, indiquez la nouvelle détection automatisée que vous créeriez d'un résultat positif, et rattachez le tout à des techniques MITRE ATT&CK.

===criteria===
- Terminez la lecture et la vérification des connaissances, et produisez le plan.
- Le renseignement doit être pertinent et actionnable (non un déversement d'IOC génériques) ; les chasses doivent être des hypothèses précises et testables liées à des sources de journaux ; et chacune doit nommer une technique ATT&CK et la détection qu'une chasse réussie créerait.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- MITRE ATT&CK — https://attack.mitre.org/
- David Bianco, *The Pyramid of Pain* — https://detect-respond.blogspot.com/2013/03/the-pyramid-of-pain.html
- MITRE, *TTP-Based Hunting* — https://www.mitre.org/sites/default/files/2021-11/prs-19-3892-ttp-based-hunting.pdf
- OASIS, *STIX/TAXII* (partage structuré de renseignement sur les menaces) — https://oasis-open.github.io/cti-documentation/
- CISA, *Avis de cybersécurité et partage d'information sur les menaces* — https://www.cisa.gov/news-events/cybersecurity-advisories
