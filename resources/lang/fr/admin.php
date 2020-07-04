<?php

return [
    'nav' => [
        'title' => 'Boutique',

        'settings' => 'Paramètres',
        'packages' => 'Produits',
        'gateways' => 'Moyens de paiements',
        'offers' => 'Offres',
        'coupons' => 'Codes promo',
        'discounts' => 'Réductions',
        'payments' => 'Paiements',
        'purchases' => 'Achats',
        'statistics' => 'Statistique',
    ],

    'permissions' => [
        'admin' => 'Voir et gérer le plugin boutique',
    ],

    'categories' => [
        'title' => 'Catégories',
        'title-edit' => 'Édition de la catégorie :category',
        'title-create' => 'Création d\'une catégorie',

        'status' => [
            'created' => 'La catégorie a été ajoutée.',
            'updated' => 'La catégorie a été mise à jour.',
            'deleted' => 'La catégorie a été supprimée.',

            'delete-items' => 'Une catégorie contenant des produits ne peut pas être supprimée',
        ],

        'enable' => 'Activer cette catégorie',
    ],

    'offers' => [
        'title' => 'Offres',
        'title-edit' => 'Édition de l\'offre :offer',
        'title-create' => 'Création d\'une offre.',

        'status' => [
            'created' => 'L\'offre a été ajoutée.',
            'updated' => 'L\'offre a été mise à jour.',
            'deleted' => 'L\'offre a été supprimée.',
        ],

        'enable' => 'Activer cette offre.',
    ],

    'coupons' => [
        'title' => 'Codes promotionnels',
        'title-edit' => 'Modifier le code promotionnel :coupon',
        'title-create' => 'Ajouter un code promotionnel',

        'status' => [
            'created' => 'Le code promotionnel a été ajouté.',
            'updated' => 'Le code promotionnel a été mis à jour.',
            'deleted' => 'Le code promotionnel a été supprimé.',
        ],

        'global' => 'Est ce que ce code promotionnel doit être actif sur toute la boutique ?',

        'active' => 'Actif',
        'enable' => 'Activer ce code promotionnel',
    ],

    'discounts' => [
        'title' => 'Réductions',
        'title-edit' => 'Édition de la réduction :discount',
        'title-create' => 'Création d\'une réduction.',

        'status' => [
            'created' => 'La réduction a été ajoutée.',
            'updated' => 'La réduction a été mise à jour.',
            'deleted' => 'La réduction a été supprimée.',
        ],

        'global' => 'La réduction doit-elle être active sur toute la boutique ?',

        'active' => 'Active',
        'enable' => 'Activer cette réduction',
    ],

    'packages' => [
        'title' => 'Produits',
        'title-edit' => 'Édition du produit :package',
        'title-create' => 'Création d\'un produit',

        'status' => [
            'created' => 'Le produit a été ajouté.',
            'updated' => 'Le produit a été mis à jour.',
            'deleted' => 'Le produit a été supprimé.',

            'order-updated' => 'Les produits ont été mis à jour.',
        ],

        'commands-info' => 'Vous pouvez utiliser la variable <code>{player}</code> pour utiliser le pseudo du joueur.',

        'need-online' => 'L\'utilisateur doit être en ligne pour recevoir le paquet (uniquement disponible avec AzLink)',
        'enable-quantity' => 'Activer la quantité',

        'create-category' => 'Créer une catégorie',
        'create-package' => 'Créer un produit',

        'enable' => 'Activer ce produit',
    ],

    'gateways' => [
        'title' => 'Moyens de paiements',
        'title-edit' => 'Édition du moyen de paiement :gateway',
        'title-create' => 'Ajout d\'un moyen de paiement',

        'subtitle-current' => 'Moyen de paiement actuel',
        'subtitle-add' => 'Ajout d\'un nouveau moyen de paiement',

        'status' => [
            'created' => 'Le moyen de paiement a été ajouté.',
            'updated' => 'Le moyen de paiement a été mis à jour.',
            'deleted' => 'Le moyen de paiement a été supprimé.',
        ],

        'api-key' => 'Clé API',
        'public-key' => 'Clé Publique',
        'private-key' => 'Clé Privée',
        'secret-key' => 'Clé Secrète',
        'service-id' => 'ID du Service',
        'client-id' => 'ID du Client',
        'env' => 'Environnement',

        'paypal-email' => 'Adresse E-Mail PayPal',
        'paysafecard-info' => 'Pour pouvoir accepter les paiements par paysafecard, vous devez être un <a href="https://www.paysafecard.com/fr/business/" target="_blank" rel="noopener noreferrer">partenaire paysafecard</a>. D\'autres méthodes existent, mais celle-ci est la seule autorisée par paysafecard.',
        'stripe-info' => 'Sur le tableau de bord Stripe, vous devez définir l\'URL du webhook sur <code>:url</code>.',
        'paymentwall-info' => 'Dans le tableau de bord PaymentWall, vous devez définir l\'URL de pingback sur <code>:url</code>.',

        'enable' => 'Activer ce moyen de paiement',
    ],

    'payments' => [
        'title' => 'Paiements',

        'fields' => [
            'status' => 'Status',
            'payment-id' => 'ID du Paiement',
        ],

        'card' => 'Paiements sur la boutique',

        'payment-status' => [
            'created' => 'Créé',
            'cancelled' => 'Annulé',
            'pending' => 'En attente',
            'expired' => 'Expiré',
            'success' => 'Validé',
            'delivered' => 'Livré',
            'error' => 'Erreur',
        ],
    ],

    'purchases' => [
        'title' => 'Achats',
    ],

    'settings' => [
        'title' => 'Paramètres de la boutique',
        'use-site-money' => 'Activer les achats avec l\'argent du site.',
    ],

    'statistics' => [
        'title' => 'Statistiques',
        'stats' => [
            'global' => 'Paiements sur la boutique',
            'estimated' => 'Estimation des gains',
            'month-estimated' => 'Estimation des gains ce mois-ci',
            'month-day-estimated' => 'Estimation des gains ces 30 derniers jours',
            'month' => 'Paiements sur la boutique ce mois-ci',
            'month-day' => 'Paiements sur la boutique ces 30 derniers jours',
        ],
    ],

];
