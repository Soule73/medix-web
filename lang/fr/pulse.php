<?php

return [
    'system-status' => "État du système",
    // Cache
    'cache-card-header-name' => 'Cache',
    'cache-card-header-title' => 'Temps global : :allTime ms ; Exécution globale à :allRunAt ; Temps de la clé : :keyTime ms ; Exécution de la clé à :keyRunAt ;',
    'cache-card-header-details' => 'il y a :periodForHumans',
    'cache-card-action-message' => "Les clés peuvent être normalisées à l'aide de groupes.\n\nIl y a actuellement %s %d %s configurés.",
    'hits' => 'Requêtes réussies',
    'misses' => 'Requêtes ratées',
    'hit-rate' => 'Taux de réussite',
    'key' => 'Clé',
    'cache-card-limited-to-entries' => 'Limité à :limit entrées',
    'cache-card-sample-rate-raw' => "Taux d'échantillonnage : :sample_rate, Valeur brute : :raw_value",

    // Exceptions
    'exception-card-header-name' => 'Exceptions',
    'exception-card-header-title' => 'Temps : :time ms ; Exécution à :runAt ;',
    'sort-by' => 'Trier par',
    'count' => 'Nombre',
    'latest' => 'Dernier',

    // Queues
    'queues-card-header-name' => "Files d'attente",
    'queued' => 'En attente',
    'processing' => 'En traitement',
    'processed' => 'Traités',
    'released' => 'Libérés',
    'failed' => 'Échoués',

    // Tâches lentes
    'slow-jobs-card-header-name' => 'Tâches lentes',

    // Requêtes sortantes lentes
    'slow-outgoing-requests-header-name' => 'Requêtes sortantes lentes',
    'slow-outgoing-requests-header-details' => 'Seuil de :threshold ms, il y a :periodForHumans',
    'slow-outgoing-requests-header-action-message' => "Les URI peuvent être normalisés à l'aide de groupes.\n\nIl y a :s0 actuellement :d :s1 configurés.",

    // Requêtes lentes
    'slow-queries-header-name' => 'Requêtes lentes',
    // Requêtes lentes
    'slow-requests-header-name' => 'Requêtes lentes',

    // Utilisation
    'top-users-making-requests' => ':count utilisateurs effectuant le plus de requêtes',
    'top-users-experiencing-slow-endpoints' => ':count utilisateurs rencontrant des points de terminaison lents',
    'top-users-dispatching-jobs' => ':count utilisateurs lançant le plus de tâches',
    'application-usage' => "Utilisation de l'application",
    'top-users' => ':count utilisateurs principaux',
    'making-requests' => 'effectuant des requêtes',
    'experiencing-slow-endpoints' => 'rencontrant des points de terminaison lents',
    'dispatching-jobs' => 'lançant des tâches',

    'period' => 'Période',
    'hour' => 'Heure',
    'hours' => 'Heures',
    'days' => 'Jours',
    'slowest' => 'Les plus lentes',
    'job' => 'Tâche',
    'unknown' => 'Inconnu',
    'uri' => 'URI',
    'query' => 'Requête',
    'method' => 'Méthode',
    'route' => 'Route',
    'are' => 'sont',
    'is' => 'est',

    'a-hour' => 'une heure',
    '6-hour' => '6 heures',
    '24-hour' => '24 heures',
    '7-days' => '7 jours',

    'no-results' => 'Aucun résultat',
];
