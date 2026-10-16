---
title: Surveillance de l'identité et du cloud
chapter: Surveillance de l'identité et du cloud
summary: Surveiller le périmètre moderne — l'identité et le cloud. Apprendre à lire les journaux de connexion et d'audit des fournisseurs d'identité et des plateformes cloud, reconnaître la compromission de compte, les attaques MFA, les consentements OAuth risqués et la mauvaise configuration cloud, et comprendre le modèle de responsabilité partagée qui décide de ce que vous devez surveiller.
duration: 50
level: intermediate
preview: true
status: published
objectives:
- Expliquer pourquoi l'identité est le périmètre de sécurité moderne
- Lire les journaux de connexion et d'audit d'un fournisseur d'identité pour repérer la compromission
- Reconnaître la fatigue MFA, le voyage impossible, le vol de jeton et le consentement OAuth risqué
- Décrire ce que capturent les journaux d'audit cloud et le modèle de responsabilité partagée
- Détecter les mauvaises configurations cloud et l'abus du plan de contrôle courants, de façon défensive
prerequisites:
- Exploitation du SIEM — Le pipeline de journaux, la normalisation et l'alerte
---

## Introduction

Les leçons SOC précédentes surveillaient les hôtes et les réseaux. Mais les organisations modernes fonctionnent sur l'**identité** et le **cloud** : le personnel se connecte au SaaS et aux consoles cloud depuis n'importe où, et l'ancien périmètre réseau ne contient presque plus les données. La conséquence pour un SOC est brutale — **l'identité est le nouveau périmètre**, et les journaux qui comptent le plus sont souvent les enregistrements de connexion du fournisseur d'identité et la piste d'audit de la plateforme cloud, non un pare-feu. Cette leçon vous apprend à lire ces sources et à reconnaître les attaques qui leur sont propres. Elle est défensive tout du long : comprendre comment l'identité et le cloud sont abusés pour les détecter et les stopper.

Tout ici afflue vers le SIEM que vous avez appris : l'identité et le cloud ne sont que davantage de sources à collecter, normaliser et sur lesquelles détecter — mais elles portent les clés de l'organisation.

## L'identité est le périmètre

Quand les applications vivent dans le cloud, un attaquant a rarement besoin de percer un réseau — il lui faut un **compte**. Une seule identité compromise, surtout privilégiée, peut atteindre la messagerie, les fichiers et l'infrastructure cloud depuis n'importe où sur Internet. Ainsi, le **fournisseur d'identité (IdP)** — le système qui authentifie les utilisateurs (p. ex. un annuaire cloud / SSO) — est à la fois la cible la plus précieuse et la source de surveillance la plus riche.

Deux types de journaux d'un IdP comptent le plus :

- **Journaux de connexion** — chaque tentative d'authentification : qui, quand, d'où (IP, géolocalisation, appareil), succès ou échec, le code de résultat/erreur, et si la MFA a été satisfaite.
- **Journaux d'audit** — chaque changement administratif : un utilisateur créé, un rôle accordé, une MFA réinitialisée, une application consentie, une politique modifiée.

Ce sont l'équivalent, côté identité, des journaux d'authentification Windows/Linux que vous avez déjà lus — les mêmes questions « qui s'est authentifié, d'où, et qu'a-t-il fait ensuite ? », à l'échelle du cloud.

## Lire les connexions : les signaux de compromission

La compromission de compte laisse des schémas reconnaissables dans les journaux de connexion :

- **Rafales d'échecs puis réussite** — le schéma brute force / password spraying du module de détection, désormais contre l'IdP (de nombreux échecs avec codes d'erreur d'identifiants, puis une réussite).
- **Voyage impossible** — des connexions réussies depuis deux lieux trop éloignés pour voyager entre eux dans le temps écoulé (p. ex. Londres puis Singapour 20 minutes plus tard) — un fort signal de vol de jeton/identifiant.
- **Connexion depuis une infrastructure inhabituelle** — proxies anonymisants, IP d'hébergeurs, ou un pays où l'utilisateur ne travaille jamais, surtout pour un compte privilégié.
- **Authentification héritée / sans MFA** — une connexion qui a contourné la MFA via un ancien protocole est suspecte là où la politique exige la MFA.

La corrélation la plus précieuse est celle du tri : **de nombreux échecs puis une réussite, depuis un nouveau lieu, sans défi MFA** sur un compte privilégié est une compromission quasi certaine — enrichissez et escaladez.

### Attaques MFA

La MFA est essentielle mais pas magique ; les attaquants la ciblent directement :

