<?php

return [
    'payment' => [
        'subject' => 'Merci pour votre achat',
        'intro' => 'Merci :user pour votre achat ! Votre paiement a été confirmé et vous recevrez votre achat dans quelques minutes.',
        'total' => 'Total : :total',
        'transaction' => 'ID de la transaction : :transaction (:gateway)',
        'date' => 'Date : :date',
        'subscription' => 'Ce paiement est pour un abonnement commencé le :date, vous pouvez le gérer dans votre profil.',
        'profile' => 'Aller au profil',
    ],

    'giftcard' => [
        'subject' => 'Votre carte cadeau est prête.',
        'intro' => 'Merci pour votre achat! Votre carte cadeau est prête.',
        'code' => 'Code : :code',
        'balance' => 'Montant : :balance',
    ],

    'file' => [
        'subject' => 'Votre lien de téléchargement',
        'intro' => 'Merci :user pour votre achat! Le lien de téléchargement pour votre achat est disponible ci-dessous.',
    ],
];
