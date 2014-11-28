<?php

namespace spec\Lycee\Tool;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MarkupToHtmlSpec extends ObjectBehavior
{
    private $imgBase = 'path';
    private $basicAbilityMock;

    function let()
    {
        $basicAbilityMock = \Mockery::mock('Lycee\Tool\BasicAbility');
        $this->basicAbilityMock = $basicAbilityMock;
        $elements = [
            [
                'name' => 'star',
            ],
        ];
        $this->beConstructedWith($elements, $this->imgBase, $basicAbilityMock);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lycee\Tool\MarkupToHtml');
    }

    function it_converts_markup_to_html()
    {
        $input = 'hello';
        $this->convert($input)->shouldReturn('hello');

        $input = 'hello[star]';
        $this->convert($input)
            ->shouldReturn('hello<img src="path/star.gif" alt="[star]">');

        $input = 'hello[target] targeting[/target]';
        $this->convert($input)
            ->shouldReturn('hello<span class="target"> targeting</span>');

        $input = 'hello[cost] costing[/cost]';
        $this->convert($input)
            ->shouldReturn('hello<span class="cost"> costing</span>');
    }

    function it_escapes_html_in_source()
    {
        $input = 'hello<span>this is html</span>';
        $this->convert($input)->shouldReturn('hello&lt;span&gt;this is html&lt;/span&gt;');

        $input = 'hello <img>';
        $this->convert($input)->shouldReturn('hello &lt;img&gt;');
    }

    function it_also_converts_basic_ability_markup()
    {
        $this->basicAbilityMock
            ->shouldReceive('normalizeAndTranslate')
            ->once()
            ->with('Ability')
            ->andReturn('NormalizedAbility');
        $input = 'something %b(Ability) lol';
        $this->convert($input)
            ->shouldReturn('something NormalizedAbility lol');
    }

    function it_adds_br_to_newlines()
    {
        $input = "something\nanother line";
        $this->convert($input)
            ->shouldReturn("something<br>\nanother line");
    }
}
