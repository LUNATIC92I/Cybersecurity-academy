---
title: Reconnaissance passive et cartographie de la surface d'attaque
chapter: Comprendre la cible avant d'y toucher
summary: Apprendre comment un testeur d'intrusion se construit une image d'une cible à partir d'informations publiques seules — sources OSINT, surface d'attaque externe, et les risques d'exposition et humains qu'elle révèle — sans envoyer de trafic intrusif, et dans le respect de l'autorisation et du périmètre qui gouvernent la mission.
duration: 50
level: intermediate
preview: true
status: published
objectives:
- Distinguer la reconnaissance passive du scan actif et savoir pourquoi la limite compte
- Identifier les principales catégories de renseignement de sources ouvertes (OSINT) qu'utilise un testeur
- Cartographier la surface d'attaque externe d'une organisation à partir de données publiques
- Reconnaître, de façon défensive, les risques d'exposition et humains que la reconnaissance révèle
- Rester dans l'autorisation, le périmètre et le droit de la vie privée en recueillant l'information
prerequisites:
- Autorisation, périmètre et règles d'engagement
---

## Introduction

Avant d'évaluer ou de rapporter quoi que ce soit, un testeur se construit une image de la cible : ce qu'elle expose à Internet, les technologies qu'elle utilise, et ce qu'un attaquant pourrait apprendre d'elle à partir de sources publiques seules. C'est la **reconnaissance**, et sa première phase, la plus sûre, est **passive** — recueillir une information déjà publique sans envoyer de trafic intrusif à la cible.

Cette leçon est conceptuelle et d'orientation défensive. Elle enseigne *quelles catégories d'information existent et quel risque elles révèlent*, pour que vous compreniez l'exposition de votre propre organisation telle qu'un attaquant la verrait — non un recueil d'outils ou de commandes. Tout ici reste borné par l'autorisation, le périmètre et le cadre légal du premier module : la reconnaissance fait partie de la mission, ce n'est pas un terrain sans règles.

## Reconnaissance passive et active

| | Reconnaissance passive | Reconnaissance active |
|---|---|---|
| Ce qu'elle fait | Recueille une information déjà publique | Envoie du trafic à / interagit avec la cible |
| Perception par la cible | La cible ne le sait généralement pas | La cible peut la détecter et la journaliser |
| Exemples (catégories) | Moteurs de recherche, registres publics, vérif. de fuites | Scan de ports, sondage de services (module suivant) |
| Risque de perturbation | Quasi nul | Peut perturber des systèmes fragiles |

La distinction compte pour deux raisons. D'abord, la **discrétion et la sûreté** : le travail passif ne touche pas la cible, il ne peut donc pas la perturber et c'est l'étape naturelle de départ. Ensuite, et surtout ici, le **périmètre et l'autorisation** : dès que vous *interagissez* avec un système (reconnaissance active), vous réalisez une action intrusive qui doit être dans le périmètre et autorisée par les règles d'engagement. Le recueil passif de données publiques est moins risqué, mais — voir plus bas — il n'est pas pour autant sans règles.

:::note La limite entre passif et actif est une décision de périmètre
Interroger un registre DNS public est passif ; se connecter au serveur DNS de la cible pour énumérer ses enregistrements est actif. Lire les offres d'emploi publiques d'une entreprise est passif ; se connecter à un portail est actif. En cas de doute sur le côté de la limite où tombe une action, traitez-la comme active et confirmez qu'elle est dans le périmètre et autorisée avant de la faire.
:::

## Catégories d'OSINT

Le **renseignement de sources ouvertes (OSINT)** est l'information recueillie à partir de sources publiquement disponibles. Un testeur (et un attaquant) puise dans des catégories reconnaissables :

- **Empreinte organisationnelle** — les sites de l'entreprise, ses filiales, marques, implantations, et les technologies qu'elle annonce utiliser.
- **Enregistrement de domaines et réseau** — données WHOIS/registres publiques, enregistrements DNS, plages d'IP et journaux de transparence des certificats qui révèlent noms d'hôtes et infrastructure.
- **Empreinte technologique** — indicateurs publiquement visibles des logiciels, frameworks et services d'un site (en-têtes, pages publiques, offres d'emploi).
- **Personnes et structure organisationnelle** — annuaires publics, réseaux professionnels et conférences qui révèlent rôles, lignes hiérarchiques et qui détient des accès sensibles.
- **Documents publiés et métadonnées** — PDF, tableurs et images publics dont les métadonnées peuvent divulguer des noms d'utilisateur, versions de logiciels ou chemins internes.
- **Exposition de code et cloud** — dépôts de code publics, stockage public mal configuré, et sous-domaines oubliés.
- **Exposition d'identifiants fuités** — si les adresses e-mail de l'organisation apparaissent dans des jeux de données de fuites connus (vérifié via des services réputés de notification de fuites).

