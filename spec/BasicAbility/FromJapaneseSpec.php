<?php

namespace spec\Lycee\BasicAbility;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FromJapaneseSpec extends ObjectBehavior
{
    function let()
    {
        $basicAbilities = [
            'Ability1',
            'Ability2'
        ];
        $japaneseMap = [
            'jap1' => 'Ability1',
            'jap2' => 'Ability2',
            'jap22' => 'Ability2',
        ];
        $this->beConstructedWith($basicAbilities, $japaneseMap);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lycee\BasicAbility\FromJapanese');
    }

    function it_can_translate_to_english()
    {
        $this->toEn('jap1')->shouldReturn('Ability1');
        $this->toEn('jap2')->shouldReturn('Ability2');
        $this->toEn('jap22')->shouldReturn('Ability2');
    }

    function it_can_translate_to_index()
    {
        $this->toEnIndex('jap1')->shouldReturn(0);
        $this->toEnIndex('jap2')->shouldReturn(1);
        $this->toEnIndex('jap22')->shouldReturn(1);
    }
}
