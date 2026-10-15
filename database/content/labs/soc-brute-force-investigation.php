<?php

/**
 * Browser-based SOC lab: investigate a brute-force attack in a synthetic
 * Windows authentication log. Answer specs are checked server-side only.
 */

return [
    'slug' => 'soc-brute-force-investigation',
    'path' => 'SOC',
    'course' => 'soc-analyst-professional',
    'module' => 'kql-detection-engineering',
    'type' => 'simulation',
    'difficulty' => 'intermediate',
    'duration' => 45,
    'pass' => 70,
    'required' => true,
    'risk' => 'standard',
    'dataset' => 'soc-auth-events.csv',

    'en' => [
        'title' => 'SOC Lab: Brute-Force Investigation',
        'summary' => 'Investigate a synthetic Windows authentication log, identify a brute-force attack that succeeds, and document the compromise.',
        'objectives' => [
            'Recognise a brute-force pattern in Windows 4625 events',
            'Identify the source, the targeted account and the moment of compromise',
            'Distinguish the attack from ordinary failed logons',
            'Recommend containment actions',
        ],
        'tools' => ['The in-browser dataset viewer (filter and sort)', 'Optionally a spreadsheet or grep on the downloaded CSV'],
        'prerequisites' => ['Windows Event Logs for Investigators', 'Detecting Brute-Force and Password-Spraying Attacks'],
        'scenario' => "A detection fired overnight on the file server **FS-01**. You have been given one hour of Windows Security events (Event ID 4624 = success, 4625 = failure) exported to CSV. The dataset is shown below; use the filter box to narrow it down.\n\nYour job is to confirm whether an attack succeeded and to document what happened, so a Tier 2 analyst can act.",
        'setup' => "The dataset is already loaded in the viewer below and can also be downloaded. Fields: `timestamp` (UTC), `event_id`, `target_user`, `source_ip`, `logon_type`, `substatus`, `computer`, `result`.\n\n:::tip Filtering\nType a term such as `4625` to see only failures, or an IP address to isolate one source. Combine terms separated by spaces.\n:::",
        'instructions' => "1. Filter to failures (`4625`) and look for a single source IP generating many failures against one account in a short window.\n2. Note the source IP and the targeted account.\n3. Count the failures from that source against that account.\n4. Switch to successes (`4624`) from the same source and find the logon that follows the failures.\n5. Note the time of the successful logon and its logon type.\n6. Answer the questions below.",
        'expected_results' => "You should find a clear burst of failures from one internal IP against one service account, ending in a successful network logon within seconds — the signature of a successful brute-force attack.",
        'scoring' => "Each task is worth the points shown. You need 70% to pass. Answers are checked on the server; the dataset viewer never reveals the expected answers.",
        'solution' => "The attack comes from **10.0.9.77** against the **svc_backup** account.\n\n- Filtering on `4625` and `10.0.9.77` shows **38** failed logons with substatus `0xC000006A` (valid user, wrong password) between 01:52 and 01:56.\n- Filtering on `4624` and `10.0.9.77` shows a **successful network logon (type 3)** for `svc_backup` at **2026-03-14 01:56:41**, seconds after the last failure.\n- The two ordinary failures earlier in the log are for `l.chen` from a workstation (type 2) and are immediately followed by a success — a user mistyping a password, not an attack.\n\nThis is a successful brute-force attack (MITRE ATT&CK T1110.001) followed by use of valid credentials (T1078). Containment: disable `svc_backup`, isolate FS-01 and the source host 10.0.9.77, and investigate activity after 01:56:41.",
        'cleanup' => 'No cleanup is required for this browser-based lab.',
        'lessons_learned' => "The highest-value identity detection is *failures followed by a success from the same source*. A per-account lockout policy and MFA on service accounts would have prevented this; a SIEM rule joining 4625 bursts to a later 4624 from the same source and account would have alerted on it in near real time.",
    ],
    'fr' => [
        'title' => 'Lab SOC : investigation de force brute',
        'summary' => 'Investiguez un journal d\'authentification Windows synthétique, identifiez une attaque par force brute qui réussit et documentez la compromission.',
        'objectives' => [
            'Reconnaître un schéma de force brute dans les événements Windows 4625',
            'Identifier la source, le compte visé et le moment de la compromission',
            'Distinguer l\'attaque des échecs de connexion ordinaires',
            'Recommander des actions de confinement',
        ],
        'tools' => ['La visionneuse de jeu de données intégrée (filtrer et trier)', 'Au besoin, un tableur ou grep sur le CSV téléchargé'],
        'prerequisites' => ['Les journaux d\'événements Windows pour l\'investigation', 'Détecter la force brute et la pulvérisation de mots de passe'],
        'scenario' => "Une détection s'est déclenchée pendant la nuit sur le serveur de fichiers **FS-01**. On vous confie une heure d'événements Windows Security (événement 4624 = succès, 4625 = échec) exportés en CSV. Le jeu de données s'affiche ci-dessous ; utilisez le champ de filtre pour l'affiner.\n\nVotre mission : confirmer si une attaque a réussi et documenter ce qui s'est passé, pour qu'un analyste de niveau 2 puisse agir.",
        'setup' => "Le jeu de données est déjà chargé dans la visionneuse ci-dessous et peut aussi être téléchargé. Champs : `timestamp` (UTC), `event_id`, `target_user`, `source_ip`, `logon_type`, `substatus`, `computer`, `result`.\n\n:::tip Filtrage\nTapez un terme comme `4625` pour ne voir que les échecs, ou une adresse IP pour isoler une source. Combinez les termes séparés par des espaces.\n:::",
        'instructions' => "1. Filtrez sur les échecs (`4625`) et cherchez une IP source unique générant de nombreux échecs contre un même compte sur une courte période.\n2. Notez l'IP source et le compte visé.\n3. Comptez les échecs de cette source contre ce compte.\n4. Passez aux succès (`4624`) de la même source et trouvez la connexion qui suit les échecs.\n5. Notez l'heure de la connexion réussie et son type d'ouverture de session.\n6. Répondez aux questions ci-dessous.",
        'expected_results' => "Vous devriez trouver une rafale nette d'échecs depuis une IP interne contre un compte de service, se terminant par une ouverture de session réseau réussie en quelques secondes — la signature d'une force brute réussie.",
        'scoring' => "Chaque tâche vaut les points indiqués. Il faut 70 % pour réussir. Les réponses sont vérifiées côté serveur ; la visionneuse ne révèle jamais les réponses attendues.",
        'solution' => "L'attaque vient de **10.0.9.77** contre le compte **svc_backup**.\n\n- En filtrant sur `4625` et `10.0.9.77`, on voit **38** connexions échouées avec le sous-état `0xC000006A` (utilisateur valide, mauvais mot de passe) entre 01:52 et 01:56.\n- En filtrant sur `4624` et `10.0.9.77`, on voit une **ouverture de session réseau réussie (type 3)** pour `svc_backup` à **2026-03-14 01:56:41**, quelques secondes après le dernier échec.\n- Les deux échecs ordinaires plus tôt dans le journal concernent `l.chen` depuis un poste (type 2) et sont immédiatement suivis d'un succès — un utilisateur qui se trompe de mot de passe, pas une attaque.\n\nIl s'agit d'une force brute réussie (MITRE ATT&CK T1110.001) suivie de l'usage d'identifiants valides (T1078). Confinement : désactiver `svc_backup`, isoler FS-01 et l'hôte source 10.0.9.77, et investiguer l'activité après 01:56:41.",
        'cleanup' => 'Aucun nettoyage n\'est requis pour ce lab dans le navigateur.',
        'lessons_learned' => "La détection d'identité la plus précieuse est *des échecs suivis d'un succès depuis la même source*. Une politique de verrouillage par compte et la MFA sur les comptes de service auraient empêché l'attaque ; une règle SIEM joignant les rafales de 4625 à un 4624 ultérieur de la même source et du même compte aurait alerté en quasi temps réel.",
    ],

    'tasks' => [
        [
            'type' => 'text', 'points' => 20,
            'spec' => ['accepted' => ['10.0.9.77']],
            'en' => ['prompt' => 'What is the source IP address of the brute-force attack?', 'hint' => 'Filter on event 4625 and look for the IP that repeats the most.', 'explanation' => 'All 38 failures against svc_backup originate from 10.0.9.77.'],
            'fr' => ['prompt' => 'Quelle est l\'adresse IP source de l\'attaque par force brute ?', 'hint' => 'Filtrez sur l\'événement 4625 et cherchez l\'IP qui revient le plus.', 'explanation' => 'Les 38 échecs contre svc_backup proviennent tous de 10.0.9.77.'],
        ],
        [
            'type' => 'text', 'points' => 20,
            'spec' => ['accepted' => ['svc_backup']],
            'en' => ['prompt' => 'Which account was targeted and ultimately compromised?', 'hint' => 'Which target_user appears in the burst of failures?', 'explanation' => 'The service account svc_backup was targeted and then logged on successfully.'],
            'fr' => ['prompt' => 'Quel compte a été visé puis compromis ?', 'hint' => 'Quel target_user apparaît dans la rafale d\'échecs ?', 'explanation' => 'Le compte de service svc_backup a été visé puis s\'est connecté avec succès.'],
        ],
        [
            'type' => 'number', 'points' => 20,
            'spec' => ['value' => 38, 'tolerance' => 0],
            'en' => ['prompt' => 'How many failed logon attempts (event 4625) came from that source against that account?', 'hint' => 'Filter on 4625 and the source IP, then count the rows.', 'explanation' => 'There are exactly 38 failed attempts before the success.'],
            'fr' => ['prompt' => 'Combien de tentatives de connexion échouées (événement 4625) proviennent de cette source contre ce compte ?', 'hint' => 'Filtrez sur 4625 et l\'IP source, puis comptez les lignes.', 'explanation' => 'Il y a exactement 38 tentatives échouées avant le succès.'],
        ],
        [
            'type' => 'choice', 'points' => 20,
            'spec' => ['correct' => 'c'],
            'en' => ['prompt' => 'What was the logon type of the successful compromise?', 'options' => ['a' => 'Type 2 (Interactive)', 'b' => 'Type 10 (RemoteInteractive / RDP)', 'c' => 'Type 3 (Network)', 'd' => 'Type 5 (Service)'], 'explanation' => 'The successful logon for svc_backup is a type 3 (network) logon, consistent with remote authentication such as SMB or a scheduled network task.'],
            'fr' => ['prompt' => 'Quel était le type d\'ouverture de session de la compromission réussie ?', 'options' => ['a' => 'Type 2 (Interactive)', 'b' => 'Type 10 (RemoteInteractive / RDP)', 'c' => 'Type 3 (Réseau)', 'd' => 'Type 5 (Service)'], 'explanation' => 'L\'ouverture de session réussie de svc_backup est de type 3 (réseau), cohérente avec une authentification distante (SMB ou tâche réseau planifiée).'],
        ],
        [
            'type' => 'multi', 'points' => 20,
            'spec' => ['correct' => ['a', 'b', 'd']],
            'en' => ['prompt' => 'Which immediate containment actions are appropriate? (Select all that apply.)', 'options' => ['a' => 'Disable the svc_backup account', 'b' => 'Isolate FS-01 and the source host 10.0.9.77', 'c' => 'Delete the Windows Security log to stop the attacker', 'd' => 'Review all activity from svc_backup after 01:56:41'], 'explanation' => 'Deleting logs destroys evidence and is itself an attacker technique (T1070.001); never do it. The others are appropriate containment and scoping steps.'],
            'fr' => ['prompt' => 'Quelles actions de confinement immédiates sont appropriées ? (Sélectionnez toutes les bonnes réponses.)', 'options' => ['a' => 'Désactiver le compte svc_backup', 'b' => 'Isoler FS-01 et l\'hôte source 10.0.9.77', 'c' => 'Supprimer le journal Windows Security pour stopper l\'attaquant', 'd' => 'Examiner toute l\'activité de svc_backup après 01:56:41'], 'explanation' => 'Supprimer les journaux détruit les preuves et constitue une technique d\'attaquant (T1070.001) ; à ne jamais faire. Les autres sont des mesures de confinement et de délimitation appropriées.'],
        ],
    ],
];