Chaque catégorie est aussi un **inventaire défensif** : une équipe bleue utilise les mêmes sources pour trouver ce qu'elle expose par inadvertance.

## Cartographier la surface d'attaque externe

La **surface d'attaque** est la somme des points où un attaquant pourrait tenter d'interagir avec l'organisation. La reconnaissance passive l'assemble à partir des catégories ci-dessus :

```text
        SURFACE D'ATTAQUE EXTERNE (à partir de données publiques)
   ┌──────────────┬───────────────┬──────────────────┐
   │ Domaines &   │ Services /    │ Personnes &      │
   │ sous-domaines│ technologies  │ identifiants     │
   │ (DNS, certs) │ exposés       │ (personnel, fuites)│
   └──────┬───────┴───────┬───────┴────────┬─────────┘
          └───────────────┼────────────────┘
                          ▼
             une liste priorisée des endroits où
             l'organisation est la plus exposée
```

Le résultat n'est pas une attaque — c'est une **carte** : les domaines et hôtes exposés à Internet, les technologies qu'ils semblent utiliser, les personnes susceptibles d'être ciblées (par exemple par hameçonnage, vecteur d'attaque vu dans le parcours SOC), et toute exposition déjà visible dans les données publiques de fuites. Cette carte alimente la phase d'évaluation ultérieure : elle indique où regarder, et elle indique à un défenseur ce qu'il faut fermer, surveiller ou protéger d'abord.

:::scenario La reconnaissance comme miroir défensif
Un testeur cartographie un client et trouve : un sous-domaine `staging.` oublié qui résout toujours vers un serveur actif ; un document public dont les métadonnées nomment une version de logiciel interne ; et plusieurs e-mails du personnel apparaissant dans d'anciens jeux de données de fuites. Rien de cela n'a nécessité de toucher aux systèmes de la cible — tout était public. La valeur pour le client est immédiate et défensive : retirer l'hôte de staging, nettoyer les métadonnées des documents avant publication, et forcer la réinitialisation des mots de passe plus la MFA pour les comptes exposés. Le premier produit de la reconnaissance est une liste de tâches pour le défenseur.
:::

## Les risques que révèle la reconnaissance

La reconnaissance passive fait régulièrement remonter les mêmes classes de risque organisationnel — celles qu'un défenseur devrait fermer :

- **Exposition oubliée** — sous-domaines périmés, anciens systèmes de test ou de staging, hôtes mis hors service mais toujours actifs. Les attaquants adorent ce que les défenseurs ont cessé de surveiller.
- **Fuite d'information** — métadonnées de documents, pages d'erreur verbeuses, et dépôts publics révélant du détail interne (versions, chemins, voire secrets committés par accident).
- **Surpartage par les personnes** — profils et publications publics détaillés qui aident un attaquant à forger un hameçonnage ou une usurpation convaincants.
- **Exposition d'identifiants** — mots de passe réutilisés issus de fuites passées permettant le credential-stuffing (le vecteur d'abus d'identifiants du parcours SOC).

Pour chacun, le remède défensif est concret : inventorier et retirer les actifs périmés, nettoyer les métadonnées et verrouiller les dépôts, former le personnel sur ce qu'il ne faut pas surpartager, et surveiller les identifiants fuités tout en imposant la MFA. La reconnaissance, lue de façon défensive, est simplement une évaluation de la surface d'attaque externe — et réduire cette surface est l'un des gains de sécurité les moins chers qu'une organisation puisse réaliser.

## Autorisation, périmètre et droit

La reconnaissance passive semble peu risquée, mais elle reste gouvernée :

- **Le périmètre s'applique toujours.** Recueillez de l'information sur l'organisation que vous êtes autorisé à tester ; ne vous égarez pas vers des tiers, partenaires ou individus sans rapport, hors de la mission.
- **Ne passez pas en actif sans autorisation.** Recueillir des données publiques est passif ; dès que vous sondez ou vous connectez, il vous faut une permission explicite et des cibles dans le périmètre.
- **Respectez le droit de la vie privée et de la protection des données.** L'OSINT sur des *personnes* est une donnée personnelle ; traitez-la licitement, de façon minimale et seulement pour la finalité autorisée (les principes de vie privée du parcours GRC s'appliquent). Ne collectez pas plus que ne l'exige la mission, et protégez ce que vous recueillez.
- **Traitez les constats de façon sécurisée.** Un dossier de reconnaissance est une carte pour attaquer le client — stockez-le et transmettez-le comme le document sensible qu'il est (comme dans la leçon de rapport).

