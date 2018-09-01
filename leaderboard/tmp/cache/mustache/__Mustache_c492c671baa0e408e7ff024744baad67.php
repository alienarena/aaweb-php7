<?php

class __Mustache_c492c671baa0e408e7ff024744baad67 extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        // 'players' section
        $value = $context->find('players');
        $buffer .= $this->sectionFa36dfa67b440ff4ab8c7309760901c2($context, $indent, $value);

        return $buffer;
    }

    private function section580ceb09be98f05f54cd1e850e3fcf09(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = '
        <tr>
            <td class="weaponskilldatarow" style="text-align:left">{{weapon}}</td>
            <td class="weaponskilldatarow" style="text-align:right">{{hits}}</td>
            <td class="weaponskilldatarow" style="text-align:right">{{shots}}</td>
            <td class="weaponskilldatarow" style="text-align:right">{{accuracy}}%</td>
        </tr>
        ';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= $indent . '        <tr>
';
                $buffer .= $indent . '            <td class="weaponskilldatarow" style="text-align:left">';
                $value = $this->resolveValue($context->find('weapon'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '            <td class="weaponskilldatarow" style="text-align:right">';
                $value = $this->resolveValue($context->find('hits'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '            <td class="weaponskilldatarow" style="text-align:right">';
                $value = $this->resolveValue($context->find('shots'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '            <td class="weaponskilldatarow" style="text-align:right">';
                $value = $this->resolveValue($context->find('accuracy'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '%</td>
';
                $buffer .= $indent . '        </tr>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function sectionFa36dfa67b440ff4ab8c7309760901c2(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = '
<div id="{{tourney_id}}_weaponaccuracy_{{index}}" class="weaponaccuracy" style="z-index: 300; display: none;">
    <table border="0" cellspacing="0" cellpadding="3" style="width: 100%;">
        <tr>
            <td class="weaponskillheader playername" style="font-size: small; text-align: center" colspan="4">{{name}}</td>
        </tr>
        <tr>
            <td class="weaponskillheader" style="width: 100px; text-align: left">WEAPON</td>
            <td class="weaponskillheader" style="width: 10px; text-align: right">HITS</td>
            <td class="weaponskillheader" style="width: 10px; text-align: right">SHOTS</td>
            <td class="weaponskillheader" style="width: 10px; text-align: right">ACCURACY</td>
        </tr>
        {{#weapon_skill}}
        <tr>
            <td class="weaponskilldatarow" style="text-align:left">{{weapon}}</td>
            <td class="weaponskilldatarow" style="text-align:right">{{hits}}</td>
            <td class="weaponskilldatarow" style="text-align:right">{{shots}}</td>
            <td class="weaponskilldatarow" style="text-align:right">{{accuracy}}%</td>
        </tr>
        {{/weapon_skill}}
    </table>
</div>
';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= $indent . '<div id="';
                $value = $this->resolveValue($context->find('tourney_id'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '_weaponaccuracy_';
                $value = $this->resolveValue($context->find('index'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '" class="weaponaccuracy" style="z-index: 300; display: none;">
';
                $buffer .= $indent . '    <table border="0" cellspacing="0" cellpadding="3" style="width: 100%;">
';
                $buffer .= $indent . '        <tr>
';
                $buffer .= $indent . '            <td class="weaponskillheader playername" style="font-size: small; text-align: center" colspan="4">';
                $value = $this->resolveValue($context->find('name'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '        </tr>
';
                $buffer .= $indent . '        <tr>
';
                $buffer .= $indent . '            <td class="weaponskillheader" style="width: 100px; text-align: left">WEAPON</td>
';
                $buffer .= $indent . '            <td class="weaponskillheader" style="width: 10px; text-align: right">HITS</td>
';
                $buffer .= $indent . '            <td class="weaponskillheader" style="width: 10px; text-align: right">SHOTS</td>
';
                $buffer .= $indent . '            <td class="weaponskillheader" style="width: 10px; text-align: right">ACCURACY</td>
';
                $buffer .= $indent . '        </tr>
';
                // 'weapon_skill' section
                $value = $context->find('weapon_skill');
                $buffer .= $this->section580ceb09be98f05f54cd1e850e3fcf09($context, $indent, $value);
                $buffer .= $indent . '    </table>
';
                $buffer .= $indent . '</div>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

}
