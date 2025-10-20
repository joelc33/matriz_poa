<?php
namespace Respect\Validation\Rules;

class Intero extends AbstractRule
{
    public function validate($input)
    {
        return is_numeric($input) && (int) $input == $input;
    }
}
