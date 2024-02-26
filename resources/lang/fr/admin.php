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
        'purchases' => 'Achats',
        'statistics' => 'Statistiques',
    ],

    'permissions' => [
        'admin' => 'Gérer le plugin boutique',
    ],

    'categories' => [
        'title' => 'Catégories',
        'edit' => 'Édition de la catégorie :category',
        'create' => 'Création d\'une catégorie',

        'parent' => 'Catégorie parente',
        'delete_error' => 'Une catégorie contenant des produits ne peut pas être supprimée',

        'cumulate' => 'Cumuler les achats dans cette catégorie',
        'cumulate_info' => 'Les utilisateurs ayant déjà acheté un produit dans cette catégorie auront une réduction et ne paieront que la différence lors de l\'achat d\'un produit plus cher.',
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
    ],

    'discounts' => [
        'title' => 'Réductions',
        'edit' => 'Édition de la réduction :discount',
        'create' => 'Création d\'une réduction.',

        'global' => 'La réduction doit-elle être active sur toute la boutique ?',
        'active' => 'Active',
        'enable' => 'Activer cette réduction',
    ],

    'packages' => [
        'title' => 'Produits',
        'edit' => 'Édition du produit :package',
        'create' => 'Création d\'un produit',

        'updated' => 'Les produits ont été mis à jour.',

        'money' => 'Argent à donner à l\'utilisateur lors de l\'achat',
        'giftcard' => 'Montant de la carte cadeau à créer lors de l\'achat',
        'command' => 'La commande ne doit pas commencer par <code>/</code>. Vous pouvez utiliser <code>{player}</code> pour le pseudo du joueur. Pour les jeux Steam, les variables <code>{steam_id}</code> et <code>{steam_id_32}</code> sont disponibles. Les autres variables disponibles sont : :placeholders.',

        'custom_price' => 'Permettre à l\'utilisateur de choisir le prix à payer (le prix du produit sera alors le minimum)',
        'enable_quantity' => 'Activer la quantité',

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
        'env' => 'Environnement',

        'paypal_email' => 'Adresse Email PayPal',
        'paypal_info' => 'Assurez vous d\'indiquer l\'adresse e-mail <strong>principale</strong> du compte PayPal.',
        'paysafecard_info' => 'Pour pouvoir accepter les paiements par paysafecard, vous devez être un <a href="https://www.paysafecard.com/fr/entreprises/" target="_blank" rel="noopener noreferrer">partenaire paysafecard</a>. D\'autres méthodes existent, mais celle-ci est la seule autorisée par paysafecard.',
        'stripe_info' => 'Sur le tableau de bord Stripe, vous devez définir l\'URL du webhook sur <code>:url</code> et séléctionner l\'événement <code>checkout.session.completed</code>.',
        'paymentwall_info' => 'Dans le tableau de bord PaymentWall, vous devez définir l\'URL de pingback sur <code>:url</code>.',
        'xsolla' => 'Dans le tableau de bord Xsolla vous devez définir l\'URL de webhook URL sur <code>:url</code>, activer \'Transaction external ID\' dans les paramètres de la \'Pay station\', tester les webhooks et ensuite activer \'Checkout\' dans les paramètres de la \'Pay Station\'.',

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
        'terms_link' => 'Liens vers les conditions générales de vente',
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
