@extends('master')

@section('content')

        <input type='button' value='Show/hide basic search' onclick="show_hide('basic_search_form');">
        <form id='basic_search_form' method='get' action='{{ URL::current() }}' class="basicSearchForm">
            <input type="hidden" name="search" value="1">
            <fieldset class="card_search">
                <legend>Basic search</legend>
                <ul>
                    <li>
                        <label for='basic_search_cid' class="form_title">ID</label>
                        <input class="card_name" id='basic_search_cid' type='text' name='cid' value=''>
                    </li>
                    <li>
                        <label for='basic_search_name' class="form_title">Card name</label>
                        <input class="card_name" id='basic_search_name' type='text' name='name' value=''>
                    </li>
                    <li>
                        <label for='basic_search_card_type' class="form_title">Card type</label>
                        <select id='basic_search_card_type' name='card_type'>
                            <option value='-2' selected="selected">-</option>
                            <option value='-1'>non-character</option>
                            <option value='0'>Character</option>
                            <option value='1'>Area</option>
                            <option value='2'>Item</option>
                            <option value='3'>Event</option>

                        </select>
                    </li>
                    <li>
                        <label for='basic_search_name' class="form_title">Cost</label>
                        <select id='basic_search_cost_type' name='cost_type'>
                            <option value="0" selected="selected">-</option>
                            <option value="1">payable by:</option>
                            <option value="2">is exactly:</option>
                        </select>
                        @foreach ($lyceeConfig['elements'] as $element)
                        <label for="basic_search_cost_{{ $element['key'] }}"><img alt='{{ $element['key'] }}' src="{{asset("img/$element[key].gif")}}"></label>
                        {{ Form::selectRange("cost_$element[key]", 0, $lyceeConfig['max_cost'], ['id' => "cost_$element[key]"]) }}
                        @endforeach
                    </li>
                    <li>
                        <label for='basic_search_name' class="form_title">Ex</label>
                        <select id="basic_search_ex_operator" name="ex_operator">
                            <option value="1" selected="selected">≥</option>
                            <option value="0">=</option>
                            <option value="-1">≤</option>
                        </select>
                        {{ Form::selectRange('ex', 0, $lyceeConfig['max_ex'], ['id' => 'basic_search_ex']) }}
                    </li>
                    <li>
                        <label class="form_title">Element</label>
                        <select id='basic_search_element_type' name='element_type'>
                            <option value='0'>-</option>
                            <option value='1'>has:</option>
                            <option value='2'>is:</option>
                        </select>
                        @foreach ($lyceeConfig['elements'] as $element)
                        <label for="basic_search_element_{{ $element['key'] }}">
                            <img alt='{{ $element['key'] }}' src="{{asset("img/$element[key].gif")}}">
                        </label>
                        <input type="checkbox" id="basic_search_element_{{ $element['key'] }}" name="element_{{ $element['key'] }}">
                        @endforeach
                    </li>
                    <li>
                        <label for='basic_search_text' class="form_title">Text contains</label>
                        <input class="card_text" id='basic_search_text' type='text' name='text' value=''>
                    </li>
                    <li>
                        <label for='basic_search_text' class="form_title">&nbsp;</label>
                        <input type="submit" value="Search">
                    </li>
                </ul>
            </fieldset>
        </form>

        [ Pagination ]

        <table class="card_results">
            <thead>
                <colgroup class="card_result_columns">
                    <col class="card_id">
                    <col class="card_name">
                    <col class="card_sets">
                    <col class="card_cost">
                    <col class="card_ex">
                    <col class="card_element">
                    <col class="card_spots">
                    <col class="card_ap">
                    <col class="card_dp">
                    <col class="card_sp">
                </colgroup>
                <tr class="card_result_columns">
                    <th class="card_id" id="card_result_title_0" onclick="reorder_table('card_result', 0)">Card ID</th>
                    <th class="card_name" id="card_result_title_1" onclick="reorder_table('card_result', 1)">Name</th>
                    <th class="card_sets" id="card_result_title_9" onclick="reorder_table('card_result', 2)">Card sets</th>
                    <th class="card_cost" id="card_result_title_2" onclick="reorder_table('card_result', 3)">Cost</th>
                    <th class="card_ex" id="card_result_title_3" onclick="reorder_table('card_result', 4)">Ex</th>
                    <th class="card_element" id="card_result_title_4" onclick="reorder_table('card_result', 5)">Element</th>
                    <th class="card_spots" id="card_result_title_5" onclick="reorder_table('card_result', 6)">FL</th>
                    <th class="card_ap" id="card_result_title_6" onclick="reorder_table('card_result', 7)">AP</th>
                    <th class="card_dp" id="card_result_title_7" onclick="reorder_table('card_result', 8)">DP</th>
                    <th class="card_sp" id="card_result_title_8" onclick="reorder_table('card_result', 9)">SP</th>
                </tr>
            </thead>


            <tbody>
                @foreach ($cards as $key => $card)
                <tr id="card_result_{{ $card['cid'] }}" class="{{ $card['type_text'] }} {{ $key % 2 ? 'odd' : 'even' }}">
                    <td class="cardId">{{ $card['cid'] }}</td>
                    <td class="cardName">{{ $card['name_jp'] }}
                    @if ($card['import_errors'])
                        <span class="clickable tooltip" title="Notice: The import script has reported errors regarding this card.
