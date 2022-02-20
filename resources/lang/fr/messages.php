<?php

return [
    'title' => 'Boutique',
    'welcome' => 'Welcome on the shop',

    'buy' => 'Acheter',

    'free' => 'Gratuit',

    'fields' => [
        'price' => 'Prix',
        'total' => 'Total',
        'quantity' => 'Quantité',
        'currency' => 'Devise',
        'category' => 'Catégorie',
        'package' => 'Produit',
        'packages' => 'Produits',
        'gateways' => 'Moyens de paiements',
        'servers' => 'Serveurs',
        'code' => 'Code',
        'discount' => 'Promotion',
        'start_date' => 'Date de début',
        'expire_date' => 'Date de fin',
        'commands' => 'Commandes',
        'required_packages' => 'Articles pré-requis',
        'user_limit' => 'Limite d\'achats par utilisateur',
        'status' => 'Status',
        'payment_id' => 'ID du Paiement',
        'role' => 'Grade à définir lors de l\'achat',
        'user_id' => 'ID de l\'utilisateur',
        'required_roles' => 'Role pré-requis',
    ],

    'actions' => [
        'remove' => 'Retirer',
        'duplicate' => 'Dupliquer',
    ],

    'goal' => [
        'title' => 'Objectif du mois',
        'progress' => ':count% complété|:count% complétés',
    ],

    'cart' => [
        'title' => 'Panier',
        'success' => 'Votre achat a été effectué avec succès.',
        'guest' => 'Vous devez être connecté pour effectuer un achat.',
        'empty' => 'Votre panier est vide.',
        'checkout' => 'Procéder au paiement',
        'remove' => 'Supprimer',
        'clear' => 'Vider le panier',
        'pay' => 'Payer',
        'back' => 'Retour à la boutique',
        'total' => 'Total : :total',
        'credit' => 'Créditer',

        'confirm' => [
            'title' => 'Payer ?',
            'info' => 'Êtes vous sûr de vouloir acheter le contenu du panier pour :price ?',
        ],

        'errors' => [
            'money' => 'Vous n\'avez pas assez d\'argent pour faire cet achat.',
            'execute' => 'Une erreur est survenue lors du paiement, votre achat a été remboursé.',
        ],
    ],

    'coupons' => [
        'title' => 'Codes promo',
        'add' => 'Ajouter un code promo',
        'error' => 'Ce code promo n\'existe pas, est expiré ou ne peut plus être utilisé.',
        'cumulate' => 'Ce code promo ne peut pas être utilisé avec un autre code promo.',
    ],

    'payment' => [
        'title' => 'Paiement',
        'manual' => 'Paiement manuel',

        'empty' => 'Aucun moyen de paiement n\'est disponible actuellement.',

        'info' => 'Achat #:id sur :website',
        'error' => 'Le paiement n\'a pas pu être effectué.',

        'success' => 'Paiement effectué, vous recevrez votre achat dans le jeu en moins d\'une minute',

        'webhook' => 'Nouveau paiement effectué sur la boutique',
    ],

    'packages' => [
        'limit' => 'Vous avez acheté le maximum possible pour cet article.',
        'requirements' => 'Vous n\'avez pas les pré-requis pour acheter cet article.',
    ],

    'offers' => [
        'gateway' => 'Moyen de paiement',
        'amount' => 'Montant',

        'empty' => 'Aucune offre n\'est disponible actuellement.',
    ],

    'profile' => [
        'payments' => 'Vos achats',
        'money' => 'Argent : :balance',
    ],

    'giftcards' => [
        'success' => ':money ont été crédité sur votre compte',
        'error' => 'Cette carte cadeau n\'existe pas, est expirée ou ne peut plus être utilisée.',
        'add' => 'Échanger une carte cadeau',
    ],
];
