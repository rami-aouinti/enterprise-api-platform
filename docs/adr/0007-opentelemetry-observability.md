# ADR-0007 : OpenTelemetry pour l'observabilité de la plateforme

- **Statut :** Proposé
- **Date :** 2026-11-27
- **Ticket associé :** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Contexte

Les requêtes et messages traversent la gateway, les API métier, les brokers et le moteur d'intégration. Des journaux séparés sans contexte commun compliquent le diagnostic des latences, erreurs, retries et comportements interservices.

## Décision

Utiliser les API/SDK OpenTelemetry et les conventions sémantiques comme socle d'instrumentation. Propager le contexte de trace à travers HTTP et la messagerie. Instrumenter les requêtes entrantes/sortantes, les opérations de base de données lorsque cela est pris en charge, ainsi que la production et la consommation de messages.

Pour le premier environnement local, utiliser Prometheus pour les métriques, Grafana pour les tableaux de bord, Loki pour les journaux et Jaeger pour l'exploration des traces. Garder l'instrumentation applicative indépendante des fournisseurs afin que les exporters et backends puissent évoluer séparément.

Définir des signaux utiles au niveau des services : taux de requêtes, taux d'erreurs, distributions de latence, saturation/ressources, profondeur des files/retard des consommateurs, nombre de retries et de messages en DLQ, résultats des exécutions d'intégration.

## Alternatives étudiées

1. Instrumentation spécifique à un fournisseur : pratique dans certains environnements, mais couple le code applicatif au backend.
2. Journaux uniquement : insuffisants pour détailler les latences et la causalité des requêtes distribuées.
3. Métriques uniquement : utiles pour la santé globale, mais peu adaptées au débogage de flux interservices individuels.

## Conséquences

**Avantages :** traces corrélées entre frontières synchrones et asynchrones ; instrumentation portable ; meilleures preuves opérationnelles pour les décisions de performance et de fiabilité.

**Inconvénients :** l'instrumentation et la configuration du collector ajoutent de la complexité ; les attributs à forte cardinalité et les données sensibles créent des risques de coût et de confidentialité ; échantillonnage et rétention nécessitent des valeurs par défaut réfléchies.

## Sécurité et exploitation

Ne jamais enregistrer de jetons Bearer, mots de passe, clés API ou données personnelles sans restriction dans la télémétrie. Masquer les attributs sensibles, contrôler la cardinalité des labels, configurer l'échantillonnage et la rétention et limiter l'accès aux journaux/traces. Surveiller la santé du collector et les pertes de télémétrie.

## Critères de révision

Revoir les exporters et backends après le premier flux instrumenté de bout en bout et vérifier que la stack fournit des signaux exploitables sans consommer trop de ressources locales.
