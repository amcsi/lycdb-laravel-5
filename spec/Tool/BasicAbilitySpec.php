<?php

namespace spec\Lycee\Tool;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BasicAbilitySpec extends ObjectBehavior
{
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translatorMock;

    /**
     *
     */
    function let()
    {
        $pretendBasicAbilities = [
            -1 => 'Ability',
            0 => 'Another Ability',
            1 => 'WrongCaps Ability',
        ];
        $translatorMock = \Mockery::mock('Symfony\Component\Translation\TranslatorInterface');
        $this->translatorMock = $translatorMock;
        $this->beConstructedWith($translatorMock, $pretendBasicAbilities);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lycee\Tool\BasicAbility');
    }

    function it_returns_correct_basic_abilities_as_they_are()
    {
        $this->normalize('Ability')->shouldReturn('Ability');
        $this->normalize('Another Ability')->shouldReturn('Another Ability');
    }
    
    function it_doesnt_need_special_characters_to_be_correct_to_normalize()
    {
        $this->normalize('Ability ')->shouldReturn('Ability');
        $this->normalize('Ability-')->shouldReturn('Ability');
    }

    function it_throws_an_exception_on_incorrect_abilities()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringNormalize('not a correct ability');
    }

    function it_corrects_capitals_and_spacing()
    {
        $this->normalize('wrongcaps  ABility')->shouldReturn('WrongCaps Ability');
    }

    function it_normalizes_then_translates()
    {
        $this->translatorMock->shouldReceive('trans')
            ->once()
            ->with('WrongCaps Ability')
            ->andReturn('WrongCaps Ability');

        $this->normalizeAndTranslate('wrongcaps  ABility')->shouldReturn('WrongCaps Ability');
    }
}