- **Fatigue MFA / push bombing** — l'attaquant, détenant un mot de passe valide, déclenche des invites push MFA répétées en espérant que l'utilisateur en approuve une par agacement. *Signal :* de nombreuses demandes MFA dans une courte fenêtre, puis une approbation. *Défense :* MFA par correspondance de numéro, limitation des push, et formation des utilisateurs.
- **Vol de jeton / de session** — voler un jeton de session (p. ex. par hameçonnage via un reverse-proxy) permet à l'attaquant de sauter entièrement la MFA en rejouant la session authentifiée. *Signal :* une session utilisée depuis une nouvelle IP/un nouvel appareil sans nouvelle authentification ; voyage impossible sur un jeton.
- **SIM swap / facteurs faibles** — les codes SMS sont hameçonnables et interceptables ; leur présence sur des comptes privilégiés est en soi une faiblesse.

:::tip MFA satisfaite n'est pas synonyme de MFA sûre
« MFA satisfaite » dans un journal de connexion ne signifie pas que la connexion est sûre — ce pourrait être une invite de fatigue approuvée ou un jeton volé rejoué. Lisez *comment* la MFA a été satisfaite (une correspondance de numéro fraîche vs une session réutilisée) et corrélez avec le lieu et l'appareil. Une approbation juste après une rafale d'invites push est l'attaque de fatigue, non une connexion légitime.
:::

## Consentement OAuth risqué et octrois d'applications

Au-delà des connexions, l'identité cloud a une seconde voie d'abus : le **consentement d'application**. Un utilisateur peut accorder à une application tierce l'accès à sa messagerie, ses fichiers ou l'annuaire via OAuth. Les attaquants en abusent par des **octrois de consentement illicites** — une application malveillante qui, une fois consentie, a un accès persistant aux données *sans avoir à nouveau besoin du mot de passe ni de la MFA*.

- **Signal :** événements de journal d'audit pour un nouveau consentement d'application, surtout un demandant de larges permissions (lire tout le courrier, accéder à tous les fichiers) accordé par un utilisateur ordinaire ; une application nouvellement enregistrée recevant des jetons.
- **Pourquoi c'est important :** c'est de la persistance et un accès aux données qui survivent à une réinitialisation de mot de passe — donc facile à manquer si vous ne surveillez que les connexions. *Défense :* restreindre qui peut consentir aux applications, revoir les octrois, et alerter sur les consentements à privilèges élevés.

C'est l'analogue cloud des mécanismes de persistance de la leçon journaux Linux : l'attaquant établit un point d'ancrage qui survit à la compromission initiale.

## Surveillance de la plateforme cloud et responsabilité partagée

