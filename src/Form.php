<?php

namespace AMO;
class Form
{
    public function __construct()
    {
        die(file_get_contents("templates/form.phtml"));
    }
}
