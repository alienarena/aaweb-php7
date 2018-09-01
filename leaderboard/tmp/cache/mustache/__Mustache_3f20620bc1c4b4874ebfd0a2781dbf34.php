<?php

class __Mustache_3f20620bc1c4b4874ebfd0a2781dbf34 extends Mustache_Template
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
        $buffer .= $indent . '    <table border="0" cellspacing="0" cellpadding="3" class="scoretable details" id="';
        $value = $this->resolveValue($context->find('tourney_id'), $context);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '">
';
        $buffer .= $indent . '        <tr>
';
        $buffer .= $indent . '            <td class="header playerid" style="display: none">&nbsp;</td>
';
        $buffer .= $indent . '            <td class="header" style="text-align:left">NAME</td>
';
        $buffer .= $indent . '            <td class="header">SCORE</td>
';
        $buffer .= $indent . '            <td class="header">DEATHS</td>
';
        $buffer .= $indent . '            <td class="header">RATIO</td>
';
        $buffer .= $indent . '            <td class="header">HITS</td>
';
        $buffer .= $indent . '            <td class="header">SHOTS</td>               
';
        $buffer .= $indent . '        </tr>
';
        // 'players' section
        $value = $context->find('players');
        $buffer .= $this->section9b8d66abeae5d47a2b6a5e67c2d0665b($context, $indent, $value);
        $buffer .= $indent . '    </table>
';
        $buffer .= $indent . '</center>
';

        return $buffer;
    }

    private function section9b8d66abeae5d47a2b6a5e67c2d0665b(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = '
        <tr>
            <td class="datarow playerid" style="text-align: right; display: none">{{index}}</td>
            <td class="datarow playername">{{name}}</td>
            <td class="datarow" align="right">{{score}}</td>
            <td class="datarow" align="right">{{deaths}}</td>
            <td class="datarow" align="right">{{ratio}}%</td>
            <td class="datarow" align="right">{{totalhits}}</td>
            <td class="datarow" align="right">{{totalshots}}</td>
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
                $buffer .= $indent . '            <td class="datarow playername">';
                $value = $this->resolveValue($context->find('name'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '            <td class="datarow" align="right">';
                $value = $this->resolveValue($context->find('score'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '            <td class="datarow" align="right">';
                $value = $this->resolveValue($context->find('deaths'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '            <td class="datarow" align="right">';
                $value = $this->resolveValue($context->find('ratio'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '%</td>
';
                $buffer .= $indent . '            <td class="datarow" align="right">';
                $value = $this->resolveValue($context->find('totalhits'), $context);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</td>
';
                $buffer .= $indent . '            <td class="datarow" align="right">';
                $value = $this->resolveValue($context->find('totalshots'), $context);
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
