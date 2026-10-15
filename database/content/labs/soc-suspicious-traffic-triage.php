<?php

return [
    'slug' => 'soc-suspicious-traffic-triage',
    'path' => 'SOC',
    'course' => 'soc-analyst-professional',
    'module' => 'networking-for-soc',
    'type' => 'simulation',
    'difficulty' => 'intermediate',
    'duration' => 50,
    'pass' => 70,
    'required' => true,
    'risk' => 'standard',
    'dataset' => 'soc-network-flows.csv',

    'en' => [
        'title' => 'SOC Lab: Suspicious Traffic Triage',
        'summary' => 'Analyse a synthetic firewall flow log to find a port scan, command-and-control beaconing and data exfiltration.',
        'objectives' => [
            'Recognise a vertical port scan in flow data',
            'Identify command-and-control beaconing by its interval and shape',
            'Spot data exfiltration by outbound volume and asymmetry',
            'Attribute the activity to internal hosts',
        ],
        'tools' => ['The in-browser dataset viewer', 'Optionally a spreadsheet for sorting by bytes_out'],
        'prerequisites' => ['TCP/IP, the Handshake and Suspicious Traffic'],
        'scenario' => "You are reviewing an hour of firewall flow records from a segment of the corporate network. Three different suspicious behaviours are present. Identify each and attribute it to a host.",
        'setup' => "The flow log is loaded below. Fields: `timestamp` (UTC), `src_ip`, `dst_ip`, `dst_port`, `protocol`, `action`, `bytes_out`, `bytes_in`.",
        'instructions' => "1. Look for one source contacting many destination ports on a single host in quick succession, mostly denied — a vertical port scan.\n2. Look for a host contacting the same external IP at a near-constant interval with small, similar payloads — beaconing.\n3. Look for a large outbound transfer to a single external destination, with bytes_out far exceeding bytes_in — exfiltration.\n4. Answer the questions below.",
        'expected_results' => "You should attribute a port scan, a C2 beacon and an exfiltration transfer, two of which involve the same compromised host.",
        'scoring' => 'Each task is worth the points shown. You need 70% to pass. Answers are scored on the server.',
        'solution' => "- **Port scan:** `10.0.9.77` scans `10.0.2.15` across sequential ports within seconds, mostly with `deny` actions and a few `allow` on common ports (22, 80, 135, 139, 445).\n- **C2 beaconing:** `10.0.5.20` connects to `198.51.100.77:443` roughly every 60 seconds with near-identical small byte counts — classic beaconing.\n- **Exfiltration:** `10.0.5.20` also sends about **3.8 GB** to `203.0.113.200:443`, with bytes_out vastly exceeding bytes_in.\n\nThe same host (10.0.5.20) is both beaconing and exfiltrating, indicating a compromised endpoint under C2 control.",
        'cleanup' => 'No cleanup is required.',
        'lessons_learned' => "Flow logs alone reveal scanning, beaconing and exfiltration by shape, interval and volume — even without packet contents. Correlating the beacon and the exfiltration to the same host turns three alerts into one incident. Enrichment such as destination rarity and first-seen time makes these detections reliable.",
    ],
    'fr' => [
        'title' => 'Lab SOC : tri du trafic suspect',
        'summary' => 'Analysez un journal de flux pare-feu synthétique pour y trouver un balayage de ports, un beaconing de commande et contrôle et une exfiltration de données.',
        'objectives' => [
            'Reconnaître un balayage de ports vertical dans des données de flux',
            'Identifier un beaconing de commande et contrôle par son intervalle et sa forme',
            'Repérer une exfiltration par le volume sortant et l\'asymétrie',
            'Attribuer l\'activité à des hôtes internes',
        ],
        'tools' => ['La visionneuse de jeu de données intégrée', 'Au besoin, un tableur pour trier par bytes_out'],
        'prerequisites' => ['TCP/IP, la poignée de main et le trafic suspect'],
        'scenario' => "Vous examinez une heure d'enregistrements de flux pare-feu d'un segment du réseau de l'entreprise. Trois comportements suspects différents sont présents. Identifiez chacun et attribuez-le à un hôte.",
        'setup' => "Le journal de flux est chargé ci-dessous. Champs : `timestamp` (UTC), `src_ip`, `dst_ip`, `dst_port`, `protocol`, `action`, `bytes_out`, `bytes_in`.",
        'instructions' => "1. Cherchez une source contactant de nombreux ports de destination sur un seul hôte en succession rapide, majoritairement refusés — un balayage vertical.\n2. Cherchez un hôte contactant la même IP externe à intervalle quasi constant avec des charges faibles et similaires — du beaconing.\n3. Cherchez un gros transfert sortant vers une seule destination externe, avec bytes_out très supérieur à bytes_in — une exfiltration.\n4. Répondez aux questions ci-dessous.",
        'expected_results' => "Vous devriez attribuer un balayage de ports, une balise C2 et un transfert d'exfiltration, dont deux impliquent le même hôte compromis.",
        'scoring' => 'Chaque tâche vaut les points indiqués. Il faut 70 % pour réussir. Les réponses sont notées côté serveur.',
        'solution' => "- **Balayage de ports :** `10.0.9.77` balaie `10.0.2.15` sur des ports successifs en quelques secondes, surtout avec des actions `deny` et quelques `allow` sur des ports courants (22, 80, 135, 139, 445).\n- **Beaconing C2 :** `10.0.5.20` se connecte à `198.51.100.77:443` environ toutes les 60 secondes avec des volumes d'octets faibles et quasi identiques — du beaconing classique.\n- **Exfiltration :** `10.0.5.20` envoie aussi environ **3,8 Go** vers `203.0.113.200:443`, avec bytes_out très supérieur à bytes_in.\n\nLe même hôte (10.0.5.20) émet des balises et exfiltre, ce qui indique un poste compromis sous contrôle C2.",
        'cleanup' => 'Aucun nettoyage n\'est requis.',
        'lessons_learned' => "Les journaux de flux seuls révèlent le balayage, le beaconing et l'exfiltration par leur forme, leur intervalle et leur volume — même sans le contenu des paquets. Corréler la balise et l'exfiltration au même hôte transforme trois alertes en un seul incident. L'enrichissement (rareté de la destination, date de première observation) rend ces détections fiables.",
    ],

    'tasks' => [
        [
            'type' => 'text', 'points' => 20, 'spec' => ['accepted' => ['10.0.9.77']],
            'en' => ['prompt' => 'Which internal host performs a vertical port scan?', 'hint' => 'One source hits many ports on a single destination, mostly denied.'],
            'fr' => ['prompt' => 'Quel hôte interne réalise un balayage de ports vertical ?', 'hint' => 'Une source frappe de nombreux ports sur une seule destination, surtout refusés.'],
        ],
        [
            'type' => 'text', 'points' => 20, 'spec' => ['accepted' => ['10.0.2.15']],
            'en' => ['prompt' => 'Which host is the target of that port scan?', 'hint' => 'Filter on the scanning source and read the destination IP.'],
            'fr' => ['prompt' => 'Quel hôte est la cible de ce balayage de ports ?', 'hint' => 'Filtrez sur la source du balayage et lisez l\'IP de destination.'],
        ],
        [
            'type' => 'text', 'points' => 20, 'spec' => ['accepted' => ['198.51.100.77']],
            'en' => ['prompt' => 'What external IP address is the beaconing destination?', 'hint' => 'Look for ~60-second intervals and near-identical small byte counts.'],
            'fr' => ['prompt' => 'Quelle adresse IP externe est la destination du beaconing ?', 'hint' => 'Cherchez des intervalles d\'environ 60 secondes et des volumes faibles quasi identiques.'],
        ],
        [
            'type' => 'text', 'points' => 20, 'spec' => ['accepted' => ['10.0.5.20']],
            'en' => ['prompt' => 'Which internal host is both beaconing and exfiltrating data?', 'hint' => 'The beacon source is also the source of the large outbound transfer.'],
            'fr' => ['prompt' => 'Quel hôte interne émet des balises ET exfiltre des données ?', 'hint' => 'La source de la balise est aussi la source du gros transfert sortant.'],
        ],
        [
            'type' => 'choice', 'points' => 20, 'spec' => ['correct' => 'b'],
            'en' => ['prompt' => 'Which single signal best distinguishes the exfiltration transfer?', 'options' => ['a' => 'Many denied connections to many ports', 'b' => 'Large bytes_out to one destination, far exceeding bytes_in', 'c' => 'Connections at an exact 60-second interval', 'd' => 'Traffic to a well-known content delivery network'], 'explanation' => 'Exfiltration shows a large, asymmetric outbound volume to a single destination. Regular intervals point to beaconing; many denied ports point to scanning.'],
            'fr' => ['prompt' => 'Quel signal unique distingue le mieux le transfert d\'exfiltration ?', 'options' => ['a' => 'De nombreuses connexions refusées vers de nombreux ports', 'b' => 'Un gros bytes_out vers une seule destination, très supérieur à bytes_in', 'c' => 'Des connexions à intervalle exact de 60 secondes', 'd' => 'Du trafic vers un réseau de diffusion de contenu bien connu'], 'explanation' => 'L\'exfiltration présente un volume sortant important et asymétrique vers une seule destination. Les intervalles réguliers indiquent du beaconing ; de nombreux ports refusés indiquent un balayage.'],
        ],
    ],
];
