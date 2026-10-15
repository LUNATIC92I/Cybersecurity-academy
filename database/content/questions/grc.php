<?php

$opt = static fn (string $en, string $fr, bool $c = false): array => ['en' => $en, 'fr' => $fr, 'correct' => $c];
$qq = static function (string $type, string $difficulty, int $points, array $en, array $fr, array $options = [], ?array $spec = null): array {
    $row = ['type' => $type, 'difficulty' => $difficulty, 'points' => $points, 'en' => $en, 'fr' => $fr];
    if ($options) {
        $row['options'] = $options;
    }
    if ($spec !== null) {
        $row['spec'] = $spec;
    }
    return $row;
};

return [
    'course' => 'grc-professional',
    'banks' => [
        'grc-core' => [
            'name' => 'GRC — Fundamentals and risk', 'module' => 'risk-management',
            'questions' => [
                $qq('single', 'easy', 1,
                    ['stem' => 'How is risk most commonly expressed in introductory risk management?'],
                    ['stem' => 'Comment le risque est-il le plus souvent exprimé en introduction à la gestion des risques ?'],
                    [$opt('Threat minus control', 'Menace moins mesure'), $opt('Likelihood × Impact', 'Vraisemblance × Impact', true),
                     $opt('Asset value only', 'Valeur de l\'actif uniquement'), $opt('Number of vulnerabilities', 'Nombre de vulnérabilités')]),
                $qq('single', 'medium', 1,
                    ['stem' => 'What is residual risk?'],
                    ['stem' => 'Qu\'est-ce que le risque résiduel ?'],
                    [$opt('Risk before any controls', 'Le risque avant toute mesure'),
                     $opt('The risk that remains after treatment is applied', 'Le risque qui subsiste après l\'application du traitement', true),
                     $opt('The cost of a control', 'Le coût d\'une mesure'), $opt('Risk that cannot be measured', 'Un risque non mesurable')]),
                $qq('numeric', 'easy', 1,
                    ['stem' => 'A risk has likelihood 4 and impact 5 on a 1–5 scale. What is its inherent score (likelihood × impact)?'],
                    ['stem' => 'Un risque a une vraisemblance de 4 et un impact de 5 sur une échelle de 1 à 5. Quel est son score inhérent (vraisemblance × impact) ?'],
                    [], ['value' => 20, 'tolerance' => 0]),
                $qq('single', 'medium', 1,
                    ['stem' => 'In the Three Lines Model, which line owns and manages risk day to day?'],
                    ['stem' => 'Dans le modèle des trois lignes, quelle ligne détient et gère le risque au quotidien ?'],
                    [$opt('First line (operational management)', 'Première ligne (management opérationnel)', true),
                     $opt('Second line (risk and compliance functions)', 'Deuxième ligne (fonctions risque et conformité)'),
                     $opt('Third line (internal audit)', 'Troisième ligne (audit interne)'),
                     $opt('External auditors', 'Auditeurs externes')]),
                $qq('true_false', 'easy', 1,
                    ['stem' => 'A risk register should always record an owner for each risk.'],
                    ['stem' => 'Un registre des risques doit toujours consigner un propriétaire pour chaque risque.'],
                    [$opt('True', 'Vrai', true), $opt('False', 'Faux')]),
                $qq('multiple', 'medium', 2,
                    ['stem' => 'Which are valid risk treatment options? (Select all that apply.)'],
                    ['stem' => 'Quelles sont des options de traitement du risque valides ? (Sélectionnez toutes les bonnes réponses.)'],
                    [$opt('Mitigate (reduce)', 'Atténuer (réduire)', true), $opt('Transfer (e.g. insurance)', 'Transférer (p. ex. assurance)', true),
                     $opt('Accept', 'Accepter', true), $opt('Ignore and hide', 'Ignorer et dissimuler')]),
            ],
        ],
        'grc-frameworks' => [
            'name' => 'GRC — ISO 27001 and NIST CSF', 'module' => 'iso-27001',
            'questions' => [
                $qq('single', 'medium', 1,
                    ['stem' => 'In ISO/IEC 27001, what does the Statement of Applicability document?'],
                    ['stem' => 'Dans l\'ISO/IEC 27001, que documente la déclaration d\'applicabilité ?'],
                    [$opt('The list of all employees', 'La liste de tous les employés'),
                     $opt('Which controls are applicable, their status and justification for inclusion or exclusion', 'Les mesures applicables, leur statut et la justification de leur inclusion ou exclusion', true),
                     $opt('The network diagram', 'Le schéma réseau'),
                     $opt('The incident response plan only', 'Uniquement le plan de réponse à incident')]),
                $qq('single', 'easy', 1,
                    ['stem' => 'The NIST Cybersecurity Framework 2.0 added which new core function?'],
                    ['stem' => 'Le NIST Cybersecurity Framework 2.0 a ajouté quelle nouvelle fonction principale ?'],
                    [$opt('Govern', 'Gouverner (Govern)', true), $opt('Identify', 'Identifier'), $opt('Protect', 'Protéger'), $opt('Recover', 'Rétablir')]),
                $qq('matching', 'hard', 3,
                    ['stem' => 'Match each NIST CSF function to its purpose.'],
                    ['stem' => 'Associez chaque fonction du NIST CSF à son objectif.'],
                    [
                        ['en' => 'Identify', 'fr' => 'Identifier', 'match_key' => '1', 'match_en' => 'Understand assets, risks and context', 'match_fr' => 'Comprendre les actifs, les risques et le contexte'],
                        ['en' => 'Protect', 'fr' => 'Protéger', 'match_key' => '2', 'match_en' => 'Implement safeguards', 'match_fr' => 'Mettre en place des protections'],
                        ['en' => 'Detect', 'fr' => 'Détecter', 'match_key' => '3', 'match_en' => 'Find events and anomalies', 'match_fr' => 'Repérer les événements et anomalies'],
                        ['en' => 'Respond', 'fr' => 'Répondre', 'match_key' => '4', 'match_en' => 'Act on detected incidents', 'match_fr' => 'Agir sur les incidents détectés'],
                    ]),
                $qq('single', 'medium', 1,
                    ['stem' => 'Which is a detective control?'],
                    ['stem' => 'Laquelle est une mesure détective ?'],
                    [$opt('Door lock', 'Serrure de porte'), $opt('Log review and alerting', 'Revue des journaux et alertes', true),
                     $opt('Firewall rule blocking traffic', 'Règle de pare-feu bloquant le trafic'), $opt('Encryption at rest', 'Chiffrement au repos')]),
                $qq('single', 'medium', 1,
                    ['stem' => 'In business continuity, what does RPO (Recovery Point Objective) define?'],
                    ['stem' => 'En continuité d\'activité, que définit le RPO (Recovery Point Objective) ?'],
                    [$opt('How long recovery may take', 'La durée que peut prendre la reprise'),
                     $opt('The maximum acceptable amount of data loss, measured in time', 'La perte de données maximale acceptable, mesurée en temps', true),
                     $opt('The cost of recovery', 'Le coût de la reprise'), $opt('The number of backups kept', 'Le nombre de sauvegardes conservées')]),
                $qq('true_false', 'medium', 1,
                    ['stem' => 'Inherent risk is the level of risk remaining after controls are applied.'],
                    ['stem' => 'Le risque inhérent est le niveau de risque subsistant après application des mesures.'],
                    [$opt('True', 'Vrai'), $opt('False', 'Faux', true)]),
            ],
        ],
    ],
    'quizzes' => [
        ['slug' => 'grc-professional-final-exam', 'kind' => 'final_exam',
         'count' => 12, 'duration' => 30, 'pass' => 70, 'max_attempts' => 5, 'cooldown' => 60, 'explanations' => 'after_pass',
         'banks' => ['grc-core' => 6, 'grc-frameworks' => 6],
         'en' => ['title' => 'GRC Professional — Final Exam', 'description' => 'Twelve questions on risk management, ISO/IEC 27001 and the NIST CSF. Pass with 70% to earn your certificate.'],
         'fr' => ['title' => 'Professionnel GRC — Examen final', 'description' => 'Douze questions sur la gestion des risques, l\'ISO/IEC 27001 et le NIST CSF. Réussissez avec 70 % pour obtenir votre certificat.']],
    ],
];