:::warning Public ne signifie pas sans restriction
Qu'une information soit publiquement accessible ne signifie pas que tout usage en est licite ou dans le périmètre. Accéder à un jeu de données « public » via une faille, extraire en violation des conditions d'utilisation, ou recueillir des données personnelles au-delà de la finalité autorisée peut franchir des lignes légales même sans toucher les serveurs de la cible. En cas de doute, confirmez avec le responsable de la mission avant de poursuivre.
:::

## Erreurs courantes

- **Confondre passif et actif.** Sonder ou se connecter est actif et nécessite une autorisation dans le périmètre ; ne franchissez pas la limite parce que cela « ressemblait à de la recherche ».
- **S'égarer hors périmètre.** Une reconnaissance sur des partenaires, filiales ou individus hors de la mission est un problème de périmètre et de droit.
- **Mal traiter les données personnelles.** L'OSINT sur les personnes est une donnée personnelle ; en collecter trop ou la traiter négligemment viole les obligations de vie privée.
- **Traiter la reconnaissance comme de l'anecdote.** Son vrai produit est une liste de tâches défensives — priorisez et transmettez, ne vous contentez pas de collecter.
- **Stockage non sécurisé.** Un dossier de reconnaissance est une carte d'attaque sensible ; protégez-le.

## Implications pour la sécurité

- Commencez passif : construisez l'image à partir de données publiques avant toute interaction, c'est le plus sûr et l'étape naturelle de départ.
- Cartographiez la surface d'attaque externe — domaines, technologies exposées, personnes et identifiants fuités — le même inventaire dont a besoin un défenseur.
- Convertissez les constats en actions défensives concrètes : retirer les actifs périmés, nettoyer les métadonnées, former les personnes, imposer la MFA et surveiller les fuites.
- Gardez chaque étape dans le périmètre, l'autorisation et le droit de la vie privée, et protégez le produit sensible de la reconnaissance.

## Vérification des connaissances

:::check Questions
1. Quelle est la différence clé entre reconnaissance passive et active, et pourquoi compte-t-elle pour le périmètre ?
2. Nommez trois catégories d'OSINT qu'un testeur pourrait utiliser.
3. Donnez deux actions défensives concrètes qu'un client pourrait tirer d'une carte de reconnaissance typique.
4. Pourquoi l'OSINT sur les personnes doit-il être traité avec un soin particulier ?
:::

:::solution Réponses
1. La reconnaissance **passive** recueille une information déjà publique sans envoyer de trafic à la cible ; la reconnaissance **active** interagit avec la cible (et peut être détectée et peut perturber). Elle compte pour le périmètre car, dès que vous interagissez (actif), vous réalisez une action intrusive qui doit être explicitement dans le périmètre et autorisée, alors que le recueil passif est moins risqué (bien que toujours gouverné).
2. Trois parmi : empreinte organisationnelle ; enregistrement de domaines/réseau (WHOIS, DNS, transparence des certificats) ; empreinte technologique ; personnes/structure organisationnelle ; documents publiés et métadonnées ; exposition de code et cloud ; exposition d'identifiants fuités.
3. Deux parmi : retirer les sous-domaines et systèmes de test oubliés/périmés ; nettoyer les métadonnées des documents publics et verrouiller les dépôts publics ; former le personnel contre le surpartage/l'hameçonnage ; forcer la réinitialisation des mots de passe et imposer la MFA pour les comptes trouvés dans les données de fuites ; surveiller les identifiants fuités.
4. Parce que l'OSINT sur les personnes est une **donnée personnelle**, soumise au droit de la vie privée et de la protection des données : elle doit être collectée de façon minimale, seulement pour la finalité autorisée, traitée licitement et protégée — en collecter trop ou mal la traiter viole ces obligations même si la donnée est publique.
:::

## Exercice