We are already aware of this error and will fix it sometime soon so please don't report it to us."><img src="<?= $this->basePath() ?>/img/exclamation-red-frame.png" alt="warning"></span>
                    @endif
                    </td>
                    <td class="sets">
                        {{ nl2br($this->escapeHtml($card['sets_string_short']), false) }}
                    </td>
                    <td class="cost">
                        {!! Helper::lycdbMarkupToHtml($card['cost_markup']) !!}
                    </td>
                    <td class="ex">
                        {{ $card['ex'] }}
                    </td>
                    <td>
                        {!! Helper::lycdbMarkupToHtml($card['elements_markup']) !!}
                    </td>
                    @if (!$card['type'])
                    <td class="positions">
                        {!! Helper::lycdbMarkupToHtml($card['position_markup']) !!}
                    </td>
                    <td class="ap">
                        {{ $card['ap'] }}
                    </td>
                    <td class="dp">
                        {{ $card['dp'] }}
                    </td>
                    <td class="sp">
                        {{ $card['sp'] }}
                    </td>
                    @else
                    <td class="positions">
                        &nbsp;
                    </td>
                    <td class="ap">
                        &nbsp;
                    </td>
                    <td class="dp">
                        &nbsp;
                    </td>
                    <td class="sp">
                        &nbsp;
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>

        <?php echo $this->paginationControl($this->paginator, 'Elastic', 'application/search/partial/pagination.phtml'
            , array('route' => $this->route));
        ?>

    <div id='hidden'>
        @if ($cards)
        @foreach($cards as $key => $card)
        <div class="card_js {{ $card['type_text'] }}" id="card_js_{{ $card['cid'] }}" style="display: none;">
            <div class="card_js_image" data-src="{{ asset("lycee_images/180/$card[cid]") }}" data-width="180">
            </div>
            <div class="card_js_details">
                <div class="card_js_id card_js_detail_left">
                    <span>{{ $card['cid'] }}</span>
                </div>
                <div class="card_js_sets">
                    <span>{!! nl2br(e($card['sets_string'])) !!}</span>
                </div>
                <div class="card_js_name">
                    <span class="card_name card_js_detail_left">
                        {{ $card['name_en'] }}
                    </span>
                </div>
                <div class="card_js_name_jap">
                    <span class="card_name jap">
                        {{ $card['name_jp'] }}
                    </span>
                </div>
                <div class="card_js_ex card_js_detail_left">
                    <span>
                        <strong>Ex:</strong> {{ $card['ex'] }}
                        {!! Helper::lycdbMarkupToHtml($card['elements_markup']) !!}
                    </span>
                </div>
                <div class="card_js_cost">
                    <span>
                        {!! Helper::lycdbMarkupToHtml($card['cost_markup']) !!}
                    </span>
                </div>
                @if (\Lycee\Card\Model::CHAR == $card['type'])
                <div class="card_js_ap_dp_sp card_js_detail_left">
                <div class='left ap'>{{ $card['ap'] }}</div>
                    <div class='left dp'>{{ $card['dp'] }}</div>
                    <div class='left sp'>{{ $card['sp'] }}</div>
                </div>
                <div class="card_js_spots">
                    {!! Helper::lycdbMarkupToHtml($card['position_markup']) !!}
                </div>
                <div class="card_js_text card_js_detail_full">
                    @if ($card['conversion_jp'])
                    <span class="conversion">{{ Lang::trans('Conversion') }}: {{ $card['conversion_jp'] }}</span>
                    @endif
                    @if ($card['display_basic_abilities_jp_markup'])
                    @foreach ($card['display_basic_abilities_jp_markup'] as $basicAbility)
                    <p class="basic_ability_name">
                        {!! Helper::lycdbMarkupToHtml($basicAbility) !!}
                    </p>
                    @endforeach
                    @endif
                    <p class="ability_name">
                        <span>
                            <span class="abilityName"><?php $cost = Helper::lycdbMarkupToHtml($card['ability_cost_jp']) ?>
                            {{ $card['ability_name_jp'] }}@if ($card['ability_cost_jp']):@endif</span>
                            @if ($card['ability_cost_jp'])
                            <span class="cost">{!! Helper::lycdbMarkupToHtml($card['ability_cost_jp']) !!}</span>
                            @endif
                        </span>
                    </p>
                    <p class="card_text">
                        <span>
                            {!! Helper::lycdbMarkupToHtml($card['ability_desc_jp']) !!}
                        </span>
                    </p>
                </div>
                @else
                <div class="card_js_text card_js_detail_full">
                    <p class="card_text">
                        <span>
                            {!! Helper::lycdbMarkupToHtml($card['ability_desc_jp']) !!}
                        </span>
                    </p>
                </div>
                @endif
                <div style="clear: both;"></div>
            </div>
            <div style="clear: both;"></div>
        </div>
        @endforeach
        @endif
    </div>

@stop
