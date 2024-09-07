<?php
/**
 * @package Plugin\format_code
 * @author Remco van der Velde
 * @since 2024-09-07
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

trait Format_code {

    protected function format_code(array $data, object $options): array
    {
        $options->indent_minimum = $options->indent;
        $options->parentheses = $options->parentheses ?? true;
        $document = [];
        foreach($data as $line_nr => $line){
            $line_array = mb_str_split($line);
            $is_single_quote = false;
            $is_double_quote = false;
            $list = '';
            $list_nr = 0;
            $next_line_indent = $options->indent;
            $parentheses_open = 0;
            foreach($line_array as $column_nr => $char){
                $previous = $line_array[$column_nr - 1] ?? null;
                $next = $line_array[$column_nr + 1] ?? null;
                if(
                    $previous !== '\\' &&
                    $char === '\'' &&
                    $is_double_quote === false &&
                    $is_single_quote === false
                ){
                    $is_single_quote = true;
                }
                elseif(
                    $previous !== '\\' &&
                    $char === '\'' &&
                    $is_double_quote === false &&
                    $is_single_quote === true
                ){
                    $is_single_quote = false;
                }
                elseif(
                    $previous !== '\\' &&
                    $char === '"' &&
                    $is_double_quote === false &&
                    $is_single_quote === false
                ){
                    $is_double_quote = true;
                }
                elseif(
                    $previous !== '\\' &&
                    $char === '"' &&
                    $is_double_quote === true &&
                    $is_single_quote === false
                ){
                    $is_double_quote = false;
                }
                if(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    in_array(
                        $char,
                        $options->tag->open,
                        true
                    )
                ){
                    $next_line_indent++;
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    in_array(
                        $char,
                        $options->tag->close,
                        true
                    )
                ){
                    $next_line_indent--;
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $options->parentheses === true &&
                    in_array(
                        $char,
                        [
                            '('
                        ],
                        true
                    ) &&
                    in_array(
                        $next,
                        [
                            "\n",
                            '(',
                            ' ',
//                            '\'',     //same line
//                            '"',      //same line
//                            '$'       //same line
                        ],
                        true
                    )
                ){
                    $next_line_indent++;
                    $parentheses_open++;
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $options->parentheses === true &&
                    $parentheses_open > 0 &&
                    in_array(
                        $char,
                        [
                            ')'
                        ],
                        true
                    ) &&
                    in_array(
                        $next,
                        [
                            "\n",
                            ';',
                            ')',
                            ' ',
                            '\'',
                            '"',
                            '$'
                        ],
                        true
                    )
                ){
                    $next_line_indent--;
                    $parentheses_open--;
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $char === "\n"
                ){
                    if($options->indent < $options->indent_minimum){
                        $options->indent = $options->indent_minimum;
                    }
                    $first = substr($list, 0, 1);
                    if(in_array($first, $options->tag->close, true)){
                        $document[] = str_repeat(' ', ($options->indent - 1) * 4) . $list;
                    } else {
                        $document[] = str_repeat(' ', $options->indent * 4) . $list;
                    }
                    $options->indent = $next_line_indent;
                    $list = '';
                    continue;
                }
                $list .= $char;
            }
            if($list){
                if($options->indent < $options->indent_minimum){
                    $options->indent = $options->indent_minimum;
                }
                $first = substr($list, 0, 1);
                if(in_array($first, $options->tag->close, true)){
                    $document[] = str_repeat(' ', ($options->indent - 1) * 4) . $list;
                } else {
                    $document[] = str_repeat(' ', $options->indent * 4) . $list;
                }
                $options->indent = $next_line_indent;
            }
        }
        return $document;
    }

}