Imaginez que vous êtes autorisé à évaluer l'exposition externe d'une entreprise fictive (passif uniquement).

1. Listez **quatre catégories d'OSINT** que vous examineriez et, pour chacune, un exemple de ce que vous pourriez trouver et pourquoi cela compte.
2. Esquissez la **carte de surface d'attaque** que vous produiriez (domaines/hôtes, technologies exposées, personnes, exposition d'identifiants) sous forme de courte liste structurée.
3. Pour trois de vos constats, donnez la **remédiation défensive** que vous recommanderiez au client.
4. Énoncez deux **limites de périmètre ou légales** que vous observeriez tout du long.

:::solution Réponse type (abrégée)
1. *Enregistrement domaines/réseau* → un `vpn-old.company.com` oublié qui résout toujours (exposition périmée). *Empreinte technologique* → une page publique révélant une version de framework obsolète (cible de correctif). *Personnes* → e-mails et rôles du personnel sur un réseau professionnel (ciblage d'hameçonnage). *Identifiants fuités* → plusieurs e-mails de l'entreprise dans d'anciens jeux de données de fuites (risque de credential-stuffing).
2. *Carte :* **Domaines/hôtes** — site principal, `vpn-old.`, `staging.` ; **Technologies** — framework web vX, fournisseur de messagerie ; **Personnes** — admin informatique et personnel financier identifiables ; **Identifiants** — 4 e-mails dans des données de fuites.
3. Retirer/sécuriser `vpn-old.` et `staging.` ; mettre à niveau ou corriger le framework obsolète ; forcer les réinitialisations et imposer la MFA sur les comptes fuités (et surveiller les fuites).
4. Je (a) ne recueillerais de l'information que sur l'organisation autorisée, pas sur les partenaires ou des individus sans rapport ; et (b) traiterais toute donnée personnelle de façon minimale et licite, en ne collectant que ce dont la mission a besoin et en protégeant le dossier de reconnaissance comme sensible.
:::

## Résumé et étapes suivantes

- La reconnaissance construit l'image d'une cible ; sa première phase est **passive** — données publiques seulement, aucun trafic intrusif, le départ naturel et le plus sûr.
- L'**OSINT** couvre l'empreinte organisationnelle, l'enregistrement de domaines/réseau, l'empreinte technologique, les personnes, les documents/métadonnées, l'exposition de code/cloud et les identifiants fuités.
- Le produit est une **carte de la surface d'attaque externe** qui sert aussi de liste de tâches au défenseur : retirer les actifs périmés, nettoyer les fuites, former les personnes, imposer la MFA.
- Passif ne signifie pas sans restriction — restez dans le périmètre et l'autorisation, respectez le droit de la vie privée, et protégez le produit sensible.

Le module suivant, **Énumération réseau**, passe du passif à l'*actif* — interagir avec des cibles dans le périmètre pour énumérer les services — réalisé uniquement dans une autorisation explicite et, lorsque l'exploitation suit, dans des environnements de lab isolés.

===assignment===
Pour une organisation de votre choix (ou une fictive que vous définissez), produisez sur papier un **plan de reconnaissance passive et une carte de surface d'attaque** : les catégories d'OSINT que vous examineriez, une carte de surface d'attaque structurée (domaines, technologies, personnes, exposition d'identifiants), et — surtout — une **liste de remédiation défensive** priorisée qui en découle. Ajoutez un court paragraphe énonçant les limites de périmètre et les limites de vie privée/légales que vous observeriez, et comment vous protégeriez le dossier obtenu.

===criteria===
- Terminez la lecture et la vérification des connaissances, et produisez le plan, la carte et la liste de remédiation.
- Votre travail doit rester strictement passif/conceptuel (aucune instruction pour sonder ou accéder à des systèmes), convertir les constats en actions défensives concrètes, et énoncer explicitement les limites de périmètre, de vie privée et de traitement.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- OSINT Framework (catégories de renseignement de sources ouvertes) — https://osintframework.com/
- NIST SP 800-115, *Technical Guide to Information Security Testing and Assessment* (phase de reconnaissance) — https://csrc.nist.gov/pubs/sp/800/115/final
- MITRE ATT&CK, tactique Reconnaissance (TA0043) — https://attack.mitre.org/tactics/TA0043/
- Certificate Transparency (journaux publics de certificats) — https://certificate.transparency.dev/
- Have I Been Pwned (notification réputée d'identifiants fuités) — https://haveibeenpwned.com/