L'infrastructure cloud (IaaS/PaaS) ajoute le **plan de contrôle** — l'API et la console par lesquelles tout est créé, modifié et supprimé. Chaque plateforme cloud enregistre cela dans un **journal d'audit/d'activité** (l'équivalent cloud d'une piste d'audit) : qui a fait quel appel d'API, quand, d'où, et s'il a réussi.

### Le modèle de responsabilité partagée

Crucialement, le fournisseur cloud sécurise *certaines* couches et le client en sécurise d'autres — le **modèle de responsabilité partagée** — et cela décide **ce que le SOC doit surveiller** :

```text
            LE FOURNISSEUR sécurise        LE CLIENT sécurise (vous surveillez)
 IaaS   │  physique, hôte, hyperviseur  │  OS, config réseau, IAM, données, appli
 PaaS   │  + OS et runtime              │  IAM, config, données, appli
 SaaS   │  + application                │  identité, accès, partage de données, config
```

Le fournisseur ne vous dira pas qu'un employé a rendu un compartiment de stockage public ou accordé un rôle trop large — c'est **votre** responsabilité, visible dans **vos** journaux d'audit. Mal lire ce modèle (supposer « le cloud est sûr ») est la façon dont surviennent les brèches ; le SOC surveille les couches que le client possède.

### Quoi surveiller dans les journaux d'audit cloud

- **Changements IAM** — une nouvelle clé d'accès, un rôle ou une politique accordant de larges permissions/admin, un nouvel utilisateur privilégié, des changements de confiance inter-comptes. L'élévation de privilèges du plan de contrôle est la version cloud de l'élévation vue sur les hôtes.
- **Mauvaise configuration** — stockage rendu public, règles de groupe de sécurité/pare-feu ouvertes à Internet, journalisation ou surveillance désactivée (un attaquant vous aveuglant — encore le signal d'altération).
- **Activité d'API anormale** — une rafale d'appels d'énumération, création de ressources dans des régions inhabituelles (p. ex. démarrer du calcul pour du minage de cryptomonnaie), accès ou export massif de données.
- **Usage du compte root/break-glass** — les comptes cloud les plus privilégiés ne devraient presque jamais être utilisés ; toute activité est à fort signal.

:::warning Désactiver la journalisation est une attaque, pas un accident
Tant en identité qu'en cloud, un événement de journal d'audit qui *désactive* la journalisation, supprime un puits de journaux ou réduit la surveillance est l'un des événements à plus fort signal que vous puissiez voir — c'est souvent un attaquant effaçant ses traces (le principe « l'absence de journaux est une preuve » de la leçon journaux Linux, à l'échelle du cloud). Alertez sur les changements de la configuration de journalisation et de surveillance elle-même, et protégez ces réglages.
:::

## L'amener dans le SOC

L'identité et le cloud ne sont que davantage de sources pour le pipeline SIEM — mais de grande valeur :

- **Collecter** les journaux de connexion et d'audit de l'IdP et les journaux du plan de contrôle cloud via leurs API (l'étape de collecte de la leçon SIEM).
- **Normaliser** vers le schéma commun (`user`, `src_ip`, `action`, `result`) pour que les événements d'identité se corrèlent aux événements hôte et réseau — une connexion depuis une IP malveillante se relie à l'activité de cette IP ailleurs.
- **Détecter** les schémas ci-dessus comme règles ; **enrichir** avec géolocalisation, rôle de l'utilisateur et appareil ; **prioriser** par privilège (un changement sur un compte admin devance un utilisateur normal).
- **Rattacher** aux techniques cloud de MITRE ATT&CK pour voir vos lacunes de couverture identité/cloud (de la leçon renseignement).

## Erreurs courantes

- **Penser encore que le réseau est le périmètre.** C'est l'identité ; les journaux de l'IdP sont souvent la source la plus importante dont vous disposez.
- **Faire confiance à « MFA satisfaite ».** Les approbations de fatigue et les jetons rejoués satisfont la MFA ; lisez comment elle a été satisfaite et corrélez.
- **Ignorer le consentement OAuth/d'application.** Les octrois illicites sont une persistance qui survit aux réinitialisations de mot de passe et sont manqués si vous ne surveillez que les connexions.
- **Mal lire la responsabilité partagée.** Le fournisseur ne sécurise pas votre IAM, votre config et vos données ; c'est à vous de les surveiller.
- **Ne pas alerter sur les changements de journalisation.** Désactiver la journalisation cloud/identité est un attaquant qui vous aveugle — l'un des événements à plus fort signal.

## Implications pour la sécurité

- Traitez le fournisseur d'identité comme le périmètre principal et ses journaux de connexion/audit comme des sources de premier plan.
- Détectez les schémas de compromission de compte (échec-puis-réussite, voyage impossible, infra inhabituelle) et les attaques MFA (fatigue, vol de jeton), en lisant *comment* la MFA a été satisfaite.
- Surveillez le consentement OAuth/d'application pour les octrois illicites — une persistance qui survit à une réinitialisation de mot de passe.
- Connaissez le modèle de responsabilité partagée et surveillez les couches du client : changements IAM, mauvaise configuration, activité d'API anormale, et toute désactivation de la journalisation.

## Vérification des connaissances

:::check Questions
1. Pourquoi l'identité est-elle décrite comme le périmètre moderne, et quels journaux d'IdP comptent le plus ?
2. Qu'est-ce que le voyage impossible, et pourquoi est-ce un fort signal de compromission ?
3. Pourquoi un attaquant disposant d'un jeton de session volé ou d'une approbation de fatigue MFA peut-il quand même entrer malgré la MFA ?
4. Selon le modèle de responsabilité partagée, qui est responsable d'un compartiment de stockage rendu public, et où le SOC le verrait-il ?
:::

:::solution Réponses
1. Parce que les applications cloud/SaaS sont atteignables de n'importe où avec un **compte**, de sorte qu'une identité compromise — non un réseau percé — est la voie d'entrée habituelle. Les **journaux de connexion de l'IdP** (tentatives d'authentification, lieu, MFA) et les **journaux d'audit** (changements admin, octrois de rôle, consentements d'application) comptent le plus.
2. Le **voyage impossible** est deux connexions réussies depuis des lieux trop éloignés pour voyager entre eux dans le temps écoulé (p. ex. Londres puis Singapour 20 minutes plus tard). Il indique fortement un vol d'identifiant ou de jeton, car une seule identité ne peut être physiquement aux deux endroits.
3. Parce que la **fatigue MFA** (l'utilisateur approuve un push répété déclenché par l'attaquant) et le **vol de jeton/de session** (rejouer une session authentifiée volée) aboutissent tous deux à une connexion où « la MFA a été satisfaite » sans que l'attaquant prouve un facteur frais — MFA satisfaite n'est pas synonyme de connexion légitime.
4. Le **client** est responsable (c'est une action de couche configuration/données, non l'infrastructure du fournisseur), et le SOC le verrait dans le **journal d'audit/d'activité de la plateforme cloud** comme un appel d'API qui a changé l'accès du compartiment en public.
:::

## Exercice

Pour une organisation utilisant un IdP cloud (SSO) et une plateforme cloud :

1. Listez **trois signaux de connexion d'identité** et **deux signaux de journal d'audit d'identité** sur lesquels vous alerteriez, et pourquoi.
2. Décrivez comment vous distingueriez une **approbation MFA légitime** d'une **attaque de fatigue MFA** dans les journaux.
3. Donnez **deux événements du plan de contrôle cloud** que vous traiteriez comme à fort signal, en rattachant chacun au risque qu'il représente.
4. Expliquez, à l'aide du modèle de responsabilité partagée, une chose que le SOC doit surveiller et que le fournisseur cloud ne surveillera pas.

:::solution Réponse type (abrégée)
1. *Connexion :* rafale d'échecs puis réussite (probable brute force/spray) ; voyage impossible (vol de jeton/identifiant) ; connexion depuis un proxy anonymisant ou un nouveau pays sur un compte privilégié. *Audit :* un nouvel octroi de rôle à privilège élevé ; un nouveau consentement d'application OAuth demandant un large accès à la messagerie/aux fichiers.
2. Une approbation légitime est une invite unique que l'utilisateur attendait, depuis son appareil/lieu habituel ; une attaque de fatigue montre **de nombreuses invites MFA dans une courte fenêtre puis une approbation**, souvent avec le mot de passe saisi depuis une IP inconnue — corrélez l'approbation avec la rafale et la source.
3. Un nouveau rôle ou une clé d'accès IAM admin/trop large (élévation de privilèges du plan de contrôle) ; la journalisation/surveillance désactivée ou un puits de journaux supprimé (attaquant effaçant ses traces). (Aussi : exposition publique de stockage ; création de ressources dans des régions inhabituelles.)
4. Selon la responsabilité partagée, le fournisseur sécurise l'infrastructure mais le **client** possède l'IAM, la configuration et les données — le SOC doit donc surveiller, par exemple, les **changements de rôle IAM et les mauvaises configurations d'exposition publique** dans ses propres journaux d'audit ; le fournisseur n'alertera pas sur un employé accordant un rôle trop large.
:::

## Résumé et étapes suivantes

- **L'identité est le périmètre** : les journaux de connexion et d'audit de l'IdP sont souvent les sources les plus importantes d'un SOC.
- Détectez la compromission de compte (échec-puis-réussite, voyage impossible, infra inhabituelle) et les attaques MFA (fatigue, vol de jeton), en lisant *comment* la MFA a été satisfaite, et surveillez le **consentement OAuth** pour la persistance.
- Le cloud ajoute le **plan de contrôle** ; le **modèle de responsabilité partagée** décide de ce que vous devez surveiller — IAM, configuration, données et toute désactivation de la journalisation.
- L'identité et le cloud sont des sources SIEM de grande valeur : collectez, normalisez, détectez, enrichissez et rattachez à ATT&CK comme toute autre.

Le module SOC final, **Opérations SOC avancées**, rassemble tout le parcours — métriques, automatisation (SOAR), purple teaming et amélioration continue — dans le cadre défensif et d'usage responsable de la plateforme.

===assignment===
Concevez un **plan de surveillance identité-et-cloud** d'une page pour une organisation de votre choix : les sources de journaux IdP et cloud que vous collecteriez ; cinq détections couvrant connexions, MFA, consentement OAuth et changements du plan de contrôle cloud (chacune avec le signal et pourquoi il compte) ; et une courte explication, à l'aide du modèle de responsabilité partagée, des couches dont votre SOC est responsable de la surveillance. Rattachez au moins deux détections à des techniques MITRE ATT&CK et notez comment vous prioriseriez par privilège.

===criteria===
- Terminez la lecture et la vérification des connaissances, et produisez le plan.
- Les détections doivent couvrir l'identité et le cloud (pas seulement les connexions), lire correctement la MFA, inclure une alerte d'altération de journalisation, et refléter le modèle de responsabilité partagée ; au moins deux doivent se rattacher à ATT&CK.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- MITRE ATT&CK Cloud Matrix — https://attack.mitre.org/matrices/enterprise/cloud/
- NIST SP 800-63, *Digital Identity Guidelines* (authentification) — https://pages.nist.gov/800-63-3/
- CISA, *Secure Cloud Business Applications (SCuBA)* — https://www.cisa.gov/resources-tools/services/secure-cloud-business-applications-scuba-project
- Cloud Security Alliance, *Modèle de responsabilité partagée* — https://cloudsecurityalliance.org/
- OWASP, *Prévention du credential stuffing et de la prise de contrôle de compte* — https://cheatsheetseries.owasp.org/cheatsheets/Credential_Stuffing_Prevention_Cheat_Sheet.html
