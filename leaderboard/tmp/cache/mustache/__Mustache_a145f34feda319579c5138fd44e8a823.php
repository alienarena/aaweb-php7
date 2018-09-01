<?php

class __Mustache_a145f34feda319579c5138fd44e8a823 extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '<center>
';
        $buffer .= $indent . '    <div class="title">
';
        $buffer .= $indent . '        <span id="title">';
        $value = $this->resolveValue($context->find('tourney_title'), $context);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</span>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '    <div class="subtitle" id="subtitle">';
        $value = $this->resolveValue($context->find('tourney_date'), $context);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</div>
';
        $buffer .= $indent . '    <table border="0" cellspacing="0" cellpadding="3" class="scoretable" id="';
        $value = $this->resolveValue($context->find('tourney_id'), $context);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '">
';
        $buffer .= $indent . '        <tr>
';
        $buffer .= $indent . '            <td class="header playerid" style="display: none">&nbsp;</td>
';
        $buffer .= $indent . '            <td class="header" style="text-align: left">NAME</td>
';
        $buffer .= $indent . '            <td class="header">SCORE</td>
';
        $buffer .= $indent . '        </tr>
';
        // 'players' section
        $value = $context->find('players');
        $buffer .= $this->section3ded509100c5b77fe52a5f9c665fedde($context, $indent, $value);
        $buffer .= $indent . '    </table>
';
        $buffer .= $indent . '</center>
';

        return $buffer;
    }

    private function section3ded509100c5b77fe52a5f9c665fedde(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = '
        <tr>
            <td class="datarow playerid" style="text-align: right; display: none">{{index}}</td>
            <td class="playername datarow">{{name}}</td>
            <td class="datarow" align="right">{{score}}</td>
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
                $buffer .= $indent . '            <td class="datarow playerid" style="text-align: right; display: none">';
                $value = $this->resolveValue($context->find('index'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '            <td class="playername datarow">';
                $value = $this->resolveValue($context->find('name'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '            <td class="datarow" align="right">';
                $value = $this->resolveValue($context->find('score'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '        </tr>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

}
