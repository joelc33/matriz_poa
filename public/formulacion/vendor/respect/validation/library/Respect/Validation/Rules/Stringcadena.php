<?php
namespace Respect\Validation\Rules;

class Stringcadena extends AbstractRule
{
    public function validate($input)
    {
        return is_string($input);
    }
}
