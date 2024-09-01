<?php

return [
    'nav' => [
        'title' => 'Boutique',
        'settings' => 'Paramètres',
        'packages' => 'Produits',
        'gateways' => 'Moyens de paiements',
        'offers' => 'Offres',
        'coupons' => 'Codes promo',
        'giftcards' => 'Cartes cadeaux',
        'discounts' => 'Réductions',
        'payments' => 'Paiements',
        'subscriptions' => 'Abonnements',
        'variables' => 'Variables',
        'purchases' => 'Achats',
        'statistics' => 'Statistiques',
    ],

    'permissions' => [
        'settings' => 'Gérer les paramètres de la boutique',
        'packages' => 'Gérer les produits de la boutique',
        'gateways' => 'Gérer les moyens de paiements de la boutique',
        'promotions' => 'Gérer les offres et réductions de la boutique',
        'giftcards' => 'Gérer les cartes cadeaux de la boutique',
        'payments' => 'Ajouter et voir les paiements/achats et abonnements de la boutique',
    ],

    'categories' => [
        'title' => 'Catégories',
        'edit' => 'Édition de la catégorie :category',
        'create' => 'Création d\'une catégorie',

        'parent' => 'Catégorie parente',
        'delete_error' => 'Une catégorie contenant des produits ne peut pas être supprimée',

        'cumulate' => 'Cumuler les achats dans cette catégorie',
        'cumulate_info' => 'Les utilisateurs ayant déjà acheté un produit dans cette catégorie auront une réduction et ne paieront que la différence lors de l\'achat d\'un produit plus cher.',
        'single_purchase' => 'Limiter un utilisateur à acheter un seul produit de cette catégorie',
        'enable' => 'Activer cette catégorie',
    ],

    'offers' => [
        'title' => 'Offres',
        'edit' => 'Édition de l\'offre :offer',
        'create' => 'Création d\'une offre.',

        'enable' => 'Activer cette offre.',
    ],

    'coupons' => [
        'title' => 'Codes promotionnels',
        'edit' => 'Modifier le code promotionnel :coupon',
        'create' => 'Ajouter un code promotionnel',

        'global' => 'Est ce que ce code promotionnel doit être actif sur toute la boutique ?',
        'cumulate' => 'Pouvoir utiliser ce code promo avec d\'autres codes promo',
        'user_limit' => 'Limite d\'utilisations par utilisateur',
        'global_limit' => 'Limite d\'utilisations globale',
        'active' => 'Actif',
        'usage' => 'Utilisations restantes',
        'enable' => 'Activer ce code promotionnel',
    ],

    'giftcards' => [
        'title' => 'Cartes Cadeaux',
        'edit' => 'Modifier la carte cadeau :giftcard',
        'create' => 'Ajouter une carte cadeau',

        'global_limit' => 'Limite d\'utilisations globale',
        'active' => 'Actif',
        'enable' => 'Activer cette carte cadeau',
        'link' => 'Lien à partager',
        'pending' => 'Un paiement est en cours, le solde peut ne pas être à jour.',
    ],

    'discounts' => [
        'title' => 'Réductions',
        'edit' => 'Édition de la réduction :discount',
        'create' => 'Création d\'une réduction.',

        'global' => 'La réduction doit-elle être active sur toute la boutique ?',
        'active' => 'Active',
        'enable' => 'Activer cette réduction',
        'restricted' => 'Limiter cette réduction à certains rôles uniquement',
    ],

    'packages' => [
        'title' => 'Produits',
        'edit' => 'Édition du produit :package',
        'create' => 'Création d\'un produit',

        'every' => 'tous les',
        'after' => 'après',

        'updated' => 'Les produits ont été mis à jour.',

        'variables' => 'Variables personnalisées',
        'files' => 'Fichiers téléchargeables',
        'role' => 'Rôle à définir après l\'expiration du produit',
        'money' => 'Argent à donner à l\'utilisateur lors de l\'achat',
        'has_giftcard' => 'Créer une carte cadeau lors de l\'achat et la donner à l\'utilisateur',
        'giftcard_balance' => 'Valeur de la carte cadeau',
        'giftcard_fixed' => 'Montant fixe',
        'giftcard_dynamic' => 'Prix du produit',
        'command' => 'La commande ne doit pas commencer par <code>/</code>. Vous pouvez utiliser <code>{player}</code> pour le pseudo du joueur. Pour les jeux Steam, les variables <code>{steam_id}</code> et <code>{steam_id_32}</code> sont disponibles. Les autres variables disponibles sont : :placeholders.',
        'has_user_limit' => 'Activer une limite d\'achat par utilisateur pour ce produit',
        'has_global_limit' => 'Activer une limite d\'achat globale à tous les utilisateurs pour ce produit',
        'limit_period' => 'Période de la limite d\'achat',
        'limits_no_expired' => 'Ne pas compter les produits expirés lors du calcul des limites d\'achats',
        'no_period' => 'Aucune période',
        'custom_price' => 'Permettre à l\'utilisateur de choisir le prix à payer (le prix du produit sera alors le minimum)',
        'enable_quantity' => 'Activer la quantité',

        'billing' => 'Type de facturation',
        'billing_period' => 'Période de facturation',
        'one_off' => 'Paiement unique',
        'subscription' => 'Abonnement (renouvellement automatique)',
        'subscription_info' => 'Assurez vous que votre moyen de paiement supporte les abonnements. Une fois un abonnement créé, l\'utilisateur sera débité du même montant tant que l\'abonnement n\'est pas annulé (toute promotion active pour le premier paiement sera appliquée aux paiements suivants).',
        'expiring' => 'Paiement unique avec expiration',
        'expiring_info' => 'Le produit sera automatiquement supprimé après la période définie.',
        'scheduler' => 'Les tâches CRON ne sont pas activées, vous devez les configurer pour utiliser les abonnements et les expirations, voir la <a href="https://azuriom.com/docs/faq" target="_blank" rel="noopener norefferer">FAQ</a> pour plus d\'informations.',

        'create_category' => 'Créer une catégorie',
        'create_package' => 'Créer un produit',

        'enable' => 'Activer ce produit',
    ],

    'commands' => [
        'online' => 'Attendre que le joueur soit connecté en jeu (nécessite AzLink)',
        'offline' => 'Exécuter la commande directement, que le joueur soit connecté en jeu ou non',
        'servers' => 'Vous devez ajouter au moins un serveur pour créer une commande.',

        'trigger' => 'Événement',
        'command' => 'Commande',
        'condition' => 'Condition pour la commande',

        'triggers' => [
            'purchase' => 'Achat',
            'expiration' => 'Après expiration',
            'refund' => 'Paiement remboursé',
            'chargeback' => 'Litige / Opposition',
            'renewal' => 'Renouvellement de l\'abonnement',
        ],
    ],

    'gateways' => [
        'title' => 'Moyens de paiements',
        'edit' => 'Édition du moyen de paiement :gateway',
        'create' => 'Ajout d\'un moyen de paiement',

        'current' => 'Moyen de paiement actuel',
        'add' => 'Ajout d\'un nouveau moyen de paiement',
        'info' => 'Si vous avez des problèmes avec les paiements en utilisant Cloudflare ou un pare-feu, essayez de suivre les étapes indiquées dans la <a href="https://azuriom.com/docs/faq" target="_blank" rel="noopener norefferer">FAQ</a>.',

        'country' => 'Pays',
        'sandbox' => 'Sandbox',
        'api-key' => 'Clé API',
        'public-key' => 'Clé Publique',
        'private-key' => 'Clé Privée',
        'secret-key' => 'Clé Secrète',
        'endpoint-secret' => 'Clé Secrète de Signature',
        'service-id' => 'ID du Service',
        'client-id' => 'ID du Client',
        'merchant-id' => 'ID du vendeur',
        'project-id' => 'ID du projet',
        'webhook_id' => 'ID du webhook',
        'website_id' => 'ID du site',
        'env' => 'Environnement',

        'subscription' => 'Ce moyen de paiement supporte les abonnements avec renouvellement automatique.',
        'no_subscription' => 'Ce moyen de paiement ne supporte pas les abonnements avec renouvellement automatique.',

        'paypal_email' => 'Adresse Email PayPal',
        'paypal_info' => 'Assurez-vous d\'indiquer l\'adresse e-mail <strong>principale</strong> du compte PayPal.',
        'paypal_checkout' => 'Sur le tableau de bord PayPal, dans l\'application pour l\'API, créez un webhook avec l\'URL <code>:url</code> et les événements <code>:events</code>.',
        'stripe_info' => 'Sur le tableau de bord Stripe, vous devez définir l\'URL du webhook sur <code>:url</code> et sélectionner les événements : <code>:events</code>.',
        'paymentwall_info' => 'Dans le tableau de bord PaymentWall, vous devez définir l\'URL de pingback sur <code>:url</code>.',
        'xsolla' => 'Dans le tableau de bord Xsolla, vous devez définir l\'URL de webhook URL sur <code>:url</code>, activer \'Transaction external ID\' dans les paramètres de la \'Pay station\', tester les webhooks et ensuite activer \'Checkout\' dans les paramètres de la \'Pay Station\'.',
        'skrill_email' => 'Adresse Email Skrill',
        'skrill_secret' => 'Mot Secret',
        'skrill_info' => 'L\'ID du site et le mot secret sont disponibles sur le tableau de bord Skrill, dans l\'onglet "Developer Settings".',

        'enable' => 'Activer ce moyen de paiement',
    ],

    'payments' => [
        'title' => 'Paiements',
        'show' => 'Paiement #:payment',

        'info' => 'Informations du paiement',
        'items' => 'Objets achetés',

        'card' => 'Paiements sur la boutique',

        'status' => [
            'pending' => 'En attente',
            'expired' => 'Expiré',
            'chargeback' => 'Litige',
            'completed' => 'Complété',
            'refunded' => 'Remboursé',
            'error' => 'Erreur',
        ],
    ],

    'purchases' => [
        'title' => 'Achats',
    ],

    'subscriptions' => [
        'title' => 'Abonnements',
        'show' => 'Abonnement #:subscription',

        'info' => 'Informations de l\'abonnement',
        'error' => 'Vous devez annuler les abonnements associés pour continuer, et attendre que ceux-ci soient expirés.',
        'setup' => 'La configuration d\'un abonnement pour un produit se fait dans les paramètres du produit, juste en dessous de la configuration du prix.',

        'status' => [
            'active' => 'Actif',
            'canceled' => 'Annulé',
            'expired' => 'Expiré',
        ],
    ],

    'settings' => [
        'title' => 'Paramètres de la boutique',
        'enable_home' => 'Activer la page d\'accueil de la boutique',
        'home_message' => 'Message de la page d\'accueil',
        'use_site_money' => 'Activer les achats avec l\'argent du site.',
        'use_site_money_info' => 'Une fois activé, les produits de la boutique ne pourront être achetés qu\'avec l\'argent du site. Afin que les utilisateurs puissent créditer leur compte, vous pouvez configurer des offres dans la boutique.',
        'webhook' => 'URL de webhook Discord',
        'webhook_info' => 'Lorsqu\'un utilisateur fait un paiement sur la boutique (hors achats avec l\'argent du site!), cela va créer une notification sur ce webhook. Laissez vide pour ne pas utiliser de webhook.',
        'commands' => 'Commandes globales',
        'recent_payments' => 'Limite de paiements récents à afficher dans la barre latérale',
        'display_amount' => 'Afficher le montant dans les paiements récents et meilleur acheteur',
        'top_customer' => 'Afficher le meilleur acheteur du mois dans la barre latérale',

        'terms_required' => 'Demander aux utilisateurs d\'accepter les conditions avant un achat',
        'terms' => 'Conditions générales de vente',
        'terms_info' => 'Liens au format Markdown, par exemple : <code>Je certifie avoir lu et accepte les [CGV](/links/cgv) de la boutique</code>.',
    ],

    'variables' => [
        'title' => 'Variables',
        'edit' => 'Édition de la variable :variable',
        'create' => 'Création d\'une variable',

        'info' => 'Les variables personnalisées permettent de demander des informations supplémentaires à l\'utilisateur lors de l\'achat d\'un produit.',

        'name' => 'Le nom interne de la variable, ne peut pas être modifié après la création.',
        'required' => 'Est-ce que cette variable doit être remplie par l\'utilisateur ?',
        'options' => 'Options',

        'text' => 'Texte',
        'number' => 'Nombre',
        'email' => 'E-Mail',
        'checkbox' => 'Case à cocher',
        'dropdown' => 'Menu déroulant',
    ],

    'logs' => [
        'shop-gateways' => [
            'created' => 'Création du moyen de paiement #:id',
            'updated' => 'Mise à jour du moyen de paiement #:id',
            'deleted' => 'Suppression du moyen de paiement #:id',
        ],

        'shop-packages' => [
            'created' => 'Création du produit #:id',
            'updated' => 'Mise à jour du produit #:id',
            'deleted' => 'Suppression du produit #:id',
        ],

        'shop-offers' => [
            'created' => 'Création de l\'offre #:id',
            'updated' => 'Mise à jour de l\'offre #:id',
            'deleted' => 'Suppression l\'offre #:id',
        ],

        'shop-giftcards' => [
            'used' => 'Utilisation de la carte cadeau #:id (:amount)',
        ],

        'settings' => 'Mise à jour des paramètres de la boutique',
    ],

    'statistics' => [
        'title' => 'Statistiques',
        'total' => 'Total',
        'recent' => 'Paiements récents',
        'count' => 'Nombre de paiements',
        'estimated' => 'Estimation des revenus',
        'month' => 'Paiements sur la boutique ce mois-ci',
        'month_estimated' => 'Estimation des gains ce mois-ci',
    ],

];
