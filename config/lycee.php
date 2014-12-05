<?php
/**
 * Game related config
 */
use Lycee\Value\BasicAbility;

return [
    'elements' => [
        [
            'key' => 'snow',
            'code' => '[snow]',
            'enum' => 0,
        ],
        [
            'key' => 'moon',
            'code' => '[moon]',
            'enum' => 1,
        ],
        [
            'key' => 'lightning',
            'code' => '[lightning]',
            'enum' => 2,
        ],
        [
            'key' => 'flower',
            'code' => '[flower]',
            'enum' => 3,
        ],
        [
            'key' => 'sun',
            'code' => '[sun]',
            'enum' => 4,
        ],
        [
            'key' => 'star',
            'code' => '[star]',
            'enum' => 5,
        ],
    ],
    'max_cost' => 12,
    'max_ex' => 3,
    'basic_abilities' => [
        'Step', // _('Step')
        'Side Step', // _('Side Step')
        'Order Step', // _('Order Step')
        'Jump', // _('Jump')
        'Escape', // _('Escape')
        'Side Attack', // _('Side Attack')
        'Tax Trash', // _('Tax Trash')
        'Tax Wake Up', // _('Tax Wake Up')
        'Supporter', // _('Supporter')
        'Touch', // _('Touch')
        'Attacker', // _('Attacker')
        'Defender', // _('Defender')
        'Bonus', // _('Bonus')
        'Penalty', // _('Penalty')
        'Dock Bonus', // _('Dock Bonus')
        'Dash', // _('Dash')
        'Aggressive', // _('Aggressive')
        'Boost', // _('Boost')
    ],
    'basic_abilities_jp_en_map' => [
        'ダッシュ'                  => 'Dash',
        'アグレッシブ'              => 'Aggressive',
        'ステップ'                  => 'Step',
        'サイドステップ'            => 'Side Step',
        'サイド・ステップ'          => 'Side Step',
        'サイド･ステップ'           => 'Side Step',
        'オーダーステップ'          => 'Order Step',
        'オーダー・ステップ'        => 'Order Step',
        'オーダー･ステップ'         => 'Order Step',
        'ジャンプ'                  => 'Jump',
        'エスケープ'                => 'Escape',
        'サイドアタック'            => 'Side Attack',
        'タックストラッシュ'        => 'Tax Trash',
        'タックス・トラッシュ'      => 'Tax Trash',
        'タックス･トラッシュ'       => 'Tax Trash',
        'タックスウェイクアップ'    => 'Tax Wake Up',
        'タックス・ウェイクアップ'  => 'Tax Wake Up',
        'タックス･ウェイクアップ'   => 'Tax Wake Up',
        'サポーター'                => 'Supporter',
        'タッチ'                    => 'Touch',
        'アタッカー'                => 'Attacker',
        'ディフェンダー'            => 'Defender',
        'ボーナス'                  => 'Bonus',
        'ペナルティ'                => 'Penalty',
        'デッキボーナス'            => 'Deck Bonus',
        'デッキ・ボーナス'          => 'Deck Bonus',
        'デッキ･ボーナス'           => 'Deck Bonus',
        'ブースト'                  => 'Boost',
    ]
];