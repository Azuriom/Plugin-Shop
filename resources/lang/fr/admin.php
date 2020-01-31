<?php

return [
    'nav' => [
        'title' => 'Boutique',

        'settings' => 'Paramètres',
        'packages' => 'Produits',
        'gateways' => 'Moyens de paiements',
        'offers' => 'Offres',
        'payments' => 'Payements',
        'purchases' => 'Achats',
    ],

    'categories' => [
        'title' => 'Catégories',
        'title-edit' => 'Edition de la catégorie :category',
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
        'title-edit' => 'Edition de l\'offre :offer',
        'title-create' => 'Création d\'une offre.',

        'status' => [
            'created' => 'L\'offre a été ajoutée.',
            'updated' => 'L\'offre a été mise à jour.',
            'deleted' => 'L\'offre a été supprimée.',
        ],

        'enable' => 'Activer cette offre.'
    ],

    'packages' => [
        'title' => 'Produits',
        'title-edit' => 'Edition du produit :package',
        'title-create' => 'Création d\'un produit.',

        'status' => [
            'created' => 'Le produit a été ajouté.',
            'updated' => 'Le produit a été mis à jour.',
            'deleted' => 'Le produit a été supprimé.',

            'order-updated' => 'Les produits ont été mis à jours.'
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
        'title-edit' => 'Edition du moyen de paiement :gateway',
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
        'service-id' => 'ID Service',
        'client-id' => 'ID Client',
        'env' => 'Environnent',

        'paypal-email' => 'Adresse E-Mail PayPal',

        'enable' => 'Activer ce moyen de paiement'
    ],

    'payments' => [
        'title' => 'Paiements',

        'fields' => [
            'status' => 'Status',
            'payment-id' => 'ID Paiement',
        ]
    ],

    'purchases' => [
        'title' => 'Achats',
    ],

    'settings' => [
        'use-site-money' => 'Activer les achats avec l\'argent du site.',
    ]
];