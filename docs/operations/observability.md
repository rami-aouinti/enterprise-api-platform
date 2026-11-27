# Observabilité et exploitation

## Signaux

### Métriques
- Nombre de requêtes, nombre d'erreurs et durée des requêtes par service/route/statut.
- Échecs upstream de la gateway et rejets dus aux limites de débit.
- Profondeur des files du broker, retard des consommateurs, retries et nombre de messages en DLQ.
- Nombre d'exécutions d'intégration, durée et taux d'échec terminal.
- Indicateurs du pool de connexions et de latence des requêtes de base de données.

Éviter les labels à forte cardinalité tels que les identifiants clients bruts, adresses e-mail, jetons ou URL arbitraires.

### Journaux
- Journaux structurés au format JSON.
- Inclure horodatage, sévérité, service, environnement, trace ID et correlation ID.
- Masquer les jetons, identifiants et champs sensibles.
- Garder des messages d'erreur utiles sans exposer de traces de pile aux consommateurs API.

### Traces
- Propager le W3C Trace Context à travers HTTP et la messagerie.
- Ajouter des spans pour les appels gateway-vers-service, base de données, broker et connecteur.
- N'enregistrer que des attributs sûrs ; ne pas attacher de payloads sensibles complets.

## Contrôles de santé

- **Liveness (vivacité) :** le processus peut continuer à fonctionner.
- **Readiness (disponibilité) :** le service peut accepter du trafic et ses dépendances nécessaires sont disponibles.
- Les endpoints de santé des dépendances doivent distinguer une dépendance dégradée d'une panne du processus.

## Premières hypothèses de SLO

Il s'agit d'objectifs d'apprentissage, pas de garanties de production :
- Objectif de disponibilité API : 99,5 % dans des scénarios de test locaux.
- Objectif de latence p95 : moins de 300 ms pour des endpoints de lecture simples sous un profil de charge défini.
- Taux de réussite des workflows d'intégration : mesuré par type de workflow, en excluant les entrées invalides explicitement rejetées.

Définir des objectifs pertinents uniquement après avoir établi des charges de test répétables.

## Modèle d'incident/runbook

Pour chaque alerte, documenter :
1. Le symptôme et l'impact.
2. Le tableau de bord et les requêtes pertinentes.
3. Les premières étapes de diagnostic.
4. La mesure d'atténuation sûre.
5. La vérification du rétablissement.
6. Le suivi et le ticket de prévention.

## Tests de performance

Utiliser un scénario k6 répétable avec volume de données, concurrence, durée, environnement et seuils documentés. Rapporter p50/p95/p99, débit, taux d'erreur et utilisation des ressources. Ne pas comparer des résultats obtenus dans des environnements matériellement différents comme s'ils étaient équivalents.